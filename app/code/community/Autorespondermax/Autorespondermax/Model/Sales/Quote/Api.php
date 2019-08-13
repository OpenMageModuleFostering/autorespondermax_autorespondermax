<?php

class Autorespondermax_Autorespondermax_Model_Sales_Quote_Api extends Autorespondermax_Autorespondermax_Model_Api {
  public static $ATTRIBUTE_FILTERS = array();
  public static $LINE_ITEM_ATTRIBUTE_FILTERS = array('product_options', 'product', 'qty_options');
  
  public function items($storeId, $limit = 250, $page = 1, $filters = array()) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('sales/quote')
      ->getCollection()
      ->addFieldToFilter('store_id', $storeId)
      ->addFieldToSelect('*')
      ->unshiftOrder('updated_at', Varien_Data_Collection::SORT_ORDER_ASC);
    $this->_applyFilters($collection, $limit, $page, $filters);
    
    $result = array();
    foreach ($collection as $quote) {
      $quoteResult = $this->_toArray($quote, self::$ATTRIBUTE_FILTERS);
      
      //Line Items
      $quoteResult['items'] = array();
      foreach ($quote->getAllItems() as $item) {
        $itemResult = $this->_toArray($item, self::$LINE_ITEM_ATTRIBUTE_FILTERS);
        $quoteResult['items'][] = $itemResult;
      }
      
      $result[] = $quoteResult;
    }
   
    return $result;
  }
  
  public function ids($storeId, $limit = 250, $page = 1) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('sales/quote')
      ->getCollection()
      ->addFieldToFilter('store_id', $storeId)
      ->addFieldToSelect('entity_id');
    $this->_applyFilters($collection, $limit, $page);
    
    $result = array();
    foreach ($collection as $quote) {
      $result[] = $quote->getEntityId();
    }
    return $result;
  }
}