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
		'validate_length_min',
		'int( 11 ) NOT NULL DEFAULT "0" AFTER `css_style`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'validate_length_max',
		'int( 11 ) NOT NULL DEFAULT "0" AFTER `css_style`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'validate_regex',
		'varchar( 255 ) NOT NULL AFTER `css_style`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'validate_message',
		'text NOT NULL AFTER `css_style`'
	)
;

$installer->endSetup();
