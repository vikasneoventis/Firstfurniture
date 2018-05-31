<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-11T13:34:45+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Profile/Edit/Tab/Settings.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Profile\Edit\Tab;

class Settings extends \Xtento\TrackingImport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Xtento\TrackingImport\Model\System\Config\Source\Order\Identifier
     */
    protected $orderIdentifierSource;

    /**
     * @var \Xtento\TrackingImport\Model\System\Config\Source\Product\Identifier
     */
    protected $productIdentifierSource;

    /**
     * Filters constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Xtento\TrackingImport\Model\System\Config\Source\Order\Identifier $orderIdentifierSource
     * @param \Xtento\TrackingImport\Model\System\Config\Source\Product\Identifier $productIdentifierSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Xtento\TrackingImport\Model\System\Config\Source\Order\Identifier $orderIdentifierSource,
        \Xtento\TrackingImport\Model\System\Config\Source\Product\Identifier $productIdentifierSource,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        $this->orderIdentifierSource = $orderIdentifierSource;
        $this->productIdentifierSource = $productIdentifierSource;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'The settings specified below will be applied to all manual and automatic imports.'
            )
        ];
        return $formMessages;
    }

    /**
     * Prepare form
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('trackingimport_profile');
        if (!$model->getId()) {
            return $this;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('settings', ['legend' => __('Import Settings'), 'class' => 'fieldset-wide',]);

        $fieldset->addField(
            'order_identifier',
            'select',
            [
                'label' => __('Order Identifier'),
                'name' => 'order_identifier',
                'values' => $this->orderIdentifierSource->toOptionArray(),
                'note' => __(
                    'This is what is called the Order Identifier in the import settings and is what\'s used to identify the orders in the import file. Almost always you will want to use the Order Increment ID (Example: 100000001).'
                )
            ]
        );

        $fieldset->addField(
            'product_identifier',
            'select',
            [
                'label' => __('Product Identifier'),
                'name' => 'product_identifier',
                'values' => $this->productIdentifierSource->toOptionArray(),
                'note' => __(
                    'This is what is called the Product Identifier in the import settings and is what\'s used to identify the product in the import file. Almost always you will want to use the SKU.'
                )
            ]
        );
        $attributeCodeJs = "<script>
require([\"jquery\", \"prototype\"], function(jQuery) {
Event.observe(window, 'load', function() { function checkAttributeField(field) {if(field.value=='attribute') {\$('product_identifier_attribute_code').parentNode.parentNode.show()} else {\$('product_identifier_attribute_code').parentNode.parentNode.hide()}} checkAttributeField($('product_identifier')); $('product_identifier').observe('change', function(){ checkAttributeField(this); }); });
});
</script>";
        if ($model->getData('product_identifier') !== 'attribute') {
            // Not filled
            $attributeCodeJs .= "<script>
require([\"jquery\", \"prototype\"], function(jQuery) {
$('product_identifier_attribute_code').parentNode.parentNode.hide()
});
</script>";
        }
        $fieldset->addField(
            'product_identifier_attribute_code',
            'text',
            [
                'label' => __('Product Identifier: Attribute Code'),
                'name' => 'product_identifier_attribute_code',
                'note' => __(
                        'IMPORTANT: This is not the attribute name. It is the attribute code you assigned to the attribute.'
                    ) . $attributeCodeJs,
            ]
        );

        $renderer = $this->rendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl(
                    'xtento_trackingimport/profile/newConditionHtml/form/rule_conditions_fieldset',
                    ['profile_id' => $model->getId()]
                )
            );

        $fieldset = $form->addFieldset(
            'rule_conditions_fieldset',
            [
                'legend' => __(
                    'Process %1 only if...',
                    $this->_coreRegistry->registry('trackingimport_profile')->getEntity()
                ),
            ]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
            ]
        )->setRule($model)->setRenderer($this->conditions);

        $form->setValues($model->getConfiguration());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Settings & Filters');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Settings & Filters');
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