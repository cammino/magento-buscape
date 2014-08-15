<?php
class Cammino_Buscape_FeedController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$feed = Mage::getModel('buscape/feed');
		$xml = $feed->getXml();
		echo $xml;
	}

}