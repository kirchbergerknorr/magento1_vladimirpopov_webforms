<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('webforms/results')}` CHANGE  `customer_ip`  `customer_ip` BIGINT NOT NULL;
");

$installer->endSetup();
