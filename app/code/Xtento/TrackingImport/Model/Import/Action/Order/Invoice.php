<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-10-11T11:44:25+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Action/Order/Invoice.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Action\Order;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice as OrderInvoice;
use Xtento\TrackingImport\Model\Import\Action\AbstractAction;
use Xtento\TrackingImport\Model\Processor\Mapping\Action\Configuration;

class Invoice extends AbstractAction
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var TransactionFactory
     */
    protected $dbTransactionFactory;

    /**
     * Invoice constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Configuration $actionConfiguration
     * @param ProductFactory $modelProductFactory
     * @param Order\Email\Sender\InvoiceSender $invoiceSender
     * @param TransactionFactory $dbTransactionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configuration $actionConfiguration,
        ProductFactory $modelProductFactory,
        Order\Email\Sender\InvoiceSender $invoiceSender,
        TransactionFactory $dbTransactionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productFactory = $modelProductFactory;
        $this->invoiceSender = $invoiceSender;
        $this->dbTransactionFactory = $dbTransactionFactory;

        parent::__construct($context, $registry, $actionConfiguration, $resource, $resourceCollection, $data);
    }

    public function invoice()
    {
        /** @var Order $order */
        $order = $this->getOrder();
        $updateData = $this->getUpdateData();

        // Prepare items to process
        $itemsToProcess = [];
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

        // Customization: Only invoice shipped items
        /*$itemsToProcess = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getQtyShipped() > $orderItem->getQtyInvoiced() && $orderItem->getQtyToInvoice() > 0) {
                $itemsToProcess[strtolower($orderItem->getSku())]['sku'] = strtolower($orderItem->getSku());
                $itemsToProcess[strtolower($orderItem->getSku())]['qty'] = ($orderItem->getQtyShipped() - $orderItem->getQtyInvoiced());
            }
        }*/

        // Check if order is holded and unhold if should be shipped
        if ($order->canUnhold() && $this->getActionSettingByFieldBoolean('invoice_create', 'enabled')) {
            $order->unhold()->save();
            $this->addDebugMessage(
                __("Order '%1': Order was unholded so it can be invoiced.", $order->getIncrementId())
            );
        }

        // Create Invoice
        if ($this->getActionSettingByFieldBoolean('invoice_create', 'enabled')) {
            if ($order->canInvoice()) {
                $invoice = false;
                $doInvoiceOrder = true;
                // Partial invoicing support:
                if ($this->getActionSettingByFieldBoolean('invoice_partial_import', 'enabled')) {
                    // Prepare items to invoice for prepareInvoices
                    $qtys = [];
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
                            $orderItemId = $orderItem->getId();
                            $qtyToProcess = $itemsToProcess[$orderItemSku]['qty'];
                            $maxQty = $orderItem->getQtyToInvoice();
                            if ($qtyToProcess > $maxQty) {
                                if (($orderItem->getProductType() == Type::TYPE_SIMPLE || $orderItem->getProductType() == Type::TYPE_VIRTUAL)
                                    && $orderItem->getParentItem() && $orderItem->getParentItem()->getQtyToInvoice() > 0
                                ) {
                                    // Has a parent item that must be invoiced instead
                                    $orderItemId = $orderItem->getParentItem()->getId();
                                    $maxQty = $orderItem->getParentItem()->getQtyToInvoice();
                                    if ($qtyToProcess > $maxQty) {
                                        $qty = round($maxQty);
                                        $itemsToProcess[$orderItemSku]['qty'] -= $maxQty;
                                    } else {
                                        $qty = round($qtyToProcess);
                                    }
                                } else {
                                    $qty = round($maxQty);
                                    $itemsToProcess[$orderItemSku]['qty'] -= $maxQty;
                                }
                            } else {
                                $qty = round($qtyToProcess);
                            }
                            if ($qty > 0) {
                                $qtys[$orderItemId] = round($qty);
                            } else {
                                $qtys[$orderItemId] = 0;
                            }
                        } else {
                            $qtys[$orderItem->getId()] = 0;
                        }
                    }
                    if (!empty($qtys)) {
                        /** @var $invoice \Magento\Sales\Model\Order\Invoice */
                        $invoice = $order->prepareInvoice($qtys);
                        // Check if proper items have been found in $qtys
                        if (!$invoice->getTotalQty()) {
                            $doInvoiceOrder = false;
                            $this->addDebugMessage(
                                __(
                                    "Order '%1' has NOT been invoiced. Partial invoicing enabled, however the items specified in the import file couldn't be found in the order. (Could not find any qtys to invoice)",
                                    $order->getIncrementId()
                                )
                            );
                        }
                    } else {
                        // We're supposed to import partial shipments, but no SKUs were found at all. Do not touch invoice.
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has NOT been invoiced. Partial invoicing enabled, however the items specified in the import file couldn't be found in the order.",
                                $order->getIncrementId()
                            )
                        );
                        $doInvoiceOrder = false;
                    }
                } else {
                    /** @var $invoice \Magento\Sales\Model\Order\Invoice */
                    $invoice = $order->prepareInvoice();
                }

                if ($invoice && $doInvoiceOrder) {
                    if ($this->getActionSettingByFieldBoolean(
                            'invoice_capture_payment',
                            'enabled'
                        ) && $invoice->canCapture()
                    ) {
                        // Capture order online
                        $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_ONLINE);
                    } else {
                        if ($this->getActionSettingByFieldBoolean('invoice_mark_paid', 'enabled')) {
                            // Set invoice status to Paid
                            $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_OFFLINE);
                        }
                    }

                    try {
                        $invoice->register();
                    } catch (\Exception $e) {
                        throw new LocalizedException(__($e->getMessage()));
                    }
                    if ($this->getActionSettingByFieldBoolean('invoice_send_email', 'enabled')) {
                        $invoice->setCustomerNoteNotify(true);
                    }
                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = $this->dbTransactionFactory->create()
                        ->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();

                    $this->setHasUpdatedObject(true);

                    if ($this->getActionSettingByFieldBoolean('invoice_send_email', 'enabled')) {
                        $this->invoiceSender->send($invoice);
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been invoiced and the customer has been notified.",
                                $order->getIncrementId()
                            )
                        );
                    } else {
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been invoiced and the customer has NOT been notified.",
                                $order->getIncrementId()
                            )
                        );
                    }

                    unset($invoice);
                }
            } else {
                $this->addDebugMessage(
                    __(
                        "Order '%1' has NOT been invoiced. Order already invoiced or order status not allowing invoicing.",
                        $order->getIncrementId()
                    )
                );
            }
        }

        return true;
    }
}