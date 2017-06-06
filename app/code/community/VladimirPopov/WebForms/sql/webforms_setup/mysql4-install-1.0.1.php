<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$edition = 'CE';
$version = explode('.', Mage::getVersion());
if ($version[1] >= 9)
	$edition = 'EE';

$webforms_table = 'webforms';
if((float)substr(Mage::getVersion(),0,3)>1.1 || $edition == 'EE')
	$webforms_table = $this->getTable('webforms/webforms');

// Magento 1.6 compatibility
$REM = "";
if((float)substr(Mage::getVersion(),0,3)>=1.6 && method_exists($installer->getConnection(), 'dropTable')){
	$REM = "--";
	$REM = "--";
	$installer->getConnection()->dropTable($this->getTable('webforms/webforms'));
	$installer->getConnection()->dropTable($this->getTable('webforms/fields'));
	$installer->getConnection()->dropTable($this->getTable('webforms/fieldsets'));
	$installer->getConnection()->dropTable($this->getTable('webforms/results'));
	$installer->getConnection()->dropTable($this->getTable('webforms/results_values'));
}

$installer->run("
$REM DROP TABLE IF EXISTS `$webforms_table`;
CREATE TABLE IF NOT EXISTS `$webforms_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `success_text` text NOT NULL,
  `registered_only` tinyint(1) NOT NULL,
  `send_email` tinyint(1) NOT NULL,
  `duplicate_email` tinyint(1) NOT NULL,
  `email` varchar(255) NOT NULL,
  `survey` tinyint(1) NOT NULL,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/fields')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/fields')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webform_id` int(11) NOT NULL,
  `fieldset_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `size` varchar(20) NOT NULL,
  `value` text NOT NULL,
  `email_subject` tinyint(1) NOT NULL,
  `css_class` varchar(255) NOT NULL,
  `css_style` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/fieldsets')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/fieldsets')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webform_id` int(11) NOT NULL,
  `name` varchar(100)  NOT NULL,
  `position` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/results')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/results')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webform_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_ip` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/results_values')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/results_values')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value` text  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_id` (`result_id`,`field_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$installer->endSetup();
