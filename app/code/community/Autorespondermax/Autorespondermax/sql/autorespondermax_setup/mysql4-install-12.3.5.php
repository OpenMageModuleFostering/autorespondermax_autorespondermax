<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
  ->newTable($installer->getTable('autorespondermax/subscriber'))
  ->addColumn(
    'autorespondermax_subscriber_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER, null,
    array(
      'identity'  => true,
      'auto_increment' => true,
      'unsigned'  => true,
      'nullable'  => false,
      'primary'   => true
    )
  )
  ->addColumn(
    'store_id', 
    Varien_Db_Ddl_Table::TYPE_SMALLINT,
    null,
    array(
      'unsigned'  => true,
      'nullable' => false
    )
  )
  ->addColumn(
    'subscriber_id', 
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    array(
      'unsigned'  => true,
      'nullable' => false
    )
  )
  ->addColumn(
    'updated_at', 
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP, //TYPE_DATETIME
    null, 
    array(
      'nullable'  => false,
      'default' => '1970-01-01 00:00:01'
    )
  );

$table->addForeignKey(
  'FK_ARMAX_SUBSCRIBER_STORE',
  'store_id',
  $installer->getTable('core/store'),
  'store_id',
  Varien_Db_Ddl_Table::ACTION_CASCADE,
  Varien_Db_Ddl_Table::ACTION_CASCADE
);
$table->addForeignKey(
  'FK_ARMAX_SUBSCRIBER_SUBSCRIBER',
  'subscriber_id',
  $installer->getTable('newsletter/subscriber'),
  'subscriber_id',
  Varien_Db_Ddl_Table::ACTION_CASCADE,
  Varien_Db_Ddl_Table::ACTION_CASCADE
);
$table->addIndex('IDX_ARMAX_SUBSCRIBER_SUBSCRIBER_ID', 'subscriber_id', array('unique' => true));
$table->addIndex('IDX_ARMAX_SUBSCRIBER_UPDATED_AT', 'updated_at', array());

$installer->getConnection()->createTable($table);

//Need to force auto increment on this column (see http://stackoverflow.com/questions/5341693/add-an-auto-increment-column-in-magento-setup-script-without-using-sql)
$installer->run("
  ALTER TABLE `{$installer->getTable('autorespondermax/subscriber')}` 
  CHANGE COLUMN `autorespondermax_subscriber_id` `autorespondermax_subscriber_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ;
");

$installer->endSetup();