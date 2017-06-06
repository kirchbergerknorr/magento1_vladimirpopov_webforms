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
        'bcc_admin_email',
        'VARCHAR ( 255 )'
    )
;

$installer->getConnection()->addColumn(
        $this->getTable('webforms'),
        'bcc_customer_email',
        'VARCHAR ( 255 )'
    )
;

$installer->getConnection()->addColumn(
        $this->getTable('webforms'),
        'bcc_approval_email',
        'VARCHAR ( 255 )'
    )
;

$installer->endSetup();
