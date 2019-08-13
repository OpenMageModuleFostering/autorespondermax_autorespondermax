<?php

class Autorespondermax_Autorespondermax_Helper_Data extends Mage_Core_Helper_Abstract {
  const CONFIG_STORE_ID = 'autorespondermax/credentials/store_id';
  const CONFIG_TOKEN = 'autorespondermax/credentials/token';
  const CONFIG_API_SECRET = 'autorespondermax/credentials/api_secret';
  const CONFIG_USE_TRACKING = 'autorespondermax/credentials/use_tracking';
  
  const DASHBOARD_HOST_NAME_ENV = 'AUTORESPONDERMAX_DASHBOARD';
  const TRACK_HOST_NAME_ENV = 'AUTORESPONDERMAX_TRACK';
  const API_HOST_NAME_ENV = 'AUTORESPONDERMAX_API';
  const DASHBOARD_HOST_NAME = 'dashboard.autorespondermax.com';
  const TRACK_HOST_NAME = 'atrsp.mx';
  const API_HOST_NAME = 'api.autorespondermax.com';
  
  public static $API_ROLE_RULE_RESOURCES = array(
    'core',
    'core/magento',
    'core/magento/info',
    'core/store',
    'core/store/list',
    'autorespondermax',
    'autorespondermax/general',
    'autorespondermax/general/info',
    'autorespondermax/sales_quote',
    'autorespondermax/sales_quote/list',
    'autorespondermax/sales_quote/ids',
    'autorespondermax/customer',
    'autorespondermax/customer/list',
    'autorespondermax/customer/ids',
    'autorespondermax/product',
    'autorespondermax/product/list',
    'autorespondermax/product/ids',
    'autorespondermax/sales_order',
    'autorespondermax/sales_order/list',
    'autorespondermax/sales_order/ids'
  );
  const API_ROLE_NAME = 'Autoresponder Max';
  
  public function getStoreId($store = null) {
      return Mage::getStoreConfig(self::CONFIG_STORE_ID, $store);
  }
  public function setStoreId($value, $store = null) {
      $this->_save(self::CONFIG_STORE_ID, $value, $store);
      return $this;
  }
  public function getToken($store = null) {
      return Mage::getStoreConfig(self::CONFIG_TOKEN, $store);
  }
  public function setToken($value, $store = null) {
      $this->_save(self::CONFIG_TOKEN, $value, $store);
      return $this;
  }
  public function getApiSecret($store = null) {
      return Mage::getStoreConfig(self::CONFIG_API_SECRET, $store);
  }
  public function setApiSecret($value, $store = null) {
      $this->_save(self::CONFIG_API_SECRET, $value, $store);
      return $this;
  }
  public function getUseTracking($store = null) {
      return Mage::getStoreConfig(self::CONFIG_USE_TRACKING, $store);
  }
  public function setUseTracking($value, $store = null) {
      $this->_save(self::CONFIG_USE_TRACKING, $value, $store);
      return $this;
  }
  
  public function connected($store = null) {
      $storeId = $this->getStoreId($store);
      return empty($storeId) ? false : true;
  }
  
  public function disconnected() {
      return !$this->connected();
  }
  
  public function createApiUsername($store) {
    return substr('autorespondermax_store_'.$store->getCode(), 0, 40); //Only 40 characters is allowed for username
  }
  
  public function createApiEmail($store) {
    return 'platform+store_'.$store->getCode().'@autorespondermax.com';
  }
  
  public function createToken($size = 16) {
    if(function_exists('openssl_random_pseudo_bytes')) {
      return bin2hex(openssl_random_pseudo_bytes($size));
    }
    else {
      return bin2hex(mcrypt_create_iv($size, MCRYPT_DEV_RANDOM));
    }
  }
  
  public function refresh($store) {
    //Get the new data
    $data = Mage::helper('autorespondermax/api')->getStore($store);
    
    //Update the configuration
    //$helper->setStoreId(, $store);
    //$helper->setToken(, $store);
    //$helper->setApiSecret(, $store);
    $this->setUseTracking($data['use_tracking'], $store);
  }
  
  /**
  * Should we verify SSL?  No, if in Developer Mode
  * 
  * @return bool
  */
  public function sslVerifyHost() {
    return Mage::getIsDeveloperMode() ? false : true;
  }
  
  /**
  * Host name to use for dashboard
  * 
  * @return string
  */
  public function dashboardHostName() {
    return $this->hostName(self::DASHBOARD_HOST_NAME_ENV, self::DASHBOARD_HOST_NAME);
  }
  
  /**
  * Host name to use for tracking
  * 
  * @return string
  */
  public function trackHostName() {
    return $this->hostName(self::TRACK_HOST_NAME_ENV, self::TRACK_HOST_NAME);
  }
  
  /**
  * Host name to use for API
  * 
  * @return string
  */
  public function apiHostName() {
    return $this->hostName(self::API_HOST_NAME_ENV, self::API_HOST_NAME);
  }
  
  
  /**
  * Save a configuration variable and delete it if null
  *
  * @param string $path
  * @param string $value
  * @param Mage_Core_Model_Store $store
  */
  private function _save($path, $value, $store = null) {
      if(is_null($store)) {
          $store = Mage::app()->getStore();
      }
      
      //Delete value if null
      if(is_null($value)) {
          Mage::getConfig()->deleteConfig($path, 'stores', $store->getId());
      }
      else {
          Mage::getConfig()->saveConfig($path, $value, 'stores', $store->getId());
      }
  }
  
  /**
  * @param string $env environment variable name
  * @param string $default default host name
  * @return string host name to use
  */
  private function hostName($env, $default) {
    $value = $this->getEnvironmentVariable($env);
    if(empty($value)) {
      $value = $default;
    }
    return $value;
  }
  
  /**
  * @param string $varname
  * @return string value of environment variable
  */
  private function getEnvironmentVariable($varname) {
    $value = null;
    if(function_exists('apache_getenv')) {
      $value = apache_getenv($varname, true);
    }
    if(empty($value)) {
      $value = getenv($varname);
    }
    return $value;
  }
}