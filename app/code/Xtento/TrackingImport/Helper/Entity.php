<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-07T12:22:56+00:00
 * File:          app/code/Xtento/TrackingImport/Helper/Entity.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Helper;

use Magento\Framework\Exception\LocalizedException;

class Entity extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Xtento\TrackingImport\Model\Import
     */
    protected $importModel;

    /**
     * Entity constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Xtento\TrackingImport\Model\Import $importModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Xtento\TrackingImport\Model\Import $importModel
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
    }

    public function getEntityName($entity)
    {
        $entities = $this->importModel->getEntities();
        if (isset($entities[$entity])) {
            return rtrim($entities[$entity], 's');
        } else {
            return __("Undefined Entity");
        }
    }

    public function getPluralEntityName($entity)
    {
        return $entity;
    }

    public function getProcessorName($processor)
    {
        $processors = $this->importModel->getProcessors();
        if (!array_key_exists($processor, $processors)) {
            throw new LocalizedException(__('Processor "%1" does not exist. Cannot load profile.', $processor));
        }
        $processorName = $processors[$processor];
        return $processorName;
    }
}
