<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

/**
 * @author Konstantin Kiritsenko <konstantin@kiritsenko.com>
 */
class Payone_Handler
{
    /**
     * @param string $paymentMethod
     *
     * @return string
     */
    protected function _getType($paymentMethod)
    {
        switch ($paymentMethod) {
            case ShopgateOrder::PAYONE_CC:
                return Payone_Enum_ClearingType::CREDITCARD;
            case ShopgateOrder::PAYONE_PP:
                return Payone_Enum_ClearingType::WALLET;
            case ShopgateOrder::PAYONE_DBT:
                return Payone_Enum_ClearingType::DEBITPAYMENT;
            case ShopgateOrder::PAYONE_SUE:
            case ShopgateOrder::PAYONE_IDL:
            case ShopgateOrder::PAYONE_GP:
                return Payone_Enum_ClearingType::ONLINEBANKTRANSFER;
            case ShopgateOrder::PAYONE_PRP:
                return Payone_Enum_ClearingType::ADVANCEPAYMENT;
            case ShopgateOrder::PAYONE_KLV:
                return Payone_Enum_ClearingType::FINANCING;
            case ShopgateOrder::PAYONE_INV:
            default:
                return Payone_Enum_ClearingType::INVOICE;
        }
    }

    /**
     * @param $paymentMethod
     *
     * @return string
     */
    public function getBankTransferType($paymentMethod)
    {
        switch ($paymentMethod) {
            case ShopgateOrder::PAYONE_GP:
                return Payone_Api_Enum_OnlinebanktransferType::GIROPAY; //DE
            case ShopgateOrder::PAYONE_IDL:
                return Payone_Api_Enum_OnlinebanktransferType::IDEAL; //NL
            case ShopgateOrder::PAYONE_SUE:
            default:
                return Payone_Api_Enum_OnlinebanktransferType::INSTANT_MONEY_TRANSFER; //DE, AT, CH, NL
        }
    }

    /**
     * @param $paymentMethod
     *
     * @return int
     */
    public function getTransactionId($paymentMethod, $paymentInfo)
    {
        $type         = 'preauthorization';
        $refId        = rand(000000000, 99999999);
        $clearingType = $this->_getType($paymentMethod);

        $personalData = new Payone_Api_Request_Parameter_Authorization_PersonalData();
        $personalData->setFirstname('Test');
        $personalData->setLastname('tester');
        $personalData->setStreet('Heniz');
        $personalData->setZip('39114');
        $personalData->setCity('Phoenix');
        $personalData->setCountry('US');
        $personalData->setEmail('teste@tester.com');
        $personalData->setIp('192.168.222.1');

        $deliveryData = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
        $deliveryData->setShippingFirstname('test');
        $deliveryData->setShippingLastname('tester');
        $deliveryData->setShippingStreet('test');
        $deliveryData->setShippingZip('13311');
        $deliveryData->setShippingCity('phoenix');
        $deliveryData->setShippingCountry('US');

        $request = new Payone_Api_Request_Preauthorization();
        $request->setRequest($type);
        $request->setAid('25775');
        $request->setClearingtype($clearingType);
        $request->setAmount(500);
        $request->setReference($refId);
        $request->setCurrency('EUR');
        $request->setMid('24906');
        $request->setPortalid('2017714');
        $request->setMode('test');
        $request->setKey('9lhXF75G2y0q8584');
        $request->setEncoding('UTF-8');
        $request->setSolutionName('noovias');
        $request->setSolutionVersion('3.2.0');
        $request->setIntegratorName('magento');
        $request->setIntegratorVersion('1.9.1.1');
        $request->setPersonalData($personalData);
        $request->setDeliveryData($deliveryData);

        if ($clearingType === Payone_Enum_ClearingType::DEBITPAYMENT) {
            $bankData = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_DebitPayment();
            $bankData->setBankcountry('DE');
            $bankData->setBankaccount($paymentInfo['bank_account']['bank_account_number']);
            $bankData->setBankcode($paymentInfo['bank_account']['bank_code']);
            //$bankData->setBic($paymentInfo['bank_account']['bic']); //not supported in v3.1.6
            //$bankData->setIban($paymentInfo['bank_account']['iban']);
            $request->setPayment($bankData);
        } elseif ($clearingType === Payone_Enum_ClearingType::ONLINEBANKTRANSFER) {
            $bankData = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_OnlineBankTransfer();
            $bankData->setBankcountry('DE');
            $bankData->setOnlinebanktransfertype($this->getBankTransferType($paymentMethod));
            $bankData->setIban($paymentInfo['bank_account']['iban']);
            $bankData->setBic($paymentInfo['bank_account']['bic']);
            $bankData->setSuccessurl(Mage::helper('payone_core/url')->getSuccessUrl());
            $bankData->setErrorurl(Mage::helper('payone_core/url')->getErrorUrl());
            $request->setPayment($bankData);
        }

        if ($type === 'preauthorization') {
            $service = new Payone_Core_Model_Service_Payment_Preauthorize();
            $api     = new Payone_Api_Service_Payment_Preauthorize();
            $req     = new Payone_Api_Mapper_Request_Payment_Preauthorization();
            $res     = new Payone_Api_Mapper_Response_Preauthorization();
        } else {
            $service = new Payone_Core_Model_Service_Payment_Authorize();
            $api     = new Payone_Api_Service_Payment_Authorize();
            $req     = new Payone_Api_Mapper_Request_Payment_Authorization();
            $res     = new Payone_Api_Mapper_Response_Authorization();
        }
        $req->setMapperCurrency(new Payone_Api_Mapper_Currency());

        $adapter = new Payone_Api_Adapter_Http_Curl();
        $adapter->setUrl('https://api.pay1.de/post-gateway/');
        $api->setAdapter($adapter);

        $api->setMapperRequest($req);
        $api->setMapperResponse($res);

        $service->setServiceApiPayment($api);
        /** @var Payone_Api_Response_Preauthorization_Approved $response */
        $response = $service->perform($request); //make perform method public in the module. I know...hacking!

        return $response->getTxid();
    }
}
