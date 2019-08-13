<?php

class Autorespondermax_Autorespondermax_Model_General_Api extends Autorespondermax_Autorespondermax_Model_Api {
  public function info() {
    return array('magento_version' => Mage::getVersion());
  }
}