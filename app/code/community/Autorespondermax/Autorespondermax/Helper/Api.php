<?php

class Autorespondermax_Autorespondermax_Helper_Api extends Mage_Core_Helper_Abstract {
  const VERSION = 'v1';
  const FORMAT = 'json';
  public static $CONFIG = array(
    'timeout' => 60,
    'maxredirects' => 0
  );
  public static $HEADERS = array(
    'Content-Type: application/json'
  );
  
  protected $_helper = null;
  
  public function getStore($store) {
    $helper = $this->_getMyHelper();
    
    $response = $this->_request(
      $helper->getToken($store), $helper->getApiSecret($store),
      Zend_Http_Client::GET, 
      'https://'.$this->_getMyHelper()->apiHostName().'/'.urlencode(self::VERSION).'/store.'.urlencode(self::FORMAT)
    );
    
    if(!$response->isSuccessful()) {
      Mage::throwException($response->getBody());
    }
    
    return Mage::helper('core')->jsonDecode($response->getBody());
  }
  
  public function deleteStore($store) {
    $helper = $this->_getMyHelper();
    
    $response = $this->_request(
      $helper->getToken($store), $helper->getApiSecret($store),
      Zend_Http_Client::DELETE, 
      'https://'.$this->_getMyHelper()->apiHostName().'/'.urlencode(self::VERSION).'/store.'.urlencode(self::FORMAT)
    );
    
    return $response->isSuccessful();
  }
  
  protected function _request($token, $apiSecret, $method, $url, $body = null) {
    //$adapter = new Varien_Http_Adapter_Curl();
    $adapter = Mage::helper('autorespondermax/curl');
    $config = self::$CONFIG; //Arrays assigned by copy
    $config['verifypeer'] = $this->_getMyHelper()->sslVerifyHost();
    $config['verifyhost'] = $this->_getMyHelper()->sslVerifyHost() ? 2 : 0;
    $adapter->setConfig($config);
    $adapter->addOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $adapter->addOption(CURLOPT_USERPWD, "$token:$apiSecret");
    $adapter->addOption(CURLOPT_CUSTOMREQUEST, $method);
    $adapter->write($method, $url, '1.1', self::$HEADERS, $body);
    $response = Zend_Http_Response::fromString($adapter->read());
    $adapter->close();
    return $response;
  }
  
  
  protected function _getMyHelper() {
    if(is_null($this->_helper)) {
      $this->_helper = Mage::helper('autorespondermax');
    }
    
    return $this->_helper;
  }
}