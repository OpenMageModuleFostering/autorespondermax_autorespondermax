<?php

class Autorespondermax_Autorespondermax_Model_System_Config_Form_Field_Button extends Varien_Data_Form_Element_Abstract {
  protected $_buttonData = array();
  
  public function getElementHtml() {
    $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock('adminhtml/widget_button');
    $buttonBlock->setBlockId('test');
    $html = $buttonBlock->setData($this->_buttonData)->toHtml();
    
    return $html;
  }
  
  public function getButtonData() {
    return $this->_buttonData;
  }
  
  public function setButtonData($data) {
    $this->_buttonData = $data;
  }
}