<?php

class Autorespondermax_Autorespondermax_Block_System_Config_AutocompleteOff extends Mage_Adminhtml_Block_System_Config_Form_Field {
  protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
    $parent = parent::_getElementHtml($element);
    $child = null;
    
    //Attempt to add attribute `autocomplete` to element
    try {
      $dom = new DOMDocument();
      $dom->loadHTML($parent);
      $xpath = new DOMXPath($dom);
      $node = $xpath->query('//input')->item(0);
      if(!is_null($node)) {
        $node->setAttribute('autocomplete', 'off');
        $child = $dom->saveHtml($node);
      }
    }
    catch(Exception $e) {
      Mage::logException($e);
    }
    
    if(empty($child) || count(trim($child)) < 1) {
      $child = $parent;
    }
    return $child;
  }
}