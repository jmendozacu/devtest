<?php
 /*
* Copyright (c) 2013 www.magebuzz.com
*/
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('shoppinglist_group')};
CREATE TABLE {$this->getTable('shoppinglist_group')} (
  `list_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL default '0',
  `list_name` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  `send_reminder_after` smallint(6) NOT NULL,
  PRIMARY KEY (`list_id`),
	FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer/entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Shopping list group';

-- DROP TABLE IF EXISTS {$this->getTable('shoppinglist_item')};
CREATE TABLE {$this->getTable('shoppinglist_item')} (
  `item_id` int(11) unsigned NOT NULL auto_increment,
  `list_id` int(10) unsigned NOT NULL default '0', 
  `product_id` int(10) unsigned default NULL,
  `description` text,
  `store_id` int(10) unsigned NOT NULL default '0',
  `qty` decimal(12,4) NOT NULL,
	`buy_request` TEXT NOT NULL, 
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`item_id`),
  KEY `FK_ITEM_SHOPPINGLIST` (`list_id`),
  CONSTRAINT `FK_ITEM_SHOPPINGLIST` FOREIGN KEY (`list_id`) REFERENCES {$this->getTable('shoppinglist_group')} (`list_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('shoppinglist_reminder')};
CREATE TABLE {$this->getTable('shoppinglist_reminder')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL default '0',
  `reminder` smallint(6) NOT NULL default '0',
	`interval`	varchar(255) NULL,
	`lasttime_send` datetime null,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer/entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup(); 