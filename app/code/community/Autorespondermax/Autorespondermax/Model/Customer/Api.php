<?php

class Autorespondermax_Autorespondermax_Model_Customer_Api extends Autorespondermax_Autorespondermax_Model_Api {
  public static $ATTRIBUTE_FILTERS = array('password_hash', 'rp_token');
  public static $SUBSCRIBER_ATTRIBUTE_FILTERS = array('subscriber_confirm_code');
  
  public function items($storeId, $limit = 250, $page = 1, $filters = array()) {
    $this->_validateStore($storeId);
    $store = $this->_getStore($storeId);
    
    $collection = Mage::getModel('customer/customer')
      ->getCollection()
      //Using store is not best, customer can login in to different stores under the same website
      //->addFieldToFilter('store_id', $storeId)
      ->addFieldToFilter('website_id', $store->getWebsiteId())
      ->addAttributeToSelect('*')
      ->unshiftOrder('updated_at', Varien_Data_Collection::SORT_ORDER_ASC);
    $this->_applyFilters($collection, $limit, $page, $filters);
    
    $result = array();
    foreach ($collection as $customer) {
      $customerResult = $this->_toArray($customer, self::$ATTRIBUTE_FILTERS);
      
      //Subscriber
      //Now there is a separate API endpoint to do this, but will be left
      $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
      if(!is_null($subscriber)) {
        $subscriberResult = $this->_toArray($subscriber, self::$SUBSCRIBER_ATTRIBUTE_FILTERS);
        $customerResult['subscriber'] = $subscriberResult;
      }
      
      $result[] = $customerResult;
    }
   
    return $result;
  }
  
  public function ids($storeId, $limit = 250, $page = 1) {
    $this->_validateStore($storeId);
    $store = $this->_getStore($storeId);
    
    $collection = Mage::getModel('customer/customer')
      ->getCollection()
      //Using store is not best, customer can login in to different stores under the same website
      //->addFieldToFilter('store_id', $storeId)
      ->addFieldToFilter('website_id', $store->getWebsiteId())
      ->addAttributeToSelect('entity_id');
    $this->_applyFilters($collection, $limit, $page);
    
    $result = array();
    foreach ($collection as $customer) {
      $result[] = $customer->getEntityId();
    }
    return $result;
  }
}