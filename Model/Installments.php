<?php
class Cammino_Buscape_Model_Installments extends Mage_Core_Model_Abstract
{
	public function getInstallments($value, $paymentMethod)
	{

		$max_installments = floatval(Mage::getStoreConfig("buscape/$paymentMethod/max_installments"));
		$min_installment_value = floatval(Mage::getStoreConfig("buscape/$paymentMethod/min_installment_value"));
		$installment_tax = $paymentMethod == "cartao_parcelado_sem_juros" ? 0 : floatval(Mage::getStoreConfig("buscape/$paymentMethod/installment_tax"));
		$qty = 1.0;
		$installment_value = floatval($value);

		for($i=1.0; $i <= $max_installments; $i++) {
			$future_value = $this->applyTax($value, $i, $installment_tax);
			if (($future_value/$i) > $min_installment_value) {
				$installment_value = ($future_value/$i);
				$qty = $i;
			} else {
				break;
			}
		}

		return array("qty" => $qty, "value" => $installment_value); 
	}

	public function applyTax($value, $n, $tax)
	{
		for ($i=1; $i <= $n; $i++) {
			$value = $value + ($value * ($tax / 100));
		}
		return $value;
	}

}
?>