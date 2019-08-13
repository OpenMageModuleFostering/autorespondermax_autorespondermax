<?php

class Autorespondermax_Autorespondermax_Model_Subscriber extends Mage_Core_Model_Abstract {
  protected function _construct() {
    $this->_init('autorespondermax/subscriber');
  }
  
  /**
  * Find or set Autoresponder Max subscriber based on newsletter subscriber
  *
  * @param Mage_Newsletter_Model_Subcriber $subscriber
  * @return Autorespondermax_Autorespondermax_Model_Subscriber
  */
  public function loadBySubscriber($subscriber) {
    $matches = $this->getResourceCollection()->addFieldToFilter('subscriber_id', $subscriber->getSubscriberId());
    foreach ($matches as $match) {
      return $this->load($match->getId());
    }
    return $this->setData('subscriber_id', $subscriber->getSubscriberId())->setData('store_id', $subscriber->getStoreId());
  }
  
  /**
  * @return Mage_Newsletter_Model_Subcriber
  */
  public function getSubscriber() {
    return Mage::getModel('newsletter/subscriber')->load($this->getSubscriberId());
  }
}