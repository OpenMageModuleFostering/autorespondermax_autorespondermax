<?php

class Autorespondermax_Autorespondermax_Block_Convert_Multishipping extends Mage_Checkout_Block_Multishipping_Success {
  const FORMAT = 'js';
  
  protected $_helper = null;
  protected $_trackHelper = null;
  
  public function useTracking() {
    $store = $this->getStore();
    
    return $this->getMyHelper()->connected($store) && $this->getMyHelper()->getUseTracking($store);
  }
  
  public function getToken() {
    $store = $this->getStore();
    
    return $this->getMyHelper()->getToken($store);
  }
  
  public function getOrder() {
    $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
    if(is_null($orderId)) {
      return null;
    }
    return Mage::getModel('sales/order')->load($orderId);
  }
  
  public function getCustomer() {
    $order = $this->getOrder();
    if(is_null($order)) {
      return null;
    }
    return Mage::getModel('customer/customer')->load($order->getCustomerId());
  }
  
  public function getEmail() {
    return $this->_getNotEmpty(array(
      $this->getCustomer()->getEmail(),
      $this->getOrder()->getBillingAddress()->getEmail()
    ));
  }
  
  public function getFirstname() {
    return $this->_getNotEmpty(array(
      $this->getCustomer()->getFirstname(),
      $this->getOrder()->getBillingAddress()->getFirstname()
    ));
  }
  
  public function getLastName() {
    return $this->_getNotEmpty(array(
      $this->getCustomer()->getLastname(),
      $this->getOrder()->getBillingAddress()->getLastname()
    ));
  }
  
  public function getUri($format = self::FORMAT) {
    return $this->_getTrackHelper()->createUri($this->getToken(), 'roi', $format);
  }
  
  public function getMyHelper() {
    if(is_null($this->_helper)) {
      $this->_helper = Mage::helper('autorespondermax');
    }
    
    return $this->_helper;
  }
  
  
  protected function _getNotEmpty($values) {
    foreach($values as $value) {
      if(!is_null($value) && !empty($value)) {
        return $value;
      }
    }
    
    return null;
  }
  
  protected function _getStore() {
    return Mage::app()->getStore();
  }
  
  protected function _getTrackHelper() {
    if(is_null($this->_trackHelper)) {
      $this->_trackHelper = Mage::helper('autorespondermax/track');
    }
    
    return $this->_trackHelper;
  }
}