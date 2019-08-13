<?php

class Autorespondermax_Autorespondermax_Model_Subscriber_Api extends Autorespondermax_Autorespondermax_Model_Api {
  public static $ATTRIBUTE_FILTERS = array('store_id');
  
  public function items($storeId, $limit = 250, $page = 1, $filters = array()) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('autorespondermax/subscriber')
      ->getResourceCollection()
      ->addFieldToFilter('store_id', $storeId)
      ->addFieldToSelect('*')
      ->unshiftOrder('updated_at', Varien_Data_Collection::SORT_ORDER_ASC);
    $this->_applyFilters($collection, $limit, $page, $filters);
    
    $result = array();
    foreach ($collection as $_subscriber) {
      $subscriber = $_subscriber->getSubscriber();
      $subscriberResult = $this->_toArray($subscriber, self::$ATTRIBUTE_FILTERS);
      //Append the Autoresponder Max subscriber data to Newsletter subscriber data
      $subscriberResult['updated_at'] = $_subscriber->getUpdatedAt();
      $result[] = $subscriberResult;
    }
   
    return $result;
  }
  
  public function ids($storeId, $limit = 250, $page = 1) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('autorespondermax/subscriber')
      ->getResourceCollection()
      ->addFieldToFilter('store_id', $storeId)
      ->addFieldToSelect('subscriber_id');
    $this->_applyFilters($collection, $limit, $page);
    
    $result = array();
    foreach ($collection as $_subscriber) {
      $result[] = $_subscriber->getSubscriberId();
    }
    return $result;
  }
}