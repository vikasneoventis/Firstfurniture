<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-06T15:04:53+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Profile/Edit/Tab/Mapping.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Profile\Edit\Tab;

use Xtento\TrackingImport\Block\Adminhtml\Profile\Edit\Tab\Mapping\Mapper;

class Mapping extends \Xtento\TrackingImport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Xtento\TrackingImport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var Mapper
     */
    protected $mappingMapper;

    /**
     * Mapping constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Xtento\TrackingImport\Helper\Entity $entityHelper
     * @param Mapper $mappingMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Xtento\TrackingImport\Helper\Entity $entityHelper,
        Mapper $mappingMapper,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->entityHelper = $entityHelper;
        $this->mappingMapper = $mappingMapper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $profile = $this->_coreRegistry->registry('trackingimport_profile');
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'This is the import processor for imported %1 files. Your import format needs to be mapped to Magento fields here.',
                $this->entityHelper->getProcessorName($profile->getProcessor())
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
        $profile = $this->_coreRegistry->registry('trackingimport_profile');
        if (!$profile->getId()) {
            return $this;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'manual_fieldset',
            [
                'legend' => __('File Settings'),
                'class' => 'fieldset-wide',
            ]
        );

        $fieldset->addField(
            'mapping_note',
            'note',
            [
                'text' => __(
                    '<strong>Notice</strong>: Please make sure to visit our <a href="http://support.xtento.com/wiki/Magento_2_Extensions:Tracking_Number_Import_Module" target="_blank">support wiki</a> for an explanation on how to set up this processor.'
                )
            ]
        );

        if ($profile->getProcessor() == \Xtento\TrackingImport\Model\Import::PROCESSOR_CSV) {
            $fieldset->addField(
                'skip_header',
                'select',
                [
                    'label' => __('Skip header line'),
                    'name' => 'skip_header',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'IMPORTANT: Set this to "Yes" if you want to skip the first line of each imported CSV file as it\'s the header line containing the column names.'
                    )
                ]
            );

            $fieldset->addField(
                'delimiter',
                'text',
                [
                    'label' => __('Field Delimiter'),
                    'name' => 'delimiter',
                    'note' => __(
                        'REQUIRED: Set the field delimiter (one character only). Example field delimiter: ;<br/>Hint: If you want to use a tab delimited file enter: \t'
                    ),
                    'required' => true
                ]
            );

            $fieldset->addField(
                'enclosure',
                'text',
                [
                    'label' => __('Field Enclosure Character'),
                    'name' => 'enclosure',
                    'maxlength' => 1,
                    'note' => __('Set the field enclosure character (<b>one</b> character only, if fields are wrapped in quotes for example). Example: "')
                ]
            );
        }

        if ($profile->getProcessor() == \Xtento\TrackingImport\Model\Import::PROCESSOR_XML) {
            $fieldset->addField(
                'xpath_data',
                'text',
                [
                    'label' => __('Data XPath'),
                    'name' => 'xpath_data',
                    'note' => __(
                        'Set the XPath for the node containing the order updates.<br/><br/>Example XML file:<br/>&lt;items&gt;<br/>&lt;item&gt;<br/>...<br/>&lt;/item&gt;<br/>&lt;item&gt;<br/>...<br/>&lt;/item&gt;<br/><br/>&lt;/items&gt;<br/>The order updates would be located in each "item" node, which are located in the "items" node, so the XPath would be: //items/item<br/><br/>Every "item" node located under the "items" node would be processed then.'
                    ),
                    'required' => true
                ]
            );
        }

        $profile = $this->_coreRegistry->registry('trackingimport_profile');
        $form->setValues($profile->getConfiguration());
        $this->setForm($form);
        $this->setTemplate('Xtento_TrackingImport::profile/mapping.phtml');
        return parent::_prepareForm();
    }

    public function getMappingHtml()
    {
        $model = $this->_coreRegistry->registry('trackingimport_profile');
        $form = $this->getForm();
        $mapping = $form->addField('mapping', 'text', ['label' => '', 'name' => 'mapping']);
        $form->setValues($model->getConfiguration());
        return $this->mappingMapper->render($mapping);
    }

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('File Mapping');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('File Mapping');
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