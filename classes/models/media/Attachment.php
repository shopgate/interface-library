<?php
/*
 * Shopgate GmbH
 * http://www.shopgate.com
 * Copyright © 2012-2014 Shopgate GmbH
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
*/


class Shopgate_Model_Media_Attachment extends Shopgate_Model_AbstractExport {
	/**
	 * @param Shopgate_Model_XmlResultObject $itemNode
	 *
	 * @return Shopgate_Model_XmlResultObject
	 */
	public function asXml(Shopgate_Model_XmlResultObject $itemNode) {
		/**
		 * @var Shopgate_Model_XmlResultObject $attachmentNode
		 */
		$attachmentNode = $itemNode->addChild('attachment');
		$attachmentNode->addAttribute('number', $this->getNumber());
		$attachmentNode->addChildWithCDATA('url', $this->getUrl());
		$attachmentNode->addChild('mime_type', $this->getMimeType());
		$attachmentNode->addChild('file_name', $this->getFileName());
		$attachmentNode->addChildWithCDATA('title', $this->getTitle());
		$attachmentNode->addChildWithCDATA('description', $this->getDescription());

		return $itemNode;
	}
}