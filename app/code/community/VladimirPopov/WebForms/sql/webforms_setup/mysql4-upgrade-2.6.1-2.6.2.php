<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('webforms')}` CHANGE  `print_template_id`  `print_template_id` INT NOT NULL;
");

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'customer_print_template_id',
        'INT NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'customer_print_attach_to_email',
        'TINYINT( 1 ) NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'approved_print_template_id',
        'INT NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'approved_print_attach_to_email',
        'TINYINT( 1 ) NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'completed_print_template_id',
        'INT NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'completed_print_attach_to_email',
        'TINYINT( 1 ) NOT NULL'
    )
;
$installer->endSetup();
