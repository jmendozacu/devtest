<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table foodcoupon_log(id int not null auto_increment, order_id int, store_id int, customer_id int,amount float, primary key(id));

		
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
