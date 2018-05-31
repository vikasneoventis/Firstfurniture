<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-12T11:26:05+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Processor/AbstractProcessor.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */
namespace Xtento\TrackingImport\Model\Processor;

use Magento\Framework\DataObject;
use Xtento\TrackingImport\Logger\Logger;
use Xtento\TrackingImport\Model\Processor\Mapping\Fields\Configuration;
use Xtento\TrackingImport\Model\Processor\Mapping\FieldsFactory;

abstract class AbstractProcessor extends DataObject
{
    protected $mappingModel;
    protected $mapping;

    /**
     * @var FieldsFactory
     */
    protected $mappingFieldsFactory;

    /**
     * @var Configuration
     */
    protected $fieldsConfiguration;

    /**
     * @var Logger
     */
    protected $xtentoLogger;

    /**
     * AbstractProcessor constructor.
     *
     * @param FieldsFactory $mappingFieldsFactory
     * @param Configuration $fieldsConfiguration
     * @param Logger $xtentoLogger
     * @param array $data
     */
    public function __construct(
        FieldsFactory $mappingFieldsFactory,
        Configuration $fieldsConfiguration,
        Logger $xtentoLogger,
        array $data = []
    ) {
        $this->mappingFieldsFactory = $mappingFieldsFactory;
        $this->fieldsConfiguration = $fieldsConfiguration;
        $this->xtentoLogger = $xtentoLogger;

        parent::__construct($data);
    }

    protected function getConfiguration()
    {
        return $this->getProfile()->getConfiguration();
    }

    protected function getConfigValue($key)
    {
        $configuration = $this->getConfiguration();
        if (isset($configuration[$key])) {
            return $configuration[$key];
        } else {
            return false;
        }
    }
}