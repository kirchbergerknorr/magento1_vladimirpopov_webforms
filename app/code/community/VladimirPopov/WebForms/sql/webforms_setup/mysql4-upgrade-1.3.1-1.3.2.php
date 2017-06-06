<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'result_display',
		'varchar( 10 ) NOT NULL DEFAULT "on" AFTER `result_label`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fieldsets'),
		'result_display',
		'varchar( 10 ) NOT NULL DEFAULT "on" AFTER `name`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'add_header',
		'tinyint(1) NOT NULL DEFAULT "1" AFTER `send_email`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'menu',
		'tinyint(1) NOT NULL DEFAULT "1" AFTER `is_active`'
	)
;

$installer->endSetup();
