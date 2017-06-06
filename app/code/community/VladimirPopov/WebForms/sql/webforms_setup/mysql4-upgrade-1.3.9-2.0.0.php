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
	$installer->getConnection()->dropTable($this->getTable('webforms/message'));
	$installer->getConnection()->dropTable($this->getTable('webforms/quickresponse'));
	$installer->getConnection()->dropTable($this->getTable('webforms/store'));
}

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'email_reply_template_id',
        'INT( 11 ) NOT NULL DEFAULT "0" AFTER `email_customer_template_id`'
    )
;

$installer->run("
$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/message')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/message')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Admin User ID',
  `message` longtext NOT NULL,
  `author` varchar(100) NOT NULL COMMENT 'Author Name',
  `is_customer_emailed` tinyint(4) NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/quickresponse')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/quickresponse')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `message` longtext NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

$REM DROP TABLE IF EXISTS `{$this->getTable('webforms/store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('webforms/store')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `entity_type` varchar(10) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `store_data` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OBJECT` (`store_id`,`entity_type`,`entity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
");

$installer->endSetup();
