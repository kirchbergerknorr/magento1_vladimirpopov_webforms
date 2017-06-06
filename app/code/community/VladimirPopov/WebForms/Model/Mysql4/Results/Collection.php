<?php

/**
 * @author        Vladimir Popov
 * @copyright    Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Mysql4_Results_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $filtered_values;

    protected $_loadValues = false;

    public function setLoadValues($value)
    {
        $this->_loadValues = $value;
        return $this;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/results');
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $item) {
            if ($this->_loadValues) {
                $query = $this->getConnection()->select()
                    ->from($this->getTable('webforms/results_values'))
                    ->where($this->getTable('webforms/results_values') . '.result_id = ' . $item->getId());
                $results = $this->getConnection()->fetchAll($query);
                foreach ($results as $result) {
                    $item->setData('field_' . $result['field_id'], trim($result['value']));
                    $item->setData('key_' . $result['field_id'], $result['key']);
                }
            }
            $item->setData('ip', long2ip($item->getCustomerIp()));

        }

        Mage::dispatchEvent('webforms_results_collection_load', array('collection' => $this));

        return $this;
    }

    public function addFieldFilter($field_id, $value)
    {
        $field = Mage::getModel('webforms/fields')->load($field_id);
        $cond = "results_values_$field_id.value like '%" . trim(str_replace("'", "\\'", $value)) . "%'";
        if ($field->getType() == 'select' || $field->getType() == 'select/radio') {
            $cond = "results_values_$field_id.value like '" . trim(str_replace("'", "\\'", $value)) . "'";
        }
        if (is_array($value)) {
            if (strstr($field->getType(), 'date')) {
                if ($value['from']) $value['from'] = "'" . date($field->getDbDateFormat(), strtotime($value['orig_from'])) . "'";
                if ($value['to']) $value['to'] = "'" . date($field->getDbDateFormat(), strtotime($value['orig_to'])) . "'";
            }
            if ($value['from']) {
                $cond = "results_values_$field_id.value >= $value[from]";
            }
            if ($value['to']) {
                $cond = "results_values_$field_id.value <= $value[to]";
            }
            if ($value['from'] && $value['to']) {
                $cond = "results_values_$field_id.value >= $value[from] AND results_values_$field_id.value <= $value[to]";
            }
        }
        $this->getSelect()
            ->join(array('results_values_' . $field_id => $this->getTable('webforms/results_values')), 'main_table.id = results_values_' . $field_id . '.result_id', array('main_table.*'))
            ->group('main_table.id');

        $this->getSelect()
            ->where("results_values_$field_id.field_id = $field_id AND $cond");

    }
}
