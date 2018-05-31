<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-12T11:25:24+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Processor/Mapping/Fields.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Processor\Mapping;

class Fields extends AbstractMapping
{
    protected $importFields = null;
    protected $mappingType = 'fields';

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
        if ($this->importFields !== null) {
            return $this->importFields;
        }

        $importFields = [
            'order_info' => [
                'label' => __('-- Order Fields -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'order_identifier' => [
                'label' => __('Order Identifier (Order #, ...) *'),
                'default_value_disabled' => true,
                'tooltip' => __(
                    'This is the field used to identify orders in Magento. Usually the order number, but based on the setting in the Import Settings tab, this may be the invoice number for example too.'
                ),
            ],
            'order_status' => [
                'label' => __('Order Status'),
                'default_values' => $this->getDefaultValues('order_status'),
                'tooltip' => __(
                    'This is mapped to the order status set for the order. This must be a valid order status code seen at System > Order Statuses.'
                ),
            ],
            'order_status_history_comment' => [
                'label' => __('Order History Comment'),
                'tooltip' => __('Comment that is added into the order status history after the order was updated.'),
            ],
            /*'invoice_info' => [
                'label' => __('-- Invoice Fields -- '),
                'disabled' => true,
                'tooltip' => '',
            ],*/
            'shipment_info' => [
                'label' => __('-- Shipment Fields -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'tracking_number' => [
                'label' => __('Tracking Number'),
                'tooltip' => __('The tracking number added to the imported/updated shpiment'),
                'group' => 'tracks'
            ],
            'carrier_code' => [
                'label' => 'Shipping Carrier Code',
                'default_values' => $this->getDefaultValues('shipping_carriers'),
                'tooltip' => __(
                    'The carrier code for the tracking number imported. Make sure this is a valid tracker code, out of the box it is usps, ups, dhl or fedex. If you are using our Custom Carrier Trackers extension, its tracker codes are tracker1 through tracker15.'
                ),
                'group' => 'tracks'
            ],
            'carrier_name' => [
                'label' => __('Shipping Carrier Name'),
                'tooltip' => __('Shipping Carrier Title for tracking number'),
                'group' => 'tracks'
            ],
            'creditmemo_info' => [
                'label' => __('-- Credit Memo Fields -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'creditmemo_shipping_amount' => [
                'label' => __('Credit Memo Shipping Amount'),
                'default_value_disabled' => true,
                'tooltip' => '',
            ],
            'creditmemo_adjustment_positive' => [
                'label' => __('Adjustment Refund'),
                'default_value_disabled' => true,
                'tooltip' => '',
            ],
            'creditmemo_adjustment_negative' => [
                'label' => __('Adjustment Fee'),
                'default_value_disabled' => true,
                'tooltip' => '',
            ],
            'item_info' => [
                'label' => __('-- Item Fields -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'sku' => [
                'label' => __('Product Identifier (SKU, ...)'),
                'default_value_disabled' => true,
                'tooltip' => __('Shipped/Invoiced SKU'),
                'group' => 'items'
            ],
            'qty' => [
                'label' => __('Quantity'),
                'tooltip' => __('Shipped/Invoiced SKU'),
                'group' => 'items'
            ],
            'custom_fields' => [
                'label' => __('-- Custom Fields -- '),
                'disabled' => true,
                'tooltip' => '',
            ],
            'custom1' => [
                'label' => __('Custom Field 1'),
                'tooltip' => __(
                    'Used to store custom data. This can be used to map/check data in the actions tab for example.'
                ),
            ],
            'custom2' => [
                'label' => __('Custom Field 2'),
                'tooltip' => __(
                    'Used to store custom data. This can be used to map/check data in the actions tab for example.'
                ),
            ],
            'custom3' => [
                'label' => __('Custom Field 3'),
                'tooltip' => __(
                    'Used to store custom data. This can be used to map/check data in the actions tab for example.'
                ),
            ],
            'custom4' => [
                'label' => __('Custom Field 4'),
                'tooltip' => __(
                    'Used to store custom data. This can be used to map/check data in the actions tab for example.'
                ),
            ],
            'custom5' => [
                'label' => __('Custom Field 5'),
                'tooltip' => __(
                    'Used to store custom data. This can be used to map/check data in the actions tab for example.'
                ),
            ],

            /*
            'custom_fields' => [
                'label' => '-- Custom Import Fields -- ',
                'disabled' => true
            ],
            //'custom1' => ['label' => 'Custom Data 1'],
            //'custom2' => ['label' => 'Custom Data 2'],
            */
        ];

        // Custom event to add fields
        $this->eventManager->dispatch(
            'xtento_trackingimport_mapping_get_fields',
            [
                'importFields' => &$importFields,
            ]
        );

        // Feature: merge fields from custom/fields.php so custom fields can be added

        $this->importFields = $importFields;

        return $this->importFields;
    }

    public function formatField($fieldName, $fieldValue)
    {
        if ($fieldName == 'order_identifier') {
            $fieldValue = trim($fieldValue);
        }
        return $fieldValue;
    }
}
