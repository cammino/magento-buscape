<?php
class Cammino_Buscape_Block_Feed extends Mage_Core_Block_Template {

	protected function _construct()
	{
		$this->addData(array(
			'cache_lifetime' => 3600,
			'cache_tags'     => array(Mage_Catalog_Model_Product::CACHE_TAG),
			'cache_key'      => "buscape_feed",
		));
	}

	protected function _toHtml() {
		$feed = Mage::getModel('buscape/feed');
		$xml = $feed->getXml();
		return $xml;
	}

}
?>