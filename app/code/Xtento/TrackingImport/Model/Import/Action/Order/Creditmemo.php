<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2017-05-24T13:01:27+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Action/Order/Creditmemo.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Action\Order;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Xtento\TrackingImport\Model\Import\Action\AbstractAction;
use Xtento\TrackingImport\Model\Processor\Mapping\Action\Configuration;

class Creditmemo extends AbstractAction
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * Creditmemo constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Configuration $actionConfiguration
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param ProductFactory $productFactory
     * @param ObjectManagerInterface $objectManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configuration $actionConfiguration,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->productFactory = $productFactory;
        $this->objectManager = $objectManager;

        parent::__construct($context, $registry, $actionConfiguration, $resource, $resourceCollection, $data);
    }

    public function create()
    {
        if ($this->getActionSettingByFieldBoolean('creditmemo_create', 'enabled')) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->getOrder();

            // Prepare items to process
            $itemsToProcess = [];
            $updateData = $this->getUpdateData();
            if (isset($updateData['items']) && !empty($updateData['items'])) {
                foreach ($updateData['items'] as $itemRecord) {
                    $itemRecord['sku'] = strtolower($itemRecord['sku']);
                    if (isset($itemsToProcess[$itemRecord['sku']])) {
                        $itemsToProcess[$itemRecord['sku']]['qty'] = $itemsToProcess[$itemRecord['sku']]['qty'] + $itemRecord['qty'];
                    } else {
                        $itemsToProcess[$itemRecord['sku']]['sku'] = $itemRecord['sku'];
                        $itemsToProcess[$itemRecord['sku']]['qty'] = $itemRecord['qty'];
                    }
                }
            }

            // Create Credit Memo
            if ($order->canCreditmemo()) {
                // Get invoice to refund against
                $invoice = false;
                $invoices = $order->getInvoiceCollection();
                if ($invoices->getSize() === 1) {
                    /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                    $invoice = $invoices->getFirstItem();
                }
                // Start creation
                $creditmemo = false;
                $doRefundOrder = true;
                $data = [];
                if (array_key_exists('creditmemo_shipping_amount', $updateData)
                    && $updateData['creditmemo_shipping_amount'] != ''
                ) {
                    $data['shipping_amount'] = $updateData['creditmemo_shipping_amount'];
                }
                if (array_key_exists('creditmemo_adjustment_positive', $updateData)
                    && $updateData['creditmemo_adjustment_positive'] != ''
                ) {
                    $data['adjustment_positive'] = $updateData['creditmemo_adjustment_positive'];
                }
                if (array_key_exists('creditmemo_adjustment_negative', $updateData)
                    && $updateData['creditmemo_adjustment_negative'] != ''
                ) {
                    $data['adjustment_negative'] = $updateData['creditmemo_adjustment_negative'];
                }
                //$data['do_offline'] = 1;
                if ($this->getActionSettingByFieldBoolean('creditmemo_partial_import', 'enabled')) {
                    // Prepare items to invoice for prepareInvoices.. but only if there is SKU info in the import file.
                    $items = [];
                    foreach ($order->getAllItems() as $orderItem) {
                        // How should the item be identified in the import file?
                        if ($this->getProfileConfiguration()->getProductIdentifier() == 'sku') {
                            $orderItemSku = strtolower(trim($orderItem->getSku()));
                        } else {
                            if ($this->getProfileConfiguration()->getProductIdentifier() == 'entity_id') {
                                $orderItemSku = trim($orderItem->getProductId());
                            } else {
                                if ($this->getProfileConfiguration()->getProductIdentifier() == 'attribute') {
                                    $product = $this->productFactory->create()->load($orderItem->getProductId());
                                    if ($product->getId()) {
                                        $orderItemSku = strtolower(
                                            trim(
                                                $product->getData(
                                                    $this->getProfileConfiguration()->getProductIdentifierAttributeCode(
                                                    )
                                                )
                                            )
                                        );
                                    } else {
                                        $this->addDebugMessage(
                                            __(
                                                "Order '%1': Product SKU '%2', product does not exist anymore and cannot be matched for importing.",
                                                $order->getIncrementId(),
                                                $orderItem->getSku()
                                            )
                                        );
                                        continue;
                                    }
                                } else {
                                    $this->addDebugMessage(
                                        __("Order '%1': No method found to match products.", $order->getIncrementId())
                                    );
                                    return true;
                                }
                            }
                        }
                        // Item matched?
                        if (isset($itemsToProcess[$orderItemSku])) {
                            if ($itemsToProcess[$orderItemSku]['qty'] == '' || $itemsToProcess[$orderItemSku]['qty'] < 0) {
                                $qty = $orderItem->getQtyOrdered();
                            } else {
                                $qty = round($itemsToProcess[$orderItemSku]['qty']);
                            }
                            if ($qty > 0) {
                                $items[$orderItem->getId()] = ['qty' => round($qty), 'back_to_stock' => true];
                            } else $items[$orderItem->getId()] = ['qty' => 0];
                        } else $items[$orderItem->getId()] = ['qty' => 0];
                    }
                    if (!empty($items)) {
                        $data['items'] = $items;
                        $this->creditmemoLoader->setOrderId($order->getId());
                        if ($invoice !== false) {
                            $this->creditmemoLoader->setInvoiceId($invoice->getId());
                        }
                        $this->creditmemoLoader->setCreditmemo($data);
                        $creditmemo = $this->creditmemoLoader->load();
                        $this->registry->unregister('current_creditmemo');
                    } else {
                        // We're supposed to import partial credit memos, but no SKUs were found at all. Do not touch credit memo.
                        $doRefundOrder = false;
                        $this->addDebugMessage(
                            __(
                                "Order '%1', no credit memo was created. Partial credit memo creation enabled, however the items specified in the import file couldn't be found in the order.",
                                $order->getIncrementId()
                            )
                        );
                    }
                } else {
                    $this->creditmemoLoader->setOrderId($order->getId());
                    if ($invoice !== false) {
                        $this->creditmemoLoader->setInvoiceId($invoice->getId());
                    }
                    $this->creditmemoLoader->setCreditmemo($data);
                    $creditmemo = $this->creditmemoLoader->load();
                    $this->registry->unregister('current_creditmemo');
                }

                if ($creditmemo && $doRefundOrder) {
                    if (!$creditmemo->isValidGrandTotal()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The credit memo\'s total must be positive.')
                        );
                    }

                    /** @var \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement */
                    $creditmemoManagement = $this->objectManager->create(
                        'Magento\Sales\Api\CreditmemoManagementInterface'
                    );
                    $refundOffline = true;
                    if ($invoice !== false && $invoice->getTransactionId()) {
                        $refundOffline = false;
                    }
                    $creditmemoManagement->refund($creditmemo, $refundOffline);

                    if ($this->getActionSettingByFieldBoolean('creditmemo_send_email', 'enabled')) {
                        $this->creditmemoSender->send($creditmemo);
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been refunded and the customer has been notified.",
                                $order->getIncrementId()
                            )
                        );
                    } else {
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been refunded and the customer has NOT been notified.",
                                $order->getIncrementId()
                            )
                        );
                    }

                    $this->setHasUpdatedObject(true);
                    unset($creditmemo);
                }
            } else {
                $this->addDebugMessage(
                    __(
                        "Order '%1' has NOT been refunded. Order already refunded or order status not allowing credit memo creation.",
                        $order->getIncrementId()
                    )
                );
            }

            return true;
        }
    }
}