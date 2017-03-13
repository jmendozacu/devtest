<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('outofstock_update')}`;
CREATE TABLE `{$this->getTable('outofstock_update')}` (
`id` int(11) NOT NULL AUTO_INCREMENT ,
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY  (`id`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
