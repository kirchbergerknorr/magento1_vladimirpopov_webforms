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
		$this->getTable('webforms'),
		'approve',
		'TINYINT( 1 ) NOT NULL AFTER `survey`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'code',
		'VARCHAR( 255 ) NOT NULL AFTER `name`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'redirect_url',
		'TEXT NOT NULL AFTER `code`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'result_label',
		'VARCHAR( 255 ) NOT NULL AFTER  `name`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'code',
		'VARCHAR( 255 ) NOT NULL AFTER  `result_label`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/results'),
		'approved',
		'TINYINT( 1 ) NOT NULL'
	)
;

$installer->endSetup();
