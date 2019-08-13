<?php

class Autorespondermax_Autorespondermax_Block_System_Config_AutocompleteOff extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $dom = new DOMDocument();
        $dom->loadHTML(parent::_getElementHtml($element));
        $xpath = new DOMXPath($dom);
        $node = $xpath->query('//input')->item(0);
        if(!is_null($node)) {
            $node->setAttribute('autocomplete', 'off');
        }
        return $dom->saveHtml($node);
    }
}