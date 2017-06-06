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
		'email_template_id',
		'int( 11 ) NOT NULL AFTER `email`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'email_customer_template_id',
		'int( 11 ) NOT NULL AFTER `email_template_id`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms/fields'),
		'comment',
		'TEXT NOT NULL AFTER `name`'
	)
;

$installer->endSetup();