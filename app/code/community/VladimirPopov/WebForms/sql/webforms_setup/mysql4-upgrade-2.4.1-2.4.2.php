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
		'email_smtpvalidation',
		'TINYINT( 1 ) NOT NULL AFTER `email_reply_template_id`'
	)
;

$installer->endSetup();
