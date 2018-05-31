<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2016-04-19T15:29:12+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Plugin/Model/Sales/OrderPlugin.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Plugin\Model\Sales;

use Magento\Sales\Model\Order;

class OrderPlugin
{
    /**
     * @var \Xtento\AdvancedOrderStatus\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Xtento\AdvancedOrderStatus\Model\ResourceModel\Notification\CollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var Order\Email\Sender\OrderCommentSender
     */
    protected $orderCommentSender;

    /**
     * OrderPlugin constructor.
     *
     * @param \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param Order\Email\Sender\OrderCommentSender $orderCommentSender
     * @param \Xtento\AdvancedOrderStatus\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory
     */
    public function __construct(
        \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Xtento\AdvancedOrderStatus\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->request = $request;
        $this->registry = $registry;
        $this->orderCommentSender = $orderCommentSender;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
    }

    /**
     * Are there any notifications that should be dispatched?
     *
     * @param Order $subject
     * @param \Closure $proceed
     * @param $comment
     * @param bool $status
     * @return mixed
     */
    public function aroundAddStatusHistoryComment(Order $subject, \Closure $proceed, $comment, $status = false)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return $proceed($comment, $status);
        }

        $notificationCollection = $this->notificationCollectionFactory->create();
        $notificationCollection->addFieldToFilter('template_id', ['neq' => -1])
            ->addFieldToFilter('store_id', $subject->getStore()->getId())
            ->addFieldToFilter('status_code', $status ? $status : $subject->getStatus());

        if ($notificationCollection->count() > 0) {
            $this->registry->register('advancedorderstatus_notifications', $notificationCollection, true);

            $isNotified = true;
            $postData = $this->request->getPost('history');
            if (!empty($postData)) {
                $isNotified = isset($postData['is_customer_notified']) ? $postData['is_customer_notified'] : false;
            }
            $this->registry->register('advancedorderstatus_notified', $isNotified, true);

            if ($this->request->getActionName() !== 'addComment') {
                $this->orderCommentSender->send($subject);
            }

            $this->registry->unregister('advancedorderstatus_notifications');
        }

        return $proceed($comment, $status);
    }
}
