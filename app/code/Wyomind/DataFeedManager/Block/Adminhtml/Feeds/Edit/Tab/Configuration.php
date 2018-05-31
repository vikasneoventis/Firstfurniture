<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Edit\Tab;

class Configuration extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_systemStore = null;
    protected $_dfmHelper = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Wyomind\DataFeedManager\Helper\Data $dfmHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_systemStore = $systemStore;
        $this->_dfmHelper = $dfmHelper;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Configuration')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        // ===================== action flags ==================================
        // save and generate flag
        $fieldset->addField('generate_i', 'hidden', ['name' => 'generate_i', 'value' => '']);
        // save and continue flag
        $fieldset->addField('back_i', 'hidden', ['name' => 'back_i', 'value' => '']);

        // ===================== required hidden fields ========================

        $fieldset->addField(
            'category_filter',
            'hidden',
            [
            'class' => 'debug',
            'name' => 'category_filter',
            'label' => __('Category filter'),
            'title' => __('Category filter'),
            'required' => true
                ]
        );
        $fieldset->addField(
            'category_type',
            'hidden',
            [
            'class' => 'debug',
            'name' => 'category_type',
            'label' => __('Category type'),
            'title' => __('Category type'),
            'required' => true
                ]
        );
        $fieldset->addField(
            'categories',
            'hidden',
            [
            'class' => 'debug',
            'name' => 'categories',
            'label' => __('Categories'),
            'title' => __('Categories'),
            'required' => true
                ]
        );
        $fieldset->addField(
            'attributes',
            'hidden',
            [
            'class' => 'debug',
            'name' => 'attributes',
            'label' => __('Attributes filters'),
            'title' => __('Attributes filters'),
            'required' => true
                ]
        );


        // ===================== required visible fields =======================


        $fieldset->addField(
            'name',
            'text',
            [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true,
            "note" => "Name of the file that is created when the data feed is generated"
                ]
        );

        $fieldset->addField(
            'type',
            'select',
            [
            'name' => 'type',
            'label' => __('File Type'),
            'required' => true,
            'values' => $this->_dfmHelper->getFileFormats()
                ]
        );

        $fn = $model->getName() ? : "filename";
        $ext = $this->_dfmHelper->getExtFromType($model->getType());

        $fieldset->addField(
            'dateformat',
            'select',
            [
            'name' => 'dateformat',
            'label' => __('File name format '),
            'values' => $this->_dfmHelper->getDateFormats($fn, $ext)
                ]
        );

        $fieldset->addField(
            'encoding',
            'select',
            [
            'label' => __('Encoding type'),
            'required' => true,
            'name' => 'encoding',
            'values' => $this->_dfmHelper->getEncodings()
                ]
        );

        $fieldset->addField(
            'path',
            'text',
            [
            'name' => 'path',
            'label' => __('Path'),
            'title' => __('Path'),
            'required' => true,
            "note" => "Directory where the file is stored (related to the Magento root directory)"
                ]
        );


        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => [
                    ["label" => __("Enabled"), "value" => 1],
                    ["label" => __("Disabled"), "value" => 0]
                ]
            ]
        );

        $fieldset->addField(
            'store_id',
            'select',
            [
                'name' => 'store_id',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'required' => true,
                'values' => $this->_systemStore->getStoreValuesForForm(false, false),
                "note" => "Data from a particular store view that must be used in the data feed"
            ]
        );

        $fieldset->addField(
            'extra_header',
            'textarea',
            [
                'label' => __('Extra header'),
                'required' => false,
                'name' => 'extra_header'
            ]
        );

        $fieldset->addField(
            'include_header',
            'select',
            [
                'label' => __('Include header'),
                'required' => true,
                'name' => 'include_header',
                'values' => $this->_dfmHelper->getYesNoOptions()
            ]
        );

        $fieldset->addField(
            'enclose_data',
            'select',
            [
                'label' => __('Enclose xml tag content inside CDATA (recommended)'),
                'required' => true,
                'name' => 'enclose_data',
                'values' => $this->_dfmHelper->getYesNoOptions()
            ]
        );
        $fieldset->addField(
            'clean_data',
            'select',
            [
                'label' => __('Remove all empty xml tags (recommended)'),
                'required' => true,
                'name' => 'clean_data',
                'values' => $this->_dfmHelper->getYesNoOptions()
            ]
        );
        $fieldset->addField(
            'header',
            'textarea',
            [
                'label' => __('Header pattern'),
                'name' => 'header',
                'required' => true
            ]
        );

        $fieldset->addField(
            'product_pattern',
            'textarea',
            [
                'name' => 'product_pattern',
                'label' => __('Product Pattern'),
                'required' => true,
                "note" => "Product template that will be used to generate the final output for the data feed"
            ]
        );

        $fieldset->addField(
            'footer',
            'textarea',
            [
                'label' => __('Footer pattern'),
                'name' => 'footer'
            ]
        );

        $fieldset->addField(
            'extra_footer',
            'textarea',
            [
                'label' => __('Extra footer'),
                'name' => 'extra_footer'
            ]
        );

        $fieldset->addField(
            'field_separator',
            'select',
            [
                'label' => __('Fields delimiter'),
                'required' => true,
                'name' => 'field_separator',
                'values' => $this->_dfmHelper->getFieldSeparators()
            ]
        );

        $fieldset->addField(
            'field_protector',
            'select',
            [
                'label' => __('Fields enclosure'),
                'name' => 'field_protector',
                'values' => $this->_dfmHelper->getFieldProtectors()
            ]
        );

        $fieldset->addField(
            'field_escape',
            'select',
            [
                'label' => __('Escape character'),
                'name' => 'field_escape',
                'values' => $this->_dfmHelper->getFieldEscapes()
            ]
        );


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Configuration');
    }

    public function getTabTitle()
    {
        return __('Configuration');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
