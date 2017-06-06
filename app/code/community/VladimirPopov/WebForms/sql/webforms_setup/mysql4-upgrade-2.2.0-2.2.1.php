<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('webforms/fields')}` CHANGE  `name`  `name` TEXT NOT NULL;
ALTER TABLE  `{$this->getTable('webforms/fields')}` CHANGE  `result_label`  `result_label` TEXT NOT NULL;
ALTER TABLE  `{$this->getTable('webforms/fieldsets')}` CHANGE  `name`  `name` TEXT NOT NULL;
");

$installer->endSetup();
