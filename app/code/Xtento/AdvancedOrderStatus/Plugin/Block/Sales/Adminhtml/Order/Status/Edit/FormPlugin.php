<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2016-04-06T13:14:50+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Plugin/Block/Sales/Adminhtml/Order/Status/Edit/FormPlugin.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Plugin\Block\Sales\Adminhtml\Order\Status\Edit;

use Magento\Sales\Block\Adminhtml\Order\Status\Edit\Form;

class FormPlugin
{
    /**
     * @var \Xtento\AdvancedOrderStatus\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $templatesFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Xtento\AdvancedOrderStatus\Model\Notification
     */
    protected $notificationModel;

    /**
     * @param \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Xtento\AdvancedOrderStatus\Model\Notification $notificationModel
     */
    public function __construct(
        \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Xtento\AdvancedOrderStatus\Model\Notification $notificationModel
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->registry = $registry;
        $this->templatesFactory = $templatesFactory;
        $this->storeManager = $storeManager;
        $this->notificationModel = $notificationModel;
    }

    /**
     * Prepare form fields and structure.. for notifications.
     *
     * @param Form $subject
     * @param string $interceptedOutput
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetForm(Form $subject, $interceptedOutput)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return $interceptedOutput;
        }

        $model = $this->registry->registry('current_status');
        if (!is_object($model)) {
            $notifications = [];
        } else {
            // Fetch notifications
            $notifications = $this->notificationModel->getNotifications($model->getStatus());
        }
        // Get form
        $form = $subject->getForm();

        $fieldset = $form->addFieldset(
            'store_notifications_fieldset',
            ['legend' => __('Order Status Notifications'), 'class' => 'store-scope']
        );
        $renderer = $subject->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset');
        $fieldset->setRenderer($renderer);

        // Fetch email templates, add "default" option
        $templateColection = $this->templatesFactory->create();
        $emailTemplates = $templateColection->load()->toOptionArray();
        array_unshift(
            $emailTemplates,
            [
                'value' => '0',
                'label' => __('Order Status Change - Default Template'),
            ]
        );
        array_unshift(
            $emailTemplates,
            [
                'value' => '-1',
                'label' => __('--- No template selected - no notification ---'),
            ]
        );

        // Output stores
        foreach ($this->storeManager->getWebsites() as $website) {
            $fieldset->addField(
                "w_{$website->getId()}_notification_label",
                'note',
                ['label' => $website->getName(), 'fieldset_html_class' => 'website']
            );
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField(
                    "sg_{$group->getId()}_notification_label",
                    'note',
                    ['label' => $group->getName(), 'fieldset_html_class' => 'store-group']
                );
                foreach ($stores as $store) {
                    $fieldset->addField(
                        "store_notification_{$store->getId()}",
                        'select',
                        [
                            'name' => 'store_notifications[' . $store->getId() . ']',
                            'required' => false,
                            'label' => $store->getName(),
                            'value' => isset($notifications[$store->getId()]) ? $notifications[$store->getId()] : '',
                            'values' => $emailTemplates,
                            'fieldset_html_class' => 'store'
                        ]
                    );
                }
            }
        }

        if ($model) {
            $form->addValues($model->getData());
        }

        return $interceptedOutput;
    }
}
