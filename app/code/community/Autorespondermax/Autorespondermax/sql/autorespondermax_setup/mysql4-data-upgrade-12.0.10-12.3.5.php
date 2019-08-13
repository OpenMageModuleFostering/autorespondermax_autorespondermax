<?php

//Find the api role
$apiUserRole = Mage::getModel('api/role')->getCollection()->setRolesFilter()->addFieldToFilter('role_name', Autorespondermax_Autorespondermax_Helper_Data::API_ROLE_NAME)->getFirstItem();

if($apiUserRole->isObjectNew()) {
  $apiUserRole = $apiUserRole
    ->setRoleName(Autorespondermax_Autorespondermax_Helper_Data::API_ROLE_NAME)
    ->setRoleType('G')
    ->save();
}

Mage::getModel('api/rules')->setRoleId($apiUserRole->getId())
  ->setResources(Autorespondermax_Autorespondermax_Helper_Data::$API_ROLE_RULE_RESOURCES)
  ->saveRel();