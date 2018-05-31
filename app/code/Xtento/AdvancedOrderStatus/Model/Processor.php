<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2017-02-02T15:26:27+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Model/Processor.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Model;

/**
 * Class Processor
 * @package Xtento\AdvancedOrderStatus\Model
 */
class Processor
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Xtento\AdvancedOrderStatus\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderStatuses;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    protected $orderCommentSender;

    /**
     * Processor constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper
     * @param \Magento\Sales\Model\Order\Config $orderStatuses
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper,
        \Magento\Sales\Model\Order\Config $orderStatuses,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
    ) {
        $this->orderFactory = $orderFactory;
        $this->request = $request;
        $this->registry = $registry;
        $this->scopeConfig = $config;
        $this->messageManager = $messageManager;
        $this->moduleHelper = $moduleHelper;
        $this->orderStatuses = $orderStatuses;
        $this->orderCommentSender = $orderCommentSender;
    }

    /**
     * @return bool
     */
    public function processOrders($orderIds)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return false;
        }
        @set_time_limit(0);

        $newOrderStatus = $this->request->getParam('new_order_status');
        if (!is_array($orderIds) || empty($newOrderStatus)) {
            $this->messageManager->addErrorMessage(__('Please select order(s) as well as a new order status.'));
            return false;
        }

        if (!$this->moduleHelper->confirmEnabled(true) || !$this->moduleHelper->isModuleEnabled()) {
            $this->messageManager->addErrorMessage(
                __(
                    str_rot13(
                        'Guvf bcrengvba pbhyqa\'g or pbzcyrgrq. Cyrnfr znxr fher lbh ner hfvat n inyvq yvprafr' .
                        'xrl va gur zbqhyrf pbasvthengvba ng Flfgrz > KGRAGB Rkgrafvbaf.'
                    )
                )
            );
            return false;
        }
        // Check if this module was made for the edition (CE/PE/EE) it's being run in
        if ($this->moduleHelper->isWrongEdition()) {
            $this->messageManager->addComplexErrorMessage(
                'backendHtmlMessage',
                [
                    'html' => (string)__(
                        'Attention: The installed extension version is not compatible with Magento Enterprise Edition. The compatibility of the currently installed extension version has only been confirmed with Magento Community Edition. Please go to <a href="https://www.xtento.com" target="_blank">www.xtento.com</a> to purchase or download the Enterprise Edition version of this extension.'
                    )
                ]
            );
            return false;
        }

        $modifiedCount = 0;
        foreach ($orderIds as $orderId) {
            try {
                $isModified = false;

                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderFactory->create()->load($orderId);
                if (!$order || !$order->getId()) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Could not process order with entity_id %1. Order has been deleted in the meantime?',
                            $orderId
                        )
                    );
                    continue;
                }

                $oldStatus = $order->getStatus();
                if (!empty($newOrderStatus)) {
                    $this->setOrderState($order, $newOrderStatus);
                    $order->setStatus($newOrderStatus)->save();
                    $isModified = true;
                }
                if ($oldStatus !== $order->getStatus()) {
                    $order->addStatusHistoryComment('', $order->getStatus())->setIsCustomerNotified(0);

                    // Xtento_AdvancedOrderStatus compatibility
                    if ($this->registry->registry('advancedorderstatus_notifications')) {
                        $this->orderCommentSender->send($order);
                    }
                    // End
                    $order->save();
                }

                if ($isModified) {
                    $modifiedCount++;
                }
            } catch (\Exception $e) {
                if (isset($order) && $order && $order->getIncrementId()) {
                    $orderId = $order->getIncrementId();
                }
                $this->messageManager->addErrorMessage('Exception (Order # ' . $orderId . '): ' . $e->getMessage());
            }
        }

        $this->messageManager->addSuccessMessage(__('Total of %1 order(s) were modified.', $modifiedCount));

        return true;
    }

    /**
     * @param $order
     * @param $newOrderStatus
     * @return bool
     */
    protected function setOrderState($order, $newOrderStatus)
    {
        /*if ($this->scopeConfig->isSetFlag('advancedorderstatus/general/force_status_change')) {
            return;
        }*/
        $orderStates = $this->orderStatuses->getStates();
        foreach ($orderStates as $state => $label) {
            $stateStatuses = $this->orderStatuses->getStateStatuses($state, false);
            foreach ($stateStatuses as $status) {
                if ($status == $newOrderStatus) {
                    $order->setData('state', $state);
                    return true;
                }
            }
        }
        return false;
    }
}
