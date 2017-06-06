<?php

class VladimirPopov_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Access
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
        $form->setFieldsetElementRenderer($renderer);
        $form->setFieldNameSuffix('form');
        $form->setDataObject(Mage::registry('webforms_data'));

        $this->setForm($form);

        $fieldset = $form->addFieldset('customer_access', array(
            'legend' => Mage::helper('webforms')->__('Customer Access')
        ));

        $access_enable = $fieldset->addField('access_enable', 'select', array(
            'name' => 'access_enable',
            'label'  => Mage::helper('webforms')->__('Limit customer access'),
            'note'   => Mage::helper('webforms')->__('Limit access to the form for certain customer groups'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $access_groups = $fieldset->addField('access_groups', 'multiselect', array
        (
            'label' => Mage::helper('webforms')->__('Allowed customer groups'),
            'title' => Mage::helper('webforms')->__('Allowed customer groups'),
            'name' => 'access_groups',
            'required' => false,
            'note' => Mage::helper('webforms')->__('Allow form access for selected customer groups only'),
            'values' => $this->getGroupOptions(),
        ));

        $fieldset = $form->addFieldset('customer_dashboard', array(
            'legend' => Mage::helper('webforms')->__('Customer Dashboard')
        ));

        $dashboard_enable = $fieldset->addField('dashboard_enable', 'select', array(
            'name' => 'dashboard_enable',
            'label'  => Mage::helper('webforms')->__('Add form to customer dashboard'),
            'note'   => Mage::helper('webforms')->__('Add link to the form and submission results to customer dashboard menu'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $dashboard_groups = $fieldset->addField('dashboard_groups', 'multiselect', array
        (
            'label' => Mage::helper('webforms')->__('Customer groups'),
            'title' => Mage::helper('webforms')->__('Customer groups'),
            'name' => 'dashboard_groups',
            'required' => false,
            'note' => Mage::helper('webforms')->__('Add form to dashboard for selected customer groups only'),
            'values' => $this->getGroupOptions(),
        ));

//        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence','form_access_dependence')
//                ->addFieldMap($access_enable->getHtmlId(), $access_enable->getName())
//                ->addFieldMap($access_groups->getHtmlId(), $access_groups->getName())
//                ->addFieldMap($dashboard_enable->getHtmlId(), $dashboard_enable->getName())
//                ->addFieldMap($dashboard_groups->getHtmlId(), $dashboard_groups->getName())
//                ->addFieldDependence(
//                    $access_groups->getName(),
//                    $access_enable->getName(),
//                    1
//                )
//                ->addFieldDependence(
//                    $dashboard_groups->getName(),
//                    $dashboard_enable->getName(),
//                    1
//                )
//        );

        if(Mage::getSingleton('adminhtml/session')->getWebFormsData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
            Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
        } elseif(Mage::registry('webforms_data')){
            $form->setValues(Mage::registry('webforms_data')->getData());
        }

        return parent::_prepareForm();
    }

    public function getGroupOptions()
    {
        $options = array();
        $collection = Mage::getModel('customer/group')->getCollection();

        foreach ($collection as $group) {
            if ($group->getCustomerGroupId())
                $options[] = array('value' => $group->getCustomerGroupId(), 'label' => $group->getCustomerGroupCode());
        }

        return $options;
    }
}