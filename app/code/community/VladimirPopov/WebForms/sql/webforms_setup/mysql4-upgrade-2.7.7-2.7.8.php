<?php
/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('webforms/files'))
    ->addColumn('id', 'integer', null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('result_id', 'integer', null, array(
        'nullable' => false,
        'unsigned' => true,
    ), 'Result ID')
    ->addColumn('field_id', 'integer', null, array(
        'nullable' => false,
        'unsigned' => true,
    ), 'Field ID')
    ->addColumn('name', 'text', null, array(
        'nullable' => false
    ), 'File Name')
    ->addColumn('size', 'integer', null, array(
        'nullable' => true,
        'unsigned' => true
    ))
    ->addColumn('mime_type', 'varchar', 255, array(
        'nullable' => false
    ), 'Mime Type')
    ->addColumn('path', 'text', null, array(
        'nullable' => false
    ), 'File Path')
    ->addColumn('link_hash', 'varchar', 255, array(
        'nullable' => false
    ), 'Link Hash')
    ->addColumn('created_time', 'datetime', null, array(
            'nullable' => false
        )
    );

$table->addForeignKey(
    $installer->getFkName('webforms/files', 'result_id', 'webforms/results', 'id'),
    'result_id',
    $installer->getTable('webforms/results'),
    'id');

$table->addForeignKey(
    $installer->getFkName('webforms/files', 'field', 'webforms/fields', 'id'),
    'field_id',
    $installer->getTable('webforms/fields'),
    'id');

$installer->getConnection()->createTable($table);

$installer->endSetup();
