<?php

class Autorespondermax_Autorespondermax_Helper_Dashboard extends Mage_Core_Helper_Abstract {
  public static $CONFIG = array(
    'timeout' => 60,
    'maxredirects' => 0
  );
  public static $HEADERS = array(
    'Content-Type: application/json'
  );
  
  protected $_helper = null;
  
  public function createStore($username, $password, $store, $apiUsername, $apiKey, $storeToken, $storeApiSecret) {
    //Create body based on store and args
    $url = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false));
    $secureUrl = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true));
    $mediaUrl = parse_url($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false));
    $body = array(
      'store' => array(
        'name' => $store->getGroup()->getName(),
        'token' => $storeToken,
        'api_secret' => $storeApiSecret,
        'use_tracking' => true,
        'character_set' => $store->getConfig('api/config/charset'),
        'currency' => $store->getDefaultCurrencyCode(),
        'virtual_time_zone_id' => $store->getConfig('general/locale/timezone'),
        'platform_type' => 'Magento1Store',
        'magento1_store' => array(
          'virtual_type' => 'Magento1Store',
          'host_name' => $url['host'],
          'relative_base_url' => $url['path'],
          'media_host_name' => $mediaUrl['host'],
          'media_relative_base_url' => $mediaUrl['path'],
          'website_id' => $store->getWebsiteId(),
          'group_id' => $store->getGroupId(),
          'store_id' => $store->getId(),
          'store_code' => $store->getCode(),
          'user_name' => $apiUsername,
          'api_key' => $apiKey
        )
      )
    );
    if($secureUrl['scheme'] === 'https') {
      $body['store']['magento1_store']['secure_host_name'] = $secureUrl['host'];
      $body['store']['magento1_store']['secure_relative_base_url'] = $secureUrl['path'];
    }
    
    //POST to dashboard
    $dashboardHostname = $this->_getMyHelper()->dashboardHostName();
    //$adapter = new Varien_Http_Adapter_Curl();
    $adapter = Mage::helper('autorespondermax/curl');
    $config = self::$CONFIG; //Arrays assigned by copy
    $config['verifypeer'] = $this->_getMyHelper()->sslVerifyHost();
    $config['verifyhost'] = $this->_getMyHelper()->sslVerifyHost() ? 2 : 0;
    $adapter->setConfig($config);
    $adapter->addOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $adapter->addOption(CURLOPT_USERPWD, "$username:$password");
    $adapter->write(Zend_Http_Client::POST, "https://$dashboardHostname/stores/create/magento.json", '1.1', self::$HEADERS, Mage::helper('core')->jsonEncode($body));
    $response = Zend_Http_Response::fromString($adapter->read());
    $adapter->close();
    
    //Check for unauthorized or error
    if($response->getStatus() === 401 || $response->getStatus() === 403) {
      Mage::throwException($this->_getMyHelper()->__('Username or password is incorrect'));
    }
    if($response->getStatus() === 500) {
      Mage::throwException($this->_getMyHelper()->__('Something went wrong, please contact support'));
    }
    
    $data = Mage::helper('core')->jsonDecode($response->getBody());
    if(is_null($data)) {
      Mage::throwException($this->_getMyHelper()->__('Something went wrong, please contact support'));
    }
    
    //Parse the response
    if($response->isSuccessful()) {
      if(!preg_match('/^(?:\/stores\/)(\d+)$/', $data['success'], $matches)) {
        Mage::throwException($this->_getMyHelper()->__('Something went wrong, please contact support'));
      }
      return $matches[1];
    }
    else {
      Mage::throwException(
        implode("\r\n", array_values($data))
      );
    }
  }
  
  public function getStoreEditUrl($store) {
    return 'https://'.$this->_getMyHelper()->dashboardHostName().'/stores/'.urlencode($this->_getMyHelper()->getStoreId($store)).'/edit';
  }
  
  public function getStoreDashboardUrl($store) {
    return 'https://'.$this->_getMyHelper()->dashboardHostName().'/stores/'.urlencode($this->_getMyHelper()->getStoreId($store));
  }
  
  
  protected function _getMyHelper() {
    if(is_null($this->_helper)) {
      $this->_helper = Mage::helper('autorespondermax');
    }
  
    return $this->_helper;
  }
}