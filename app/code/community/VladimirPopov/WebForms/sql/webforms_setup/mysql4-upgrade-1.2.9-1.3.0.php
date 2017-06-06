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
		'images_upload_limit',
		'int( 11 ) NOT NULL DEFAULT "0" AFTER `approve`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'files_upload_limit',
		'int( 11 ) NOT NULL DEFAULT "0" AFTER `approve`'
	)
;

$installer->getConnection()
	->addColumn(
		$this->getTable('webforms'),
		'captcha_mode',
		'varchar( 40 ) NOT NULL AFTER `approve`'
	)
;

$installer->endSetup();
