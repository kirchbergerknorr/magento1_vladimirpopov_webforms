<?php
/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->dropTable($installer->getTable('webforms/dropzone'));

$table = $installer->getConnection()
    ->newTable($installer->getTable('webforms/dropzone'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('field_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true,
    ), 'Field ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false
    ), 'File Name')
    ->addColumn('size', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
        'unsigned' => true
    ))
    ->addColumn('mime_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false
    ), 'Mime Type')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false
    ), 'File Path')
    ->addColumn('hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false
    ), 'Hash')
    ->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false
        )
    );

$installer->getConnection()->createTable($table);

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms/fields'),
        'validate_unique',
        'TINYINT ( 1 )'
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms/fields'),
        'validate_unique_message',
        'TEXT'
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms/fields'),
        'browser_autocomplete',
        'TEXT'
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'frontend_download',
        'TINYINT ( 1 )'
    );

$installer->endSetup();
