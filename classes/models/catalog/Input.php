<?php
/*
 * Shopgate GmbH
 * http://www.shopgate.com
 * Copyright © 2012-2014 Shopgate GmbH
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
*/


class Shopgate_Model_Catalog_Input extends Shopgate_Model_AbstractExport {

	const DEFAULT_INPUT_TYPE_SELECT = 'select';
	const DEFAULT_INPUT_TYPE_MULTIPLE = 'multiple';
	const DEFAULT_INPUT_TYPE_RADIO = 'radio';
	const DEFAULT_INPUT_TYPE_CHECKBOX = 'checkbox';
	const DEFAULT_INPUT_TYPE_TEXT = 'text';
	const DEFAULT_INPUT_TYPE_AREA = 'area';
	const DEFAULT_INPUT_TYPE_FILE = 'file';
	const DEFAULT_INPUT_TYPE_DATE = 'date';
	const DEFAULT_INPUT_TYPE_TIME = 'time';
	const DEFAULT_INPUT_TYPE_DATETIME = 'datetime';

	/**
	 * define allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods = array(
		'Uid',
		'Type',
		'Options',
		'Validation',
		'Required',
		'Label',
		'InfoText');

	/**
	 * init default objects
	 */
	public function __construct() {
		$this->setValidation(new Shopgate_Model_Catalog_Validation());
		$this->setOptions(array());
	}

	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject    $inputNode
		 * @var Shopgate_Model_XmlResultObject    $optionsNode
		 * @var Shopgate_Model_Catalog_Validation $validationItem
		 * @var Shopgate_Model_Catalog_Option     $optionItem
		 */
		$inputNode = $itemNode->addChild('input');
		$inputNode->addAttribute('uid', $this->getUid());
		$inputNode->addAttribute('type', $this->getType());
		$inputNode->addAttribute('required', $this->getRequired());
		$inputNode->addChildWithCDATA('label', $this->getLabel());
		$inputNode->addChildWithCDATA('info_text', $this->getInfoText());
		$optionsNode = $inputNode->addChild('options');

		/**
		 * options
		 */
		foreach ($this->getOptions() as $optionItem) {
			$optionItem->asXml($optionsNode);
		}

		/**
		 * validation
		 */
		$this->getValidation()->asXml($inputNode);

		return $itemNode;
	}

	/**
	 * add option
	 *
	 * @param Shopgate_Model_Catalog_Option $option
	 */
	public function addOption($option) {
		$options = $this->getOptions();
		array_push($options, $option);
		$this->setOptions($options);
	}
}