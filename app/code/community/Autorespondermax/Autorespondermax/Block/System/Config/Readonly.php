<?php

class Autorespondermax_Autorespondermax_Block_System_Config_Readonly extends Mage_Adminhtml_Block_System_Config_Form_Field {
  protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
    $element->setReadonly(true, true);
    return parent::_getElementHtml($element);
  }
}