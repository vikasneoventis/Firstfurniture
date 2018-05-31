<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-07-01T20:11:14+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Action/Order/Shipment.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Action\Order;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Shipping\Model\ConfigFactory;
use Magento\Store\Model\ScopeInterface;
use Xtento\TrackingImport\Model\Import\Action\AbstractAction;
use Xtento\TrackingImport\Model\Processor\Mapping\Action\Configuration;

class Shipment extends AbstractAction
{
    protected $allCarriers = null;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * @var TransactionFactory
     */
    protected $dbTransactionFactory;

    /**
     * @var CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigFactory
     */
    protected $shippingConfigFactory;

    /**
     * @var Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * Shipment constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Configuration $actionConfiguration
     * @param ProductFactory $modelProductFactory
     * @param TrackFactory $shipmentTrackFactory
     * @param TransactionFactory $resourceModelTransactionFactory
     * @param CollectionFactory $shipmentCollection
     * @param ShipmentLoader $shipmentLoader
     * @param ShipmentFactory $shipmentFactory
     * @param Order\Email\Sender\ShipmentSender $shipmentSender
     * @param ShipmentRepositoryInterface $shipmentRepositoryInterface
     * @param ScopeConfigInterface $configScopeConfigInterface
     * @param ConfigFactory $modelConfigFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configuration $actionConfiguration,
        ProductFactory $modelProductFactory,
        TrackFactory $shipmentTrackFactory,
        TransactionFactory $resourceModelTransactionFactory,
        CollectionFactory $shipmentCollection,
        ShipmentLoader $shipmentLoader,
        ShipmentFactory $shipmentFactory,
        Order\Email\Sender\ShipmentSender $shipmentSender,
        ShipmentRepositoryInterface $shipmentRepositoryInterface,
        ScopeConfigInterface $configScopeConfigInterface,
        ConfigFactory $modelConfigFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productFactory = $modelProductFactory;
        $this->trackFactory = $shipmentTrackFactory;
        $this->dbTransactionFactory = $resourceModelTransactionFactory;
        $this->shipmentCollectionFactory = $shipmentCollection;
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentRepository = $shipmentRepositoryInterface;
        $this->scopeConfig = $configScopeConfigInterface;
        $this->shippingConfigFactory = $modelConfigFactory;

        parent::__construct($context, $registry, $actionConfiguration, $resource, $resourceCollection, $data);
    }

    public function ship()
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

        // Customization: Only ship invoiced items
        /*$itemsToProcess = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getQtyInvoiced() > $orderItem->getQtyShipped() && $orderItem->getQtyToShip() > 0) {
                $itemsToProcess[strtolower($orderItem->getSku())]['sku'] = strtolower($orderItem->getSku());
                $itemsToProcess[strtolower($orderItem->getSku())]['qty'] = ($orderItem->getQtyInvoiced() - $orderItem->getQtyShipped());
            }
        }*/

        // Prepare tracking numbers to import
        $tracksToImport = [];
        if (isset($updateData['tracks']) && !empty($updateData['tracks'])) {
            foreach ($updateData['tracks'] as $trackRecord) {
                if (empty($trackRecord['tracking_number'])) {
                    continue;
                }
                $tracksToImport[$trackRecord['tracking_number']] = [
                    'tracking_number' => $trackRecord['tracking_number'],
                    'carrier_code' => (isset($trackRecord['carrier_code'])) ? $trackRecord['carrier_code'] : '',
                    'carrier_name' => (isset($trackRecord['carrier_name'])) ? $trackRecord['carrier_name'] : '',
                ];
            }
        }
        #var_dump($updateData, $tracksToImport); die();

        // Check if order is holded and unhold if should be shipped
        if ($order->canUnhold() && $this->getActionSettingByFieldBoolean('shipment_create', 'enabled')) {
            $order->unhold()->save();
            $this->addDebugMessage(
                __("Order '%1': Order was unholded so it can be shipped.", $order->getIncrementId())
            );
        }

        // Create Shipment
        if ($this->getActionSettingByFieldBoolean('shipment_create', 'enabled')) {
            $doShipOrder = true;
            if ($this->getActionSettingByFieldBoolean(
                    'shipment_not_without_trackingnumbers',
                    'enabled'
                ) && empty($tracksToImport)
            ) {
                $doShipOrder = false;
                $this->addDebugMessage(
                    __("Order '%1': No tracking numbers to import found, not shipping order.", $order->getIncrementId())
                );
            }
            if ($doShipOrder && $order->canShip()) {
                // Partial shipment support:
                $shipment = false;
                if ($this->getActionSettingByFieldBoolean('shipment_partial_import', 'enabled')) {
                    // Prepare items to ship for prepareShipment.. but only if there is SKU info in the import file.
                    $data = [];
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
                            $maxQty = $orderItem->getQtyToShip();
                            if ($qtyToProcess > $maxQty) {
                                if ($orderItem->getProductType() == Type::TYPE_SIMPLE && $orderItem->getParentItem(
                                    ) && $orderItem->getParentItem()->getQtyToShip() > 0
                                ) {
                                    // Has a parent item that must be shipped instead
                                    $orderItemId = $orderItem->getParentItem()->getId();
                                    $maxQty = $orderItem->getParentItem()->getQtyToShip();
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
                                $data['items'][$orderItemId] = round($qty);
                            }
                        }
                    }
                    #var_dump($qtys);
                    #die();
                    if (!empty($data)) {
                        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                        $shipment = $this->shipmentFactory->create($order, isset($data['items']) ? $data['items'] : []);
                        // Check if proper items have been found in $qtys
                        if (!$shipment->getTotalQty()) {
                            #$shipment = $order->prepareShipment();
                            $doShipOrder = false;
                            $this->addDebugMessage(
                                __(
                                    "Order '%1' has NOT been shipped. Partial shipping enabled, however the items specified in the import file couldn't be found in the order. (Could not find any qtys to ship)",
                                    $order->getIncrementId()
                                )
                            );
                        }
                    } else {
                        // We're supposed to import partial shipments, but no SKUs were found at all. Do not touch shipment.
                        $doShipOrder = false;
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has NOT been shipped. Partial shipping enabled, however the items specified in the import file couldn't be found in the order.",
                                $order->getIncrementId()
                            )
                        );
                    }
                } else {
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    $shipment = $this->shipmentFactory->create($order);
                }

                /* @var $shipment Order\Shipment */
                if ($shipment && $doShipOrder) {
                    $shipment->register();
                    if ($this->getActionSettingByFieldBoolean('shipment_send_email', 'enabled')) {
                        $shipment->setCustomerNoteNotify(true);
                    }
                    #if (isset($updateData['custom1']) && !empty($updateData['custom1'])) $shipment->addComment($updateData['custom1'], true);
                    $shipment->getOrder()->setIsInProcess(true);

                    $trackCount = 0;
                    foreach ($tracksToImport as $trackingNumber => $trackData) {
                        $trackCount++;
                        if (!$this->getActionSettingByFieldBoolean(
                                'shipment_multiple_trackingnumbers',
                                'enabled'
                            ) && $trackCount > 1
                        ) {
                            // Do not import more than one tracking number.
                            continue;
                        }
                        $carrierCode = $trackData['carrier_code'];
                        $carrierName = $trackData['carrier_name'];
                        if (empty($carrierCode) && !empty($carrierName)) {
                            $carrierCode = $carrierName;
                        }
                        if (empty($carrierCode) && empty($carrierName)) {
                            $carrierCode = 'custom';
                        }
                        /*if (empty($carrierName) && !empty($carrierCode)) {
                            $carrierName = $carrierCode;
                        }*/
                        if (!empty($trackingNumber)) {
                            $trackingNumber = str_replace("'", "", $trackingNumber);
                            $track = $this->trackFactory->create()->addData(
                                [
                                    'carrier_code' => $this->determineCarrierCode($carrierCode),
                                    'title' => $this->determineCarrierName($carrierName, $carrierCode),
                                    'track_number' => $trackingNumber
                                ]
                            );
                            $shipment->addTrack($track);
                        }
                    }

                    $transactionSave = $this->dbTransactionFactory->create()
                        ->addObject($shipment)->addObject($shipment->getOrder());
                    $transactionSave->save();

                    $this->setHasUpdatedObject(true);

                    if ($this->getActionSettingByFieldBoolean('shipment_send_email', 'enabled')) {
                        $this->shipmentSender->send($shipment);
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been shipped and the customer has been notified.",
                                $order->getIncrementId()
                            )
                        );
                    } else {
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been shipped and the customer has NOT been notified.",
                                $order->getIncrementId()
                            )
                        );
                    }

                    $this->setHasUpdatedObject(true);

                    unset($shipment);
                }
            } else {
                $this->addDebugMessage(
                    __(
                        "Order '%1' has NOT been shipped. Already shipped or order status not allowing shipping.",
                        $order->getIncrementId()
                    )
                );
            }
        }

        // All items of that order have been shipped but there are more tracking numbers? Try to load the last shipment and still add the tracking number.
        if (!$order->canShip() && !empty($tracksToImport)) {
            if ($this->getActionSettingByFieldBoolean('shipment_multiple_trackingnumbers', 'enabled')) {
                // Add a second/third/whatever tracking number to the shipment - if possible.
                /* @var $shipments Collection */
                $shipments = $this->shipmentCollectionFactory->create()
                    ->setOrderFilter($order)
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSort('entity_id', 'desc');
                // Customization: Add tracking# to shipment# specified as order_identifier, i.e. when loading orders via shipment# in profile.
                /*$profileConfig = $this->getProfile()->getConfiguration();
                if (isset($profileConfig['order_identifier']) && $profileConfig['order_identifier'] == 'shipment_increment_id') {
                    // Only add to this shipment ID
                    $shipments->addAttributeToFilter('increment_id', $updateData['order_identifier']);
                }*/
                // End Customization
                $shipments->setPage(1, 1);
                $lastShipment = $shipments->getFirstItem();
                if ($lastShipment->getId()) {
                    /** @var \Magento\Sales\Model\Order\Shipment $lastShipment */
                    $lastShipment = $this->shipmentRepository->get($lastShipment->getId());

                    $newTrackAdded = false;
                    foreach ($tracksToImport as $trackingNumber => $trackData) {
                        $carrierCode = $trackData['carrier_code'];
                        $carrierName = $trackData['carrier_name'];
                        if (empty($carrierCode) && !empty($carrierName)) {
                            $carrierCode = $carrierName;
                        }
                        if (empty($carrierCode) && empty($carrierName)) {
                            $carrierCode = 'custom';
                        }
                        /*if (empty($carrierName) && !empty($carrierCode)) {
                            $carrierName = $carrierCode;
                        }*/
                        $trackAlreadyAdded = false;
                        foreach ($lastShipment->getAllTracks() as $trackInfo) {
                            if ($trackInfo->getTrackNumber() == $trackingNumber) {
                                $trackAlreadyAdded = true;
                                break;
                            }
                        }
                        if (!$trackAlreadyAdded) {
                            if (!empty($trackingNumber)) {
                                // Determine carrier and add tracking number
                                $trackingNumber = str_replace("'", "", $trackingNumber);
                                $track = $this->trackFactory->create()->addData(
                                    [
                                        'carrier_code' => $this->determineCarrierCode($carrierCode),
                                        'title' => $this->determineCarrierName($carrierName, $carrierCode),
                                        'track_number' => $trackingNumber
                                    ]
                                );
                                $lastShipment->addTrack($track)->save();
                                $newTrackAdded = true;
                            }
                        }
                    }
                    if ($newTrackAdded) {
                        if ($this->getActionSettingByFieldBoolean('shipment_send_email', 'enabled')) {
                            // Re-send shipment email when another tracking number was added.
                            $this->shipmentSender->send($lastShipment);
                            $this->addDebugMessage(
                                __(
                                    "Order '%1': Another tracking number was added for the last shipment (Multi-Tracking) and the customer has been notified.",
                                    $order->getIncrementId()
                                )
                            );
                        } else {
                            $this->addDebugMessage(
                                __(
                                    "Order '%1': Another tracking number was added for the last shipment (Multi-Tracking) and the customer has NOT been notified.",
                                    $order->getIncrementId()
                                )
                            );
                        }
                        $this->setHasUpdatedObject(true);
                    }
                }
            }
        }

        return true;
    }

    protected function determineCarrierCode($carrierCode = '')
    {
        // In case the XTENTO Custom Carrier Trackers extension is installed, make sure no disabled carriers are used here.
        $disabledCarriers = explode(
            ",",
            $this->scopeConfig->getValue(
                'customtrackers/general/disabled_carriers',
                ScopeInterface::SCOPE_STORE
            )
        );
        if (!in_array($carrierCode, $disabledCarriers)) {
            // Try to find the carrier code and see if a title is assigned to it
            $carrierTitle = $this->getCarrierTitle($carrierCode, true);
            if (!empty($carrierTitle)) {
                // Valid carrier code
                return $carrierCode;
            }
        }

        /*
         * Add your custom tracking method mapping here.
         *
         * Just copy one the if conditions and replace the values with your mapping.
         *
         * The returnedCarrierCode variable must hold a valid carrierCode. Examples are ups, usps, fedex, dhl
         *
         * If you're using the XTENTO Custom Carrier Trackers extension, you can also use your custom trackers. The number relates to the Custom Carrier Trackers configuration. Examples:
         * tracker1, tracker2, tracker3, ... tracker10
         */
        $returnedCarrierCode = 'custom';
        if (preg_match("/UPS/i", $carrierCode)) {
            $returnedCarrierCode = 'ups';
        }
        if (preg_match("/FedEx/i", $carrierCode) || preg_match("/Federal Express/i", $carrierCode)) {
            $returnedCarrierCode = 'fedex';
        }
        if (preg_match("/USPS/i", $carrierCode) || preg_match("/United States Postal Service/i", $carrierCode)) {
            $returnedCarrierCode = 'usps';
        }
        if (in_array($returnedCarrierCode, $disabledCarriers)) {
            $returnedCarrierCode = 'custom';
        }

        // No custom mapping was found
        if ($returnedCarrierCode == 'custom') {
            // Try to get the carrier code by the tracker description
            if ($this->allCarriers === null) {
                $this->allCarriers = $this->shippingConfigFactory->create()->getAllCarriers();
            }
            foreach ($this->allCarriers as $carrierCodeLoop => $carrierConfig) {
                if (in_array($carrierCodeLoop, $disabledCarriers)) {
                    continue;
                }
                $carrierLoopName = $carrierConfig->getConfigData('name');
                if (preg_match('/Custom Tracker/i', $carrierLoopName)) {
                    $carrierLoopName = ''; // Custom Carrier Trackers - this "name" shouldn't be used to match
                }
                $carrierLoopTitle = $carrierConfig->getConfigData('title');
                if ($carrierConfig->isTrackingAvailable() &&
                    (
                        strpos(strtolower($carrierLoopTitle), strtolower($carrierCode)) !== false
                        || strtolower($carrierLoopTitle) == strtolower($carrierCode)
                        || strpos(strtolower($carrierLoopName), strtolower($carrierCode)) !== false
                    )
                ) {
                    return $carrierCodeLoop;
                }
            }
        }

        return $returnedCarrierCode;
    }

    protected function determineCarrierName($carrierName, $carrierCode)
    {
        if (empty($carrierName)) {
            $carrierCode = $this->determineCarrierCode($carrierCode);
            return $this->getCarrierTitle($carrierCode);
        } else {
            return $carrierName;
        }
    }

    protected function getCarrierTitle($carrierCode, $returnEmpty = false)
    {
        $carrierTitle = $this->scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            ScopeInterface::SCOPE_STORE
        );
        if (empty($carrierTitle)) {
            $carrierTitle = $this->scopeConfig->getValue(
                'customtrackers/' . $carrierCode . '/title',
                ScopeInterface::SCOPE_STORE
            );
        }
        if (!$returnEmpty && empty($carrierTitle)) {
            return ucwords($carrierCode);
        }
        return $carrierTitle;
    }
}