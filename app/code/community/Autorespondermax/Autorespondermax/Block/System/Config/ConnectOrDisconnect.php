<?php

class Autorespondermax_Autorespondermax_Block_System_Config_ConnectOrDisconnect extends Mage_Adminhtml_Block_System_Config_Form_Field {
  protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
    $store = $this->_currentStore();
    return Mage::helper('autorespondermax')->connected($store) ? $this->_disconnectHtml($element) : $this->_connectHtml($element);
  }
  
  private function _connectHtml(Varien_Data_Form_Element_Abstract $element) {
    $helper = Mage::helper('autorespondermax');
    
    //Hidden field for state
    $element->addField($element->getHtmlId().'[connecting]', 'hidden', array(
      'name' => $element->getName().'[connecting]',
      'value' => '1',
      'disabled' => 'disabled'
    ));
    
    //Create fields within fieldset
    $usernameElement = $element->addField($element->getHtmlId().'[username]', 'text', array(
      'label' => $helper->__('Username'),
      'name' => $element->getName().'[username]'
    ), false);
    $usernameElement->setRenderer(Mage::getBlockSingleton('autorespondermax/system_config_autocompleteOff'));
    $usernameElement->setComment($helper->__('Don\'t have an account?  <a href="https://autorespondermax.com/signup">Sign up now!</a>'));
    $passwordElement = $element->addField($element->getHtmlId().'[password]', 'password', array(
      'label' => $helper->__('Password'),
      'name' => $element->getName().'[password]',
      'autocomplete' => 'off'
    ), false);
    $passwordElement->setRenderer(Mage::getBlockSingleton('autorespondermax/system_config_autocompleteOff'));
    $passwordElement->setComment($helper->__('Forget your password?  <a href="https://dashboard.autorespondermax.com/customer/forgot_password">Recover it here.</a>'));
    
    //Create button
    $javascript = <<<JAVASCRIPT
      (function(element){
        element.addClassName('disabled');
      
        try{
          $('autorespondermax_settings_connect_disconnect[connecting]').enable();
          $('autorespondermax_credentials_api_secret').disable();
        } catch(e){}
      })($(this));
      return true;
JAVASCRIPT;
    $buttonElement = Mage::getModel('autorespondermax/system_config_form_field_button');
    $buttonElement->setId('connect');
    $buttonElement->setButtonData(array(
      'id' => $element->getHtmlId().'[connect]',
      'label' => $helper->__('Connect'),
      'type' => 'submit',
      'onclick' => "javascript: $javascript"
    ));
    $buttonElement->setRenderer($element->getForm()->getFieldsetElementRenderer());
    $element->addElement($buttonElement, false);
    
    //Disable comment when rendering
    $element->setComment($helper->__('Enter your Autoresponder Max Dashboard Username and Password and press Connect to create connection.'));
    
    //Render fieldset using fieldset renderer (Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset)
    $html = $element->getForm()->getFieldsetRenderer()->render($element);
    
    //Put comment back in place
    $element->setComment($helper->__('Connects by creating a SOAP API User and Role within Magento and creating a store within Autoresponder Max.'));
    
    return $html;
  }
  
  private function _disconnectHtml(Varien_Data_Form_Element_Abstract $element) {
    $helper = Mage::helper('autorespondermax');
    $store = $this->_currentStore();
    
    //Hidden field for state
    $element->addField($element->getHtmlId().'[disconnecting]', 'hidden', array(
      'name' => $element->getName().'[disconnecting]',
      'value' => '1',
      'disabled' => 'disabled'
    ));
    $element->addField($element->getHtmlId().'[refreshing]', 'hidden', array(
      'name' => $element->getName().'[refreshing]',
      'value' => '1',
      'disabled' => 'disabled'
    ));
    
    //Disconnect button
    $confirmation = Mage::helper('core')->jsonEncode($helper->__('Are you sure you would like to disconnect this store view?'));
    $javascript = <<<JAVASCRIPT
    if(!window.confirm($confirmation)) {
      return false;
    }
    
    (function(element){
      var formElement = element.up('form');
      
      element.addClassName('disabled');
      formElement.select('input[name^="groups[credentials][fields]"]').invoke('clear');
      
      try{
        $('autorespondermax_settings_connect_disconnect[disconnecting]').enable();
      } catch(e){}
    })($(this));
    
    return true;
JAVASCRIPT;
    $javascript = $this->htmlEscape($javascript);
    $disconnectButtonElement = Mage::getModel('autorespondermax/system_config_form_field_button');
    $disconnectButtonElement->setId('disconnect');
    $disconnectButtonElement->setButtonData(array(
      'id' => $element->getHtmlId().'[disconnect]',
      'name' => $element->getName().'[disconnect]',
      'type' => 'submit',
      'label' => $helper->__('Disconnect'),
      'onclick' => "javascript: $javascript"
    ));
    $disconnectButtonElement->setRenderer($element->getForm()->getFieldsetElementRenderer());
    $element->addElement($disconnectButtonElement, false);
    
    //Refresh button
    $javascript = <<<JAVASCRIPT
    (function(element){
      element.addClassName('disabled');
      try{
        $('autorespondermax_settings_connect_disconnect[refreshing]').enable();
      } catch(e){}
    })($(this));
    
    return true;
JAVASCRIPT;
    $javascript = $this->htmlEscape($javascript);
    $refreshButtonElement = Mage::getModel('autorespondermax/system_config_form_field_button');
    $refreshButtonElement->setId('refresh');
    $refreshButtonElement->setButtonData(array(
      'id' => $element->getHtmlId().'[refresh]',
      'name' => $element->getName().'[refresh]',
      'type' => 'submit',
      'label' => $helper->__('Refresh'),
      'onclick' => "javascript: $javascript"
    ));
    $refreshButtonElement->setRenderer($element->getForm()->getFieldsetElementRenderer());
    
    $element->addElement($refreshButtonElement, false);
    
    //Links
    $element->addField($element->getHtmlId().'[link]', 'link', array(
      'label' => null,
      'href' => Mage::helper('autorespondermax/dashboard')->getStoreEditUrl($store),
      'value' => $helper->__('Manage your store within Autoresponder Max')
    ));
    
    //Disable comment when rendering
    $element->setComment($helper->__('Press Disconnect to remove connection with Autoresponder Max.'));

    //Render fieldset using fieldset renderer (Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset)
    $html = $element->getForm()->getFieldsetRenderer()->render($element);

    //Put comment back in place
    $element->setComment($helper->__('Disconnects by removing SOAP API User within Magento and deleting store within Autoresponder Max.'));

    return $html;
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
}