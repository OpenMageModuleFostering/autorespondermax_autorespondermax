<?php

class Autorespondermax_Autorespondermax_Model_Sales_Order_Api extends Autorespondermax_Autorespondermax_Model_Api {
  public static $ATTRIBUTE_FILTERS = array();
  public static $LINE_ITEM_ATTRIBUTE_FILTERS = array('product_options', 'product');
  public static $SHIPMENT_ATTRIBUTE_FILTERS = array();
  
  public function items($storeId, $limit = 250, $page = 1, $filters = array()) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('sales/order')
      ->getCollection()
      ->addFieldToFilter('store_id', $storeId)
      ->addAttributeToSelect('*')
      ->unshiftOrder('updated_at', Varien_Data_Collection::SORT_ORDER_ASC);
    $this->_applyFilters($collection, $limit, $page, $filters);
    
    $result = array();
    foreach ($collection as $order) {
      $orderResult = $this->_toArray($order, self::$ATTRIBUTE_FILTERS);
      
      //Line Items
      $orderResult['items'] = array();
      foreach ($order->getAllItems() as $item) {
        $itemResult = $this->_toArray($item, self::$LINE_ITEM_ATTRIBUTE_FILTERS);
        $orderResult['items'][] = $itemResult;
      }
      
      //Shipments
      $orderResult['shipments'] = array();
      foreach($order->getShipmentsCollection() as $shipment) {
        $shipmentResult = $this->_toArray($shipment, self::$SHIPMENT_ATTRIBUTE_FILTERS);
        $orderResult['shipments'][] = $shipmentResult;
      } 
      
      //Billing Address
      $orderResult['billing_address'] = $this->_toArray($order->getBillingAddress());
      
      $result[] = $orderResult;
    }
   
    return $result;
  }
  
  public function ids($storeId, $limit = 250, $page = 1) {
    $this->_validateStore($storeId);
    
    $collection = Mage::getModel('sales/order')
      ->getCollection()
      ->addFieldToFilter('store_id', $storeId)
      ->addAttributeToSelect('entity_id');
    $this->_applyFilters($collection, $limit, $page);
    
    $result = array();
    foreach ($collection as $order) {
      $result[] = $order->getEntityId();
    }
    return $result;
  }
}