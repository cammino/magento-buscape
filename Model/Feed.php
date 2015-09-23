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
		$xml  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		$xml .= "<buscape>\n";
		$xml .= "<data_atualizacao>". date("c") ."</data_atualizacao>\n";
		$xml .= "<produtos>\n";
		return $xml;
	}

	public function getXmlEnd() {
		$xml  = "</produtos>\n";
		$xml .= "</buscape>\n";
		return $xml;
	}

	public function getProductXml($product) {

		$productPrice = $this->getPrice($product);
		$urlSufix = Mage::getStoreConfig("buscape/config/url_sufix");

		if ($productPrice == null)
			return "";

		if (($product->getShowInBuscape() != null) && (intval($product->getShowInBuscape()) == 0))
			return "";

		$xml  = "<produto>\n";
		$xml .= "<descricao><![CDATA[". $product->getName() ."]]></descricao>\n";
		$xml .= "<canal_buscape>\n";
		$xml .= "<canal_url><![CDATA[". $product->getProductUrl() . $urlSufix ."]]></canal_url>\n";
		$xml .= "<valores>\n";


		if (intval(Mage::getStoreConfig("buscape/boleto/active")) == 1) {
			$discount = floatval(Mage::getStoreConfig("buscape/boleto/discount"));
			$tmpPrice = $productPrice * (1 - ($discount/100));
			$xml .= "<valor>\n";
			$xml .= "<forma_de_pagamento>boleto</forma_de_pagamento>\n";
			$xml .= "<parcelamento>1x de ". Mage::helper('core')->currency($tmpPrice, true, false) ."</parcelamento>\n";
			$xml .= "<canal_preco>". Mage::helper('core')->currency($tmpPrice, true, false) ."</canal_preco>\n";
			$xml .= "</valor>\n";
		}

		if (intval(Mage::getStoreConfig("buscape/cartao_avista/active")) == 1) {
			$discount = floatval(Mage::getStoreConfig("buscape/cartao_avista/discount"));
			$tmpPrice = $productPrice * (1 - ($discount/100));
			$xml .= "<valor>\n";
			$xml .= "<forma_de_pagamento>cartao_avista</forma_de_pagamento>\n";
			$xml .= "<parcelamento>1x de ". Mage::helper('core')->currency($tmpPrice, true, false) ."</parcelamento>\n";
			$xml .= "<canal_preco>". Mage::helper('core')->currency($tmpPrice, true, false) ."</canal_preco>\n";
			$xml .= "</valor>\n";
		}

		if (intval(Mage::getStoreConfig("buscape/cartao_parcelado_sem_juros/active")) == 1) {
			$installments = Mage::getSingleton('buscape/installments')->getInstallments($productPrice, "cartao_parcelado_sem_juros");
			$xml .= "<valor>\n";
			$xml .= "<forma_de_pagamento>cartao_parcelado_sem_juros</forma_de_pagamento>\n";
			$xml .= "<parcelamento>". $installments["qty"] ."x de ". Mage::helper('core')->currency($installments["value"], true, false) ."</parcelamento>\n";
			$xml .= "<canal_preco>". Mage::helper('core')->currency($productPrice, true, false) ."</canal_preco>\n";
			$xml .= "</valor>\n";
		}

		if (intval(Mage::getStoreConfig("buscape/cartao_parcelado_com_juros/active")) == 1) {
			$installments = Mage::getSingleton('buscape/installments')->getInstallments($productPrice, "cartao_parcelado_com_juros");
			$xml .= "<valor>\n";
			$xml .= "<forma_de_pagamento>cartao_parcelado_com_juros</forma_de_pagamento>\n";
			$xml .= "<parcelamento>". $installments["qty"] ."x de ". Mage::helper('core')->currency($installments["value"], true, false) ."</parcelamento>\n";
			$xml .= "<canal_preco>". Mage::helper('core')->currency(($installments["qty"]*$installments["value"]), true, false) ."</canal_preco>\n";
			$xml .= "</valor>\n";
		}

		$xml .= "</valores>\n";
		$xml .= "</canal_buscape>\n";
		$xml .= "<id_oferta>". $product->getId() ."</id_oferta>\n";
		$xml .= "<imagens>\n";
		$xml .= "<imagem tipo=\"O\"><![CDATA[". (string)Mage::helper('catalog/image')->init($product, 'image')->resize(600,600) ."]]></imagem>\n";
		$xml .= "<imagem tipo=\"F\"><![CDATA[". (string)Mage::helper('catalog/image')->init($product, 'image')->resize(600,600) ."]]></imagem>\n";
		$xml .= "</imagens>\n";

		$xml .= $this->getCategoriesNode($product);

		$xml .= "<isbn>". $product->getSku() ."</isbn>\n";

	//	$xml .= "<cod_barra><cod_barra>\n";
	//	$xml .= "<disponibilidade><disponibilidade>\n";
	//	$xml .= "<marketplace><marketplace>\n";

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

		return "<categoria>". $storeCategory ."</categoria>\n";
	}

	public function getProducts() {
		$products = Mage::getModel('catalog/product')->getCollection();

		$products->addAttributeToSelect('*')
			//->addMinimalPrice()
			//->addFinalPrice()
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', array('neq' => '1'))
			->addAttributeToSort('created_at', 'desc');

		$filterAttribute = Mage::getStoreConfig("buscape/config/filter_attribute");

		if (strval($filterAttribute) != "") {
			$products->addAttributeToSelect($filterAttribute)
				->addAttributeToFilter($filterAttribute, array('eq' => 1));
		}

		return $products;
	}

	public function getPrice($product) {
		if ($product->getTypeId() == "simple") {
			if (($product->getSpecialPrice() > 0) &&
				((($product->getSpecialFromDate() != "") && (strtotime($product->getSpecialFromDate()) <= time())) || ($product->getSpecialFromDate() == "")) &&
				((($product->getSpecialToDate() != "") && (strtotime($product->getSpecialToDate()) >= time())) || ($product->getSpecialToDate() == ""))) {
				return $product->getSpecialPrice();
			} else {
				return $product->getPrice();
			}
		} else if ($product->getTypeId() == "grouped") {
			return $this->getGroupedPrice($product);
		} else {
			return null;
		}
	}

	public function getGroupedPrice($product) {
		$associated = $this->getAssociatedProducts($product);
		$prices = array();
		$minimal = 0;

		foreach($associated as $item) {
			if ($item->getPrice() > 0) {
				array_push($prices, $item->getPrice());
			}
		}

		rsort($prices, SORT_NUMERIC);

		if (count($prices) > 0) {
			$minimal = end($prices);	
		}

		return $minimal;
	}

}