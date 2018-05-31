<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-12T10:57:50+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Processor/Mapping/Action.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Processor\Mapping;


class Action extends AbstractMapping
{
    protected $importFields = null;
    protected $mappingType = 'action';
    protected $importActions = null;

    /*
     * [
     * 'label'
     * 'disabled'
     * 'tooltip'
     * 'default_value_disabled'
     * 'default_values'
     * ]
     */
    public function getMappingFields()
    {
        if ($this->importActions !== null) {
            return $this->importActions;
        }

        $importActions = [
            'invoice_settings' => [
                'label' => __('-- Invoice Actions -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'invoice_create' => [
                'label' => __('Create invoice for imported order'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'invoice_send_email' => [
                'label' => __('Send invoice email to customer'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'invoice_capture_payment' => [
                'label' => __('Capture payment for invoice (=Capture Online)'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => __(
                    'This will try to capture the payment at the payment gateway, i.e. charge the credit card if you authorized the payment.'
                ),
            ],
            'invoice_mark_paid' => [
                'label' => __('Set invoice status to "Paid" (=Capture Offline)'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'invoice_partial_import' => [
                'label' => __('Import partial invoices'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => __(
                    'This requires the SKU and quantity fields in the import file to be filled with data. The order will then only get invoiced for the imported SKU and quantity. Otherwise the order will simply be invoiced completely.'
                ),
            ],
            'shipment_settings' => [
                'label' => __('-- Shipment Actions -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'shipment_create' => [
                'label' => __('Create shipment for imported order'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'shipment_send_email' => [
                'label' => __('Send shipment email to customer'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'shipment_not_without_trackingnumbers' => [
                'label' => __('Import no shipments without tracking numbers'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => __('If set to "Yes" orders without tracking numbers will not be imported.'),
            ],
            'shipment_multiple_trackingnumbers' => [
                'label' => __('Add tracking numbers to existing shipments & import multiple tracking numbers'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => __(
                    'If the import file contains more than one tracking number for one order or if the order has been already shipped, these tracking numbers will get added to the most recent shipment of the order.'
                ),
            ],
            'shipment_partial_import' => [
                'label' => __('Import partial shipments'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => __(
                    'This requires the SKU and quantity fields in the import file to be filled with data. The order will then only get shipped for the imported SKU and quantity. Otherwise the order will simply be shipped completely.'
                ),
            ],
            'creditmemo_settings' => [
                'label' => __('-- Credit Memo Actions -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'creditmemo_create' => [
                'label' => __('Create credit memo for imported order'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'creditmemo_send_email' => [
                'label' => __('Send credit memo email to customer'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'creditmemo_back_to_stock' => [
                'label' => __('Return refunded items to stock'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => '',
            ],
            'creditmemo_partial_import' => [
                'label' => __('Import partial credit memos'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => 0,
                'tooltip' => __(
                    'This requires the SKU and quantity fields in the import file to be filled with data. The order will then only get refunded for the imported SKU and quantity. Otherwise the order will simply be refunded completely.'
                ),
            ],
            'order_status_settings' => [
                'label' => __('-- Order (Status) Actions -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'cancel_order' => [
                'label' => __('Cancel order'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => '',
                'tooltip' => __(
                    'This will cancel the order and all items in the order, if it can be cancelled. This is only possible if it has not been invoiced/shipped yet.'
                ),
            ],
            'order_status_file' => [
                'label' => __(
                    'Change order status to the status defined in the "Order Status" column in the import file'
                ),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => '',
                'tooltip' => __(
                    'This will set the order status to whatever status is defined in the Order Status column in the import file.'
                ),
            ],
            'order_status_change_partial' => [
                'label' => __('Change order status after importing partial order'),
                'default_values' => $this->getDefaultValues('order_status'),
                'default_value' => '',
                'tooltip' => __(
                    'If a partial shipped/invoiced order gets imported, change the order status to a specific status.
                Attention: Do not use the "On Hold" status for partial shipments, as otherwise no further shipments can be created.'
                ),
            ],
            'order_status_change' => [
                'label' => __('Change order status after import (or when order has been completely invoiced/shipped)'),
                'default_values' => $this->getDefaultValues('order_status'),
                'default_value' => '',
                'tooltip' => __(
                    'You can either import the status from the file you are importing (see processor options), or statically change the order status to the status set here after the order has been completely shipped.'
                ),
            ],
            'send_order_update_email' => [
                'label' => __('Send order update email to customer (see help)'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => '',
                'tooltip' => __(
                    'If enabled, the order update email will be sent to the customer. This is the same email that also gets sent if you add a comment to an order from the Orders view, so it makes sense especially if you add an order comment. Note, this only works if order update emails are enabled in System - Configuration.'
                ),
            ],
            /*'custom_actions' => [
                'label' => __('-- Custom Actions -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'custom1' => [
                'label' => __('Custom Action 1'),
                'tooltip' => '',
            ],
            'custom2' => [
                'label' => __('Custom Action 2'),
                'tooltip' => '',
            ],
            'custom3' => [
                'label' => __('Custom Action 3'),
                'tooltip' => '',
            ],*/
        ];

        // Custom event to add fields
        $this->eventManager->dispatch(
            'xtento_trackingimport_mapping_get_actions',
            [
                'importActions' => &$importActions,
            ]
        );
        // feature: merge fields from custom/action.php so custom actions can be added

        $this->importActions = $importActions;

        return $this->importActions;
    }

    public function getImportActions()
    {
        // feature: merge actions from custom/action.php so custom action models can be added
        return [
            'order' => [
                'creditmemo' => [
                    'class' => '\Xtento\TrackingImport\Model\Import\Action\Order\Creditmemo',
                    'method' => 'create'
                ],
                'invoice' => [
                    'class' => '\Xtento\TrackingImport\Model\Import\Action\Order\Invoice',
                    'method' => 'invoice'
                ],
                'shipment' => [
                    'class' => '\Xtento\TrackingImport\Model\Import\Action\Order\Shipment',
                    'method' => 'ship'
                ],
                'status' => [
                    'class' => '\Xtento\TrackingImport\Model\Import\Action\Order\Status',
                    'method' => 'update'
                ],
            ]
        ];
    }

    public function formatField($fieldName, $fieldValue)
    {
        if ($fieldName == 'qty') {
            if ($fieldValue[0] == '+') {
                $fieldValue = sprintf("%+.4f", $fieldValue);
            } else {
                $fieldValue = sprintf("%.4f", $fieldValue);
            }
        }
        if ($fieldName == 'product_identifier') {
            $fieldValue = trim($fieldValue);
        }
        return $fieldValue;
    }
}
