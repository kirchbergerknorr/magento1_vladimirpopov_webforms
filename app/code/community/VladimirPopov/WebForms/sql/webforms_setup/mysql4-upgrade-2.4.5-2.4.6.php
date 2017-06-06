<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2017 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$webforms_table = 'webforms';

$edition = 'CE';
$version = explode('.', Mage::getVersion());
if ($version[1] >= 9)
	$edition = 'EE';

if((float)substr(Mage::getversion(),0,3)>1.1 || $edition == 'EE')
	$webforms_table = $this->getTable('webforms/webforms');

/**
 * Add foreign keys
 */

if((float)substr(Mage::getversion(),0,3)>1.5) {
    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/results', 'webform_id', 'webforms/webforms', 'id'),
        $installer->getTable('webforms/results'),
        'webform_id',
        $installer->getTable('webforms/webforms'),
        'id'
    );

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/results_values', 'result_id', 'webforms/results', 'id'),
        $installer->getTable('webforms/results_values'),
        'result_id',
        $installer->getTable('webforms/results'),
        'id'
    );

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/results_values', 'field_id', 'webforms/fields', 'id'),
        $installer->getTable('webforms/results_values'),
        'field_id',
        $installer->getTable('webforms/fields'),
        'id'
    );

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/fields', 'webform_id', 'webforms/webforms', 'id'),
        $installer->getTable('webforms/fields'),
        'webform_id',
        $installer->getTable('webforms/webforms'),
        'id'
    );

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/fieldsets', 'webform_id', 'webforms/webforms', 'id'),
        $installer->getTable('webforms/fieldsets'),
        'webform_id',
        $installer->getTable('webforms/webforms'),
        'id'
    );

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/logic', 'field_id', 'webforms/fields', 'id'),
        $installer->getTable('webforms/logic'),
        'field_id',
        $installer->getTable('webforms/fields'),
        'id'
    );

    $installer->getConnection()->addForeignKey(
        $installer->getFkName('webforms/message', 'field_id', 'webforms/results', 'id'),
        $installer->getTable('webforms/message'),
        'result_id',
        $installer->getTable('webforms/results'),
        'id'
    );
} else {
    $installer->run("
    ALTER TABLE `{$this->getTable('webforms/results')}`
      ADD CONSTRAINT `FK_WEBFORMS_RESULTS_WEBFORM_ID_WEBFORMS_ID` FOREIGN KEY (`webform_id`)
      REFERENCES `{$this->getTable('webforms/webforms')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
    ALTER TABLE `{$this->getTable('webforms/results_values')}`
      ADD CONSTRAINT `FK_WEBFORMS_RESULTS_VALUES_RESULT_ID_WEBFORMS_RESULTS_ID` FOREIGN KEY (`result_id`)
      REFERENCES `{$this->getTable('webforms/results')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
    ALTER TABLE `{$this->getTable('webforms/results_values')}`
      ADD CONSTRAINT `FK_WEBFORMS_RESULTS_VALUES_FIELD_ID_WEBFORMS_FIELDS_ID` FOREIGN KEY (`field_id`)
      REFERENCES `{$this->getTable('webforms/fields')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
    ALTER TABLE `{$this->getTable('webforms/fields')}`
      ADD CONSTRAINT `FK_WEBFORMS_FIELDS_WEBFORM_ID_WEBFORMS_ID` FOREIGN KEY (`webform_id`)
      REFERENCES `{$this->getTable('webforms/webforms')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
    ALTER TABLE `{$this->getTable('webforms/fieldsets')}`
      ADD CONSTRAINT `FK_WEBFORMS_FIELDSETS_WEBFORM_ID_WEBFORMS_ID` FOREIGN KEY (`webform_id`)
      REFERENCES `{$this->getTable('webforms/webforms')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
    ALTER TABLE `{$this->getTable('webforms/logic')}`
      ADD CONSTRAINT `FK_WEBFORMS_LOGIC_FIELD_ID_WEBFORMS_FIELDS_ID` FOREIGN KEY (`field_id`)
      REFERENCES `{$this->getTable('webforms/fields')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
    ALTER TABLE `{$this->getTable('webforms/message')}`
      ADD CONSTRAINT `FK_WEBFORMS_MESSAGE_FIELD_ID_WEBFORMS_RESULTS_ID` FOREIGN KEY (`result_id`)
      REFERENCES `{$this->getTable('webforms/results')}` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
");
}

$installer->endSetup();