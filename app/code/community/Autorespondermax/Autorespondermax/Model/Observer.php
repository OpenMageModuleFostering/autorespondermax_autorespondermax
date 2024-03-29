<?php

class Autorespondermax_Autorespondermax_Model_Observer {
  /**
  * Observer method for CRON job to refresh store data from Autoresponder Max
  * 
  * @param  Varien_Event_Observer $observer
  */
  public function dailyRefresh($observer) {
    $helper = Mage::helper('autorespondermax');
    $stores = Mage::app()->getStores();
    foreach($stores as $store) {
      if($helper->connected($store)) {
        try {
          $helper->refresh($store);
        }
        catch(Exception $e) {
          Mage::logException($e);
        }
      }
    }
  }
  
  /**
  * Observe changes to `Mage_Newsletter_Model_Subcriber` and record them under `Autorespondermax_Autorespondermax_Model_Subscriber`
  *
  * @param  Varien_Event_Observer $observer
  */
  public function updateSubscriber($observer) {
    $subscriber = $observer->getSubscriber();
    $_subscriber = Mage::getModel('autorespondermax/subscriber')->loadBySubscriber($subscriber);
    $_subscriber->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
    $_subscriber->save();
  }
}