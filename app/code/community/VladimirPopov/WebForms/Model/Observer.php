<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2017 Vladimir Popov
 */
class VladimirPopov_WebForms_Model_Observer
{

    public function addAssets($observer)
    {
        $layout = $observer->getLayout();
        $update = $observer->getLayout()->getUpdate();

        if (in_array('cms_page', $update->getHandles())) {

            $pageId = intval(Mage::app()->getRequest()->getParam('page_id', Mage::app()->getRequest()->getParam('id', false)));

            $page = Mage::getModel('cms/page')->load($pageId);

            if (stristr($page->getContent(), 'webforms/form')) {
                Mage::helper('webforms')->addAssets($layout);
            }

            if (stristr($page->getContent(), 'webforms/results')) {
                $head = $layout->getBlock('head');
                if ($head) {
                    $head->addCss('webforms/results.css');
                }
            }
        }

    }

    public function addAdminCustomerTab($observer){
        $layout = $observer->getLayout();
        $update = $layout->getUpdate();
        if (in_array('adminhtml_customer_edit', $update->getHandles())) {
            $menu = $layout->getBlock('customer_edit_tabs');
            if($menu){
                $tab = $layout->createBlock('webforms/adminhtml_customer_tab_results', 'adminhtml_customer_tab_results');
                $menu->addTab('webform_results', array(
                    'label' => Mage::helper('webforms')->__('Web-form Results'),
                    'content' => $tab->toHtml(),
                    'after' => 'tags',
                    'is_hidden'    => Mage::registry('current_customer')->getId() ? false : true
                ));
            }
        }
    }

    public function adjustConfig($observer)
    {

        if (Mage::helper('webforms')->getMageSubversion() <= 3) return false;

        if (Mage::registry('webforms_adjust_config')) return false;

        //adjust acl

        $parent = Mage::getSingleton('admin/config')
            ->getAdminhtmlConfig()
            ->getNode('acl')
            ->descend('resources')
            ->descend('admin')
            ->descend('children')
            ->descend('webforms');

        if ($parent) {

            $webformsNode = $parent->descend('children');

            if ($webformsNode) {

                $collection = Mage::getModel('webforms/webforms')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->getCollection();
                $collection->getSelect()->order('name asc');

                foreach ($collection as $webform) {
                    $xml = '
						<webform_' . $webform->getId() . ' translate="title">
							<title>' . htmlspecialchars($webform->getName()) . '</title>
						</webform_' . $webform->getId() . '>
					';
                    $menuitem = simplexml_load_string($xml);
                    if ($menuitem) $webformsNode->appendChild($menuitem);
                }

            }
        }

        //adjust menu

        $parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');

        if ($parent) {

            $webformsNode = $parent->children()->descend('webforms')->descend('children');

            if ($webformsNode) {

                $collection = Mage::getModel('webforms/webforms')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->getCollection()
                    ->addFilter('menu', '1');
                $collection->getSelect()->order('name asc');

                $i = 1;

                foreach ($collection as $webform) {
                    $xml = '
						<webform_' . $webform->getId() . ' module="webforms">
							<title>' . htmlspecialchars($webform->getName()) . '</title>
							<sort_order>' . ($i++ * 10) . '</sort_order>
							<action>adminhtml/webforms_results/index/webform_id/' . $webform->getId() . '/</action>
						</webform_' . $webform->getId() . '>
					';
                    $menuitem = simplexml_load_string($xml);
                    if ($menuitem) $webformsNode->appendChild($menuitem);
                }

                $settings = simplexml_load_string('
						<settings module="webforms">
							<title>Settings</title>
							<sort_order>' . ($i++ * 10) . '</sort_order>
							<action>adminhtml/system_config/edit/section/webforms</action>
						</settings>
				');
                if ($settings) $webformsNode->appendChild($settings);
            }
        }

        Mage::register('webforms_adjust_config', true);
    }
}

