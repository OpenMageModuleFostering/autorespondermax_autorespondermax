<?php
 
class Autorespondermax_Autorespondermax_Model_Mysql4_Subscriber extends Mage_Core_Model_Mysql4_Abstract {
  protected function _construct() {
    $this->_init('autorespondermax/subscriber', 'autorespondermax_subscriber_id');
  }
}