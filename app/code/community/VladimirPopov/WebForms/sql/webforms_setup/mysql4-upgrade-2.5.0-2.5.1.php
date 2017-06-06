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
        $this->getTable('webforms/fieldsets'),
        'css_class',
        'TEXT NOT NULL AFTER `name`'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms/fields'),
        'css_class_container',
        'TEXT NOT NULL AFTER `css_class`'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'access_enable',
        'TINYINT(1) NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'access_groups_serialized',
        'TEXT NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'dashboard_enable',
        'TINYINT(1) NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'dashboard_groups_serialized',
        'TEXT NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'email_result_completed_template_id',
        'TINYINT( 1 ) NOT NULL AFTER `email_result_approved_template_id`'
    )
;

$installer->endSetup();