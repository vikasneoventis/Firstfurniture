<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Edit\Tab;

class Categories extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_directoryList = null;
    protected $_coreHelper = null;
    protected $_objectManager = null;
    protected $_tree = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Wyomind\DataFeedManager\Helper\CategoryTree $tree,
        array $data = []
    ) {
    
        $this->_directoryList = $directoryList;
        $this->_coreHelper = $coreHelper;
        $this->_objectManager = $objectManager;
        $this->_tree = $tree;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getFeedTaxonomy()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        return $model->getTaxonomy();
    }

    public function getCategoryFilter()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        return $model->getCategoryFilter();
    }

    public function getCategoryType()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        return $model->getCategoryType();
    }

    public function getDFMCategories()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        return $model->getCategories();
    }

    public function dirFiles($directory)
    {
        $dir = dir($directory); //Open Directory
        while (false !== ($file = $dir->read())) { //Reads Directory
            $extension = substr($file, strrpos($file, '.')); // Gets the File Extension
            if ($extension == ".txt") { // Extensions Allowed
                $filesall[$file] = $file;
            } // Store in Array
        }
        $dir->close(); // Close Directory
        asort($filesall); // Sorts the Array
        return $filesall;
    }

    public function getAvailableTaxonomies()
    {
        $rootDir = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        if (file_exists($rootDir . "/app/code/Wyomind/DataFeedManager/data/Google/Taxonomies/")) {
            return $this->dirFiles($rootDir . "/app/code/Wyomind/DataFeedManager/data/Google/Taxonomies/");
        } elseif (file_exists($rootDir . "/vendor/wyomind/datafeedmanager/data/Google/Taxonomies/")) {
            return $this->dirFiles($rootDir . "/vendor/wyomind/datafeedmanager/data/Google/Taxonomies/");
        } else {
            return [];
        }
    }

    public function getJsonTree()
    {
        $treeCategories = $this->_tree->getTree();
        return json_encode($treeCategories);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $form->setValues($model->getData());
        $this->setForm($form);
        $this->setTemplate('edit/categories.phtml');
        return parent::_prepareForm();
    }

    public function getStoreId()
    {
        return $this->_coreRegistry->registry('data_feed')->getStoreId();
    }

    public function getCategories()
    {
        $tmp = $this->_categoryCollection->create();
        return $tmp
                        ->setStoreId($this->getStoreId())
                        ->addAttributeToSelect(['name'/* , 'is_active' */])
                        ->addAttributeToSort('path', 'ASC');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Categories');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Categories');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
