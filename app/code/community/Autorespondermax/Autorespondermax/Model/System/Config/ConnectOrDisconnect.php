<?php

class Autorespondermax_Autorespondermax_Model_System_Config_ConnectOrDisconnect extends Mage_Core_Model_Config_Data {
  protected function _afterLoad() {
    //Value will always be reset
    $this->setValue(null);
  }

  //protected function _beforeSave() {
  public function save() {
    $data = $this->getValue();
    if(!is_array($data)) {
      $data = array();
    }
    
    //Decide what action to take, if any
    if(array_key_exists('connecting', $data)) {
      $this->_connect($data);
    }
    else if(array_key_exists('disconnecting', $data)) {
      $this->_disconnect($data);
    }
    else if(array_key_exists('refreshing', $data)) {
      $this->_refresh($data);
    }
    else {
      //Nothing
    }
    
    //No actual value will be stored for this
    $this->setValue(null);
    
    //return $this;
    return parent::save();
  }

  private function _connect($data) {
    $helper = Mage::helper('autorespondermax');
    $store = $this->_currentStore();
    $username = $data['username'];
    $password = $data['password'];
    
    if(empty($username) || strlen($username) < 3) {
      Mage::throwException($helper->__('Please provide a username.'));
    }
    if(empty($password)) {
      Mage::throwException($helper->__('Please provide a password.'));
    }
    
    //Create user and password for API
    $storeToken = $helper->createToken(16);
    $storeApiSecret = $helper->createToken(16);
    $apiUsername = $helper->createApiUsername($store);
    $apiKey = $helper->createToken(32);
    
    //Find the api role
    $apiUserRole = Mage::getModel('api/role')->getCollection()->setRolesFilter()->addFieldToFilter('role_name', Autorespondermax_Autorespondermax_Helper_Data::API_ROLE_NAME)->getFirstItem();
    //Create the Role if not available yet
    if($apiUserRole->isObjectNew()) {
      $apiUserRole = $apiUserRole
        ->setRoleName(Autorespondermax_Autorespondermax_Helper_Data::API_ROLE_NAME)
        ->setRoleType('G')
        ->save();
      Mage::getModel('api/rules')->setRoleId($apiUserRole->getId())
        ->setResources(Autorespondermax_Autorespondermax_Helper_Data::$API_ROLE_RULE_RESOURCES)
        ->saveRel();
    }
    
    //Create or save user and associated role
    $apiUserModel = Mage::getModel('api/user')
      ->loadByUsername($apiUsername)
      ->setUsername($apiUsername)
      ->setEmail($helper->createApiEmail($store))
      ->setFirstname('Autoresponder')
      ->setLastname('Max')
      ->setIsActive(true)
      ->setApiKey($apiKey)
      ->save();
    $apiUserModel->setRoleIds( array($apiUserRole->getId()) )
      ->setRoleUserId($apiUserModel->getUserId())
      ->saveRelations();
    
    //Must commit current transaction to test the SOAP api
    $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
    $transaction->commit();
    $transaction->commit(); //Weird?
    
    try {
      //Create the store in Autoresponder Max
      $storeId = Mage::helper('autorespondermax/dashboard')->createStore($username, $password, $store, $apiUsername, $apiKey, $storeToken, $storeApiSecret);
            
      $helper->setStoreId($storeId, $store);
      $helper->setToken($storeToken, $store);
      $helper->setApiSecret($storeApiSecret, $store);
      $helper->setUseTracking(true, $store);
    }
    catch(Exception $e) {
      //Silently try and delete the user
      try {$apiUserModel->delete();} catch(Exception $other){}
      
      //Re-throw
      throw $e;
    }
    
    //Clear any cache for configuration
    $this->_clearConfigCache();
    
    //Add success message
    Mage::getSingleton('core/session')->addNotice($helper->__('Success! Autoresponder Max is now connected to your store.  <a href="'.Mage::helper('autorespondermax/dashboard')->getStoreDashboardUrl($store).'">Setup your email campaigns</a> within Autoresponder Max.'));
  }
  
  private function _disconnect($data) {
    $helper = Mage::helper('autorespondermax');
    $store = $this->_currentStore();
    
    try {
      $result = Mage::helper('autorespondermax/api')->deleteStore($store);
      if(!$result) {
        Mage::throwException('Not deleted');
      }
    }
    catch(Exception $e){
      Mage::getSingleton('core/session')->addWarning('Store has not been deleted within Autoresponder Max, <a href="'.Mage::helper('autorespondermax/dashboard')->getStoreDashboardUrl($store).'">please delete it manually</a>.');
    }
    
    //Delete the API user
    $apiUsername = $helper->createApiUsername($store);
    $apiUserModel = Mage::getModel('api/user')
      ->loadByUsername($apiUsername)
      ->delete();
    
    //Just reset all the fields
    $helper = Mage::helper('autorespondermax');
    $helper->setStoreId(null, $store);
    $helper->setToken(null, $store);
    $helper->setApiSecret(null, $store);
    $helper->setUseTracking(null, $store);
    
    $this->_clearConfigCache();
  }
  
  private function _refresh($data) {
    Mage::helper('autorespondermax')->refresh($this->_currentStore());
    $this->_clearConfigCache();
  }
  
  private function _currentStore() {
    $store = null;
    
    //See http://magento.stackexchange.com/questions/6833/how-to-get-current-store-id-from-current-scope-in-admin
    $code = Mage::app()->getRequest()->getParam('store');
    if(!empty($code)) {
      $store = Mage::getModel('core/store')->getCollection()->addFieldToFilter('code', $code)->getFirstItem();
    }
    
    /*$code = Mage::getSingleton('adminhtml/config_data')->getStore();
    if(!empty($code)) {
      $store = Mage::getModel('core/store')->load($code);
    }
    if(is_null($store)) {
      $store = Mage::app()->getStore();
    }*/
    
    return $store;
  }
  
  private function _clearConfigCache() {
    Mage::getConfig()->reinit();
    Mage::app()->reinitStores();
  }
}