<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-27T14:16:21+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Entity/Order.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Entity;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Xtento\TrackingImport\Model\Log;
use Xtento\TrackingImport\Model\Processor\Mapping\ActionFactory;
use Xtento\TrackingImport\Model\ProfileFactory;

class Order extends AbstractEntity
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var ActionFactory
     */
    protected $mappingActionFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        ResourceConnection $resourceConnection,
        Registry $frameworkRegistry,
        OrderFactory $modelOrderFactory,
        InvoiceFactory $orderInvoiceFactory,
        CollectionFactory $orderShipmentCollectionFactory,
        CreditmemoFactory $orderCreditmemoFactory,
        ProfileFactory $modelProfileFactory,
        ManagerInterface $eventManagerInterface,
        \Magento\Store\Model\App\Emulation $appEmulation,
        ActionFactory $mappingActionFactory,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->orderFactory = $modelOrderFactory;
        $this->invoiceFactory = $orderInvoiceFactory;
        $this->shipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->creditmemoFactory = $orderCreditmemoFactory;
        $this->profileFactory = $modelProfileFactory;
        $this->eventManager = $eventManagerInterface;
        $this->appEmulation = $appEmulation;
        $this->mappingActionFactory = $mappingActionFactory;
        $this->objectManager = $objectManager;

        parent::__construct($resourceConnection, $frameworkRegistry, $data);
    }

    /**
     * Prepare import
     *
     * @param $updatesInFilesToProcess
     *
     * @return bool
     */
    public function prepareImport($updatesInFilesToProcess)
    {
        // Prepare actions to apply
        $actions = $this->getActions();
        $actionFields = $this->getActionFields();
        foreach ($actions as &$action) {
            $actionField = $action['field'];
            if (isset($actionFields[$actionField])) {
                $action['field_data'] = $actionFields[$actionField];
            } else {
                unset($action);
            }
        }
        $this->setActions($actions);
        return true;
    }

    protected function loadOrder($rowIdentifier)
    {
        $order = false;

        // Identify order and return $order
        $orderIdentifier = $this->getConfig('order_identifier');
        if ($orderIdentifier === 'order_increment_id') {
            $order = $this->orderFactory->create()->loadByIncrementId($rowIdentifier);
        }
        if ($orderIdentifier === 'order_entity_id') {
            $order = $this->orderFactory->create()->load($rowIdentifier);
        }
        if ($orderIdentifier === 'invoice_increment_id') {
            $invoice = $this->invoiceFactory->create()->loadByIncrementId($rowIdentifier);
            if ($invoice->getId()) {
                $order = $invoice->getOrder();
            }
        }
        if ($orderIdentifier === 'shipment_increment_id') {
            $shipment = $this->shipmentCollectionFactory->create()->addAttributeToFilter(
                'increment_id',
                $rowIdentifier
            )->getFirstItem();
            if ($shipment->getId()) {
                $order = $shipment->getOrder();
            }
        }
        if ($orderIdentifier === 'creditmemo_increment_id') {
            $creditmemo = $this->creditmemoFactory->create()
                ->getCollection()
                ->addAttributeToFilter('increment_id', $rowIdentifier)
                ->getFirstItem();
            if ($creditmemo->getId()) {
                $order = $creditmemo->getOrder();
            }
        }

        return $order;
    }

    /**
     * Process order
     *
     * @param $rowIdentifier
     * @param $updateData
     *
     * @return array
     */
    public function process($rowIdentifier, $updateData)
    {
        // Result (and debug information) returned to observer
        $importChanged = false;
        $importDebugMessages = [];

        // Load order
        $order = $this->loadOrder($rowIdentifier);
        if (!$order || !$order->getId()) {
            $importResult = [
                'changed' => false,
                'debug' => __("Order '%1' could not be found in Magento. Skipping order.", $rowIdentifier)
            ];
            return $importResult;
        }

        // Get validation profile to see if order should be processed
        $validationProfile = $this->getProfile();
        $importConditions = $validationProfile->getData('conditions_serialized');
        if (strlen($importConditions) > 90) {
            // Force load profile for rule validation, as it fails on some stores if the profile is not re-loaded
            $validationProfile = $this->profileFactory->create()->load($this->getProfile()->getId());
        }
        // Check if order should be imported, matched by the "Settings & Filters" "Process order only if..." settings
        $collectionItemValidated = true;

        // Custom validation event
        $this->eventManager->dispatch(
            'xtento_trackingimport_custom_validation',
            [
                'validationProfile' => $validationProfile,
                'collectionItem' => $order,
                'collectionItemValidated' => &$collectionItemValidated,
            ]
        );

        // If not validated, skip object
        if (!($collectionItemValidated && $validationProfile->validate($order))) {
            $importDebugMessages[] = __(
                "Order '%1' did not match import profile filters and will be skipped.",
                $rowIdentifier
            );
            $importChanged = false;
            unset($order);
            return $this->returnDebugResult($importChanged, $importDebugMessages);
        }

        // Test mode - stop import
        if ($this->getTestMode()) {
            $importDebugMessages[] = __(
                "Order '%1' (Row Identifier: %2) was found in Magento and would have been imported. (Test Mode)",
                $order->getIncrementId(),
                $rowIdentifier
            );
            return $this->returnDebugResult(true, $importDebugMessages);
        } else {
            $importDebugMessages[] = __("Order '%1' was found in Magento and will be updated now.", $rowIdentifier);
        }

        // Register update data for third party processing
        $this->registry->unregister('xtento_trackingimport_updatedata');
        $this->registry->register('xtento_trackingimport_updatedata', $updateData);

        $this->eventManager->dispatch(
            'xtento_trackingimport_process_order_before',
            [
                'import_profile' => $validationProfile,
                'update_data' => &$updateData,
                'order' => $order
            ]
        );

        // Set store and locale, so email templates and locales are sent correctly
        $this->appEmulation->startEnvironmentEmulation($order->getStoreId());

        // Apply actions
        #var_dump($this->getActions()); die();
        foreach ($this->mappingActionFactory->create()->getImportActions() as $entity => $actions) {
            foreach ($actions as $actionId => $actionData) {
                if (isset($actionData['class']) && isset($actionData['method'])) {
                    $actionModel = $this->objectManager->create($actionData['class']);
                    if ($actionModel) {
                        try {
                            $actionModel->setData('update_data', $updateData);
                            $actionModel->setData('order', $order);
                            $actionModel->setData('actions', $this->getActions());
                            $actionModel->{$actionData['method']}();
                            $importDebugMessages = array_merge($importDebugMessages, $actionModel->getDebugMessages());
                            if ($actionModel->getHasUpdatedObject()) {
                                $importChanged = true;
                            }
                        } catch (\Exception $e) {
                            // Don't break execution, but log the order related error.
                            $errorMessage = __(
                                "Exception catched for order '%1' while executing action '%2::%3':\n%4",
                                $order->getIncrementId(),
                                $actionData['class'],
                                $actionData['method'],
                                $e->getMessage(). ' (Line: '.$e->getLine().')'
                            );
                            $importDebugMessages[] = $errorMessage;
                            $this->registry->registry('trackingimport_log')->setResult(Log::RESULT_WARNING);
                            $this->registry->registry('trackingimport_log')->addResultMessage($errorMessage);
                            // Re-load order to "kill" changes made in order object by invoice/shipment creation
                            $order = $this->loadOrder($rowIdentifier);
                            #return $this->_returnDebugResult($importChanged, $importDebugMessages);
                            continue;
                        }
                    }
                }
            }
        }

        unset($order);

        // Reset locale.
        $this->appEmulation->stopEnvironmentEmulation();

        return $this->returnDebugResult($importChanged, $importDebugMessages);
    }

    protected function returnDebugResult($changed, $debugMessages)
    {
        $importResult = ['changed' => $changed, 'debug' => implode("\n", $debugMessages)];
        return $importResult;
    }

    /**
     * After the import ran
     */
    public function afterRun()
    {
        // End of routine
        #$this->getLogEntry()->addDebugMessage('Done: afterRun()');
        return $this;
    }
}