<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-11T16:41:20+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Processor/Mapping/AbstractMapping.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Processor\Mapping;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses;
use Xtento\XtCore\Model\System\Config\Source\Shipping\Carriers;

abstract class AbstractMapping extends DataObject
{
    protected $mapping = null;
    protected $mappingType = '';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Carriers
     */
    protected $carriers;

    /**
     * @var AllStatuses
     */
    protected $orderStatuses;

    /**
     * AbstractMapping constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $eventManager
     * @param Carriers $carriers
     * @param AllStatuses $orderStatuses
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager,
        Carriers $carriers,
        AllStatuses $orderStatuses,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
        $this->carriers = $carriers;
        $this->orderStatuses = $orderStatuses;

        parent::__construct($data);
    }

    public function getMapping()
    {
        if ($this->mapping !== null) {
            return $this->mapping;
        }

        $mappingData = $this->getMappingData();

        $mapping = [];
        foreach ($mappingData as $id => $field) {
            if (!isset($field['field'])) {
                continue;
            }
            if (!isset($field['value'])) {
                $value = '';
            } else {
                $value = $field['value'];
            }
            if (!isset($field['default_value'])) {
                $defaultValue = false;
            } else {
                $defaultValue = $field['default_value'];
            }
            if (!isset($field['xml'])) {
                $xml = false;
            } else {
                $xml = $field['xml'];
            }
            // Get data from mapping fields
            $mappingFields = $this->getMappingFields();
            if (!isset($mappingFields[$field['field']]) || !isset($mappingFields[$field['field']]['group'])) {
                $group = false;
            } else {
                $group = $mappingFields[$field['field']]['group'];
            }

            // Get field configuration (based on XML)
            $fieldConfiguration = $this->objectManager->create(
                '\Xtento\TrackingImport\Model\Processor\Mapping\\' . ucfirst($this->mappingType) . '\Configuration'
            )->getConfiguration($field['field'], $xml);

            // Return field
            $mapping[$id] = [
                'id' => $id,
                'field' => $field['field'],
                'value' => $value,
                'default_value' => $defaultValue,
                'xml' => $xml,
                'config' => $fieldConfiguration,
                'group' => $group
            ];
        }
        $this->mapping = $mapping;

        return $this->mapping;
    }

    public function getMappedFieldsForField($field)
    {
        $mapping = $this->getMapping();
        $mappingFields = [];
        foreach ($mapping as $rowId => $fieldData) {
            if ($fieldData['field'] == $field) {
                $mappingFields[] = $fieldData;
            }
        }
        if (!empty($mappingFields)) {
            return $mappingFields;
        }
        return false;
    }

    public function getMappingFields()
    {
    }

    public function getDefaultValues($entity)
    {
        $defaultValues = [];
        if ($entity == 'shipping_carriers') {
            $carriers = $this->carriers->toOptionArray();
            foreach ($carriers as $carrier) {
                $defaultValues[$carrier['value']] = $carrier['label'];
            }
        }
        if ($entity == 'order_status') {
            $statuses = $this->orderStatuses->toOptionArray();
            array_shift($statuses);
            foreach ($statuses as $status) {
                if ($status['value'] == 'no_change') {
                    continue;
                }
                $defaultValues[$status['value']] = $status['label'];
            }
        }
        if ($entity == 'yesno') {
            $defaultValues[0] = __('No');
            $defaultValues[1] = __('Yes');
        }
        return $defaultValues;
    }

    public function getDefaultValue($fieldId)
    {
        $mapping = $this->getMapping();
        if (isset($mapping[$fieldId])) {
            return $mapping[$fieldId]['default_value'];
        }
        return '';
    }
}
