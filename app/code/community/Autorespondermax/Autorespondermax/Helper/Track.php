<?php

class Autorespondermax_Autorespondermax_Helper_Track extends Mage_Core_Helper_Abstract {
  const VERSION = 'v5';
  const FORMAT = 'js';
  
  protected $_helper = null;
  
  public function createURI($storeToken, $path = '', $format = self::FORMAT) {
    return 'https://'.$this->_getMyHelper()->trackHostName().'/'.urlencode($storeToken).'/'.self::VERSION.'/'.urlencode($path).(is_null($format) ? '' : '.'.urlencode($format));
  }
  
  
  protected function _getMyHelper() {
    if(is_null($this->_helper)) {
      $this->_helper = Mage::helper('autorespondermax');
    }
    
    return $this->_helper;
  }
}