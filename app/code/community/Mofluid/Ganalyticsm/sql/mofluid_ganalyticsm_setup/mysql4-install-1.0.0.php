<?php
$installer = $this;  //Getting Installer Class Object In A Variable
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mofluid_ganalyticsm/ganalyticsm')};
CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_ganalyticsm/ganalyticsm')} (
  `mofluid_ga_id` int(11) unsigned NOT NULL, 
  `mofluid_ga_status` int(11) NOT NULL default 0,
  `mofluid_ga_accountid` varchar(63) NOT NULL default '',
  `mofluid_ga_extras` varchar(63) NOT NULL default '',
  PRIMARY KEY (`mofluid_ga_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



 INSERT INTO {$this->getTable('mofluid_ganalyticsm/ganalyticsm')} (
  `mofluid_ga_id`,
  `mofluid_ga_status`,
  `mofluid_ga_accountid`,
  `mofluid_ga_extras`
 )
VALUES (
 23, 
 0,
 '',
 ''
);
INSERT INTO {$this->getTable('adminmofluid/mofluidresource')} (module, resource_name, resource, version, scope, sendbuildmode) VALUES ('Mofluidextra_Mofluidga','mofluidextra_mofluidga_setup','{$this->getTable('mofluid_ganalyticsm/ganalyticsm')}','1.0.0','Analytics',1);

");
$installer->endSetup();
?>
