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

$subscribers = Mage::getModel('newsletter/subscriber')->getCollection();
foreach($subscribers as $subscriber) {
  $_subscriber = Mage::getModel('autorespondermax/subscriber')->loadBySubscriber($subscriber);
  $_subscriber->setUpdatedAt('1970-01-01 00:00:01');
  $_subscriber->save();
}