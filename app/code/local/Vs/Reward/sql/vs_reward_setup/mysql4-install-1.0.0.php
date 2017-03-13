<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
 
/**
 * Create table 'vs_reward_program'
 */
$table = $installer->getConnection()
    // The following call to getTable('vs_reward/program') will lookup the resource for vs_reward (vs_reward_mysql4), and look
    // for a corresponding entity called program. The table name in the XML is vs_reward_program, so ths is what is created.
    ->newTable($installer->getTable('vs_reward/program'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_CLOB, 0, array(
        'nullable'  => false,
        ), 'Name');
$installer->getConnection()->createTable($table);
 
$installer->endSetup();