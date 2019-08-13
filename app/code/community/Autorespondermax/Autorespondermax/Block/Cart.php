<?php

class Autorespondermax_Autorespondermax_Block_Cart extends Mage_Checkout_Block_Cart {
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
  
  public function getEmail() {
    return $this->_getNotEmpty(array(
      $this->getQuote()->getCustomerEmail(),
      $this->getCustomer()->getEmail()
    ));
  }
  
  public function getFirstname() {
    return $this->_getNotEmpty(array(
      $this->getQuote()->getCustomerFirstname(),
      $this->getCustomer()->getFirstname()
    ));
  }
  
  public function getLastName() {
    return $this->_getNotEmpty(array(
      $this->getQuote()->getCustomerLastname(),
      $this->getCustomer()->getLastname()
    ));
  }
  
  public function getUri($format = self::FORMAT) {
    return $this->_getTrackHelper()->createUri($this->getToken(), 'cart', $format);
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