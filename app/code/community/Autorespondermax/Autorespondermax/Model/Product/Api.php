<?php

class Autorespondermax_Autorespondermax_Model_Product_Api extends Autorespondermax_Autorespondermax_Model_Api {
  public function items($storeId, $limit = 250, $page = 1, $filters = array()) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('catalog/product')
      ->setStoreId($storeId)
      ->getCollection()
      ->addStoreFilter($storeId)
      ->addAttributeToSelect('*')
      ->unshiftOrder('updated_at', Varien_Data_Collection::SORT_ORDER_ASC);
    $this->_applyFilters($collection, $limit, $page, $filters);
    
    $result = array();
    foreach ($collection as $product) {
      $productResult = $this->_toArray($product);
      
      //Is Salable?
      $productResult['is_salable'] = $product->getIsSalable();
      
      //Is Available?
      $productResult['is_available'] = $product->isAvailable();
      
      //Stock
      $productResult['stock_item'] = $product->getStockItem()->toArray();
      
      $result[] = $productResult;
    }
   
    return $result;
  }
  
  public function ids($storeId, $limit = 250, $page = 1) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('catalog/product')
      ->setStoreId($storeId)
      ->getCollection()
      ->addStoreFilter($storeId)
      ->addAttributeToSelect('entity_id');
    $this->_applyFilters($collection, $limit, $page);
    
    $result = array();
    foreach ($collection as $product) {
      $result[] = $product->getEntityId();
    }
    return $result;
  }
}