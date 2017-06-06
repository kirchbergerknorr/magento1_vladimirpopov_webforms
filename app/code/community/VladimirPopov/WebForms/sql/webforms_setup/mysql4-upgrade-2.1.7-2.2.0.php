<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Magento 1.6 compatibility
$REM = "";
if((float)substr(Mage::getVersion(),0,3)>=1.6 && method_exists($installer->getConnection(), 'dropTable')){
    $REM = "--";
    $installer->getConnection()->dropTable($this->getTable('webforms/logic'));
}

$installer->run("
$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/logic')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/logic')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `logic_condition` varchar(20) NOT NULL DEFAULT 'equal',
  `action` varchar(20) NOT NULL DEFAULT 'show',
  `aggregation` varchar(20) NOT NULL DEFAULT 'any',
  `value_serialized` text NOT NULL,
  `target_serialized` text NOT NULL,
  `is_active` tinyint(4) NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");