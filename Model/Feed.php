<?php
class Cammino_Buscape_Model_Feed extends Mage_Core_Model_Abstract
{

	public function getXml() {
		$products = $this->getProducts();

		$xml = $this->getXmlStart();

		foreach ($products as $product) {
			$xml .= $this->getProductXml($product);
		}

		$xml .= $this->getXmlEnd();

		return $xml;
	}

	public function getXmlStart() {
		$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<sitemap>\n";
		return $xml;
	}

	public function getXmlEnd() {
		$xml  = "</sitemap>\n";
		return $xml;
	}

	public function getProductXml($product) {
		$xml  = "<produto>\n";
		$xml .= "<descricao><![CDATA[". $product->getName() ."]]></descricao>\n";

		if (($product->getSpecialPrice() > 0) &&
			((($product->getSpecialFromDate() != "") && (strtotime($product->getSpecialFromDate()) <= time())) || ($product->getSpecialFromDate() == "")) &&
			((($product->getSpecialToDate() != "") && (strtotime($product->getSpecialToDate()) >= time())) || ($product->getSpecialToDate() == ""))) {
			$xml .= "<preco>". Mage::helper('core')->currency($product->getSpecialPrice(), true, false) ."</preco>\n";
		} else {
			$xml .= "<preco>". Mage::helper('core')->currency($product->getPrice(), true, false) ."</preco>\n";
		}

		$xml .= "<id_produto>". $product->getId() ."</id_produto>\n";
		$xml .= "<link_prod><![CDATA[". $product->getProductUrl() ."]]></link_prod>\n";
	//	$xml .= "<ISBN><ISBN>\n";
		$xml .= "<imagem><![CDATA[". (string)Mage::helper('catalog/image')->init($product, 'image')->resize(500,500) ."]]></imagem>\n";
		$xml .= $this->getCategoriesNode($product);
	//	$xml .= "<parcel>ou Nx Valor cada parcela</parcel>\n";
		$xml .= "</produto>\n";
		return $xml;
	}

	public function getCategoriesNode($product) {
		$ids = $product->getCategoryIds();
		$categoryLevel = -1;
		$storeCategory = "";

		foreach($ids as $id) {
			$category = Mage::getModel('catalog/category')->load($id);
			if (intval($category->getLevel()) > $categoryLevel) {
				$categoryLevel = intval($category->getLevel());
				$storeCategory = $category->getName();
			}
		}

		return "<categ>". $storeCategory ."</categ>\n";
	}

	public function getProducts() {
		$products = Mage::getModel('catalog/product')->getCollection();

		$products->addAttributeToSelect('*')
			//->addMinimalPrice()
			//->addFinalPrice()
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', array('neq' => '1'))
			->addAttributeToSort('created_at', 'desc');

		return $products;
	}

}