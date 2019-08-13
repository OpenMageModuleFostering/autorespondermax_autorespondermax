<?php

class Autorespondermax_Autorespondermax_Model_Api extends Mage_Api_Model_Resource_Abstract {
  protected function _applyFilters(&$collection, $limit = 250, $page = 1, $filters = array()) {
    $collection->setPageSize($limit)->setCurPage($page);
    
    //$filters = Mage::helper('api')->parseFilters($filters);
    $filters = $this->parseFilters($filters);
    try {
      foreach ($filters as $field => $value) {
       $collection->addFieldToFilter($field, $value);
      }
    }
    catch(Mage_Core_Exception $e) {
      $this->_fault('filters_invalid', $e->getMessage());
    }
    
    //Fault when page has gone too far
    if($page !== $collection->getCurPage()) {
      $this->_fault('page_out_of_range', Mage::helper('autorespondermax')->__('Page out of range'));
    }
  }
  
  /**
  * Converts object to array, inspired by Mage_Checkout_Model_Api_Resource#_getAttributes
  * 
  * @param Mage_Core_Model_Abstract $object
  * @param array $filters list of attribute names to exclude
  */
  protected function _toArray($object, $filters = array()) {
    $result = array();
    
    if(is_object($object)) {
      foreach($object->getData() as $attribute => $value) {
        if(is_object($value)){ continue; } //Skip embedded objects
        if(in_array($attribute, $filters)){ continue; } //Skip if filtered
        $result[$attribute] = $value;
      }
    }
    
    //Cleanup any invalid data
    $this->_removeInvalidUTF8($result);
    
    return $result;
  }
  
  /**
  * @param array $array
  * @return array
  */
  protected function _removeInvalidUTF8(&$array) {
    foreach($array as $key => $value) {
      if(is_array($value)) {
        $this->_removeInvalidUTF8($value);
      }
      elseif(is_string($value)) {
        #Check for valid UTF-8 characters
        #See http://stackoverflow.com/questions/6723562/how-to-detect-malformed-utf-8-string-in-php
        if(!preg_match('//u', $value)) {
          $array[$key] = null;
        }
      }
    }
  }
  
  protected function _getStore($storeId) {
    try {
      return Mage::app()->getStore($storeId);
    }
    catch(Mage_Core_Model_Store_Exception $e) {
      return null;
    }
  }
  
  protected function _validateStore($storeId) {
    if(is_null($this->_getStore($storeId))) {
      $this->_fault('store_id_invalid', Mage::helper('autorespondermax')->__('Store Id is invalid'));
    }
  }
  
  /**
  * Backport for 1.5
  *
  * Need filter parsing functions
  */
  /**
   * Magento
   *
   * NOTICE OF LICENSE
   *
   * This source file is subject to the Open Software License (OSL 3.0)
   * that is bundled with this package in the file LICENSE.txt.
   * It is also available through the world-wide-web at this URL:
   * http://opensource.org/licenses/osl-3.0.php
   * If you did not receive a copy of the license and are unable to
   * obtain it through the world-wide-web, please send an email
   * to license@magentocommerce.com so we can send you a copy immediately.
   *
   * DISCLAIMER
   *
   * Do not edit or add to this file if you wish to upgrade Magento to newer
   * versions in the future. If you wish to customize Magento for your
   * needs please refer to http://www.magentocommerce.com for more information.
   *
   * @category    Mage
   * @package     Mage_Api
   * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
   * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
   */
  /**
   * Parse filters and format them to be applicable for collection filtration
   *
   * @param null|object|array $filters
   * @param array $fieldsMap Map of field names in format: array('field_name_in_filter' => 'field_name_in_db')
   * @return array
   */
  private function parseFilters($filters, $fieldsMap = null)
  {
      // if filters are used in SOAP they must be represented in array format to be used for collection filtration
      if (is_object($filters)) {
          $parsedFilters = array();
          // parse simple filter
          if (isset($filters->filter) && is_array($filters->filter)) {
              foreach ($filters->filter as $field => $value) {
                  if (is_object($value) && isset($value->key) && isset($value->value)) {
                      $parsedFilters[$value->key] = $value->value;
                  } else {
                      $parsedFilters[$field] = $value;
                  }
              }
          }
          // parse complex filter
          if (isset($filters->complex_filter) && is_array($filters->complex_filter)) {
              $parsedFilters += $this->_parseComplexFilter($filters->complex_filter);
          }

          $filters = $parsedFilters;
      }
      // make sure that method result is always array
      if (!is_array($filters)) {
          $filters = array();
      }
      // apply fields mapping
      if (isset($fieldsMap) && is_array($fieldsMap)) {
          foreach ($filters as $field => $value) {
              if (isset($fieldsMap[$field])) {
                  unset($filters[$field]);
                  $field = $fieldsMap[$field];
                  $filters[$field] = $value;
              }
          }
      }
      return $filters;
  }

  /**
   * Parses complex filter, which may contain several nodes, e.g. when user want to fetch orders which were updated
   * between two dates.
   *
   * @param array $complexFilter
   * @return array
   */
  private function _parseComplexFilter($complexFilter)
  {
      $parsedFilters = array();

      foreach ($complexFilter as $filter) {
          if (!isset($filter->key) || !isset($filter->value)) {
              continue;
          }

          list($fieldName, $condition) = array($filter->key, $filter->value);
          $conditionName = $condition->key;
          $conditionValue = $condition->value;
          $this->formatFilterConditionValue($conditionName, $conditionValue);

          if (array_key_exists($fieldName, $parsedFilters)) {
              $parsedFilters[$fieldName] += array($conditionName => $conditionValue);
          } else {
              $parsedFilters[$fieldName] = array($conditionName => $conditionValue);
          }
      }

      return $parsedFilters;
  }

  /**
   * Convert condition value from the string into the array
   * for the condition operators that require value to be an array.
   * Condition value is changed by reference
   *
   * @param string $conditionOperator
   * @param string $conditionValue
   */
  private function formatFilterConditionValue($conditionOperator, &$conditionValue)
  {
      if (is_string($conditionOperator) && in_array($conditionOperator, array('in', 'nin', 'finset'))
          && is_string($conditionValue)
      ) {
          $delimiter = ',';
          $conditionValue = explode($delimiter, $conditionValue);
      }
  }
}