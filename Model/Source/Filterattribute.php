<?php
class Cammino_Buscape_Model_Source_Filterattribute extends Mage_Core_Model_Abstract
{
	public function toOptionArray() {
		$attrs = array();
		$productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');

		$attrs["none"] = array(
			'label' => "None",
			'value' => ""
		);

		foreach ($productAttrs as $productAttr) {
			if ($productAttr->getSourceModel() == "eav/entity_attribute_source_boolean") {

				$attrCode = $productAttr->getAttributeCode();
				$attrName = $productAttr->getFrontend()->getLabel();

				$attrs[$attrCode] = array(
					'label' =>$attrName,
					'value' => $attrCode
				);				
			}
		}

		return $attrs;
	}
}