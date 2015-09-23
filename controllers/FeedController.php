<?php
class Cammino_Buscape_FeedController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$block = $this->getLayout()->createBlock('buscape/feed');
		$xml = $block->toHtml();
		header('Content-Type: application/xml; charset=utf-8');
		die($xml);
	}

}