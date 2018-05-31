<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-11T16:25:10+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Entity/AbstractEntity.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Entity;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

abstract class AbstractEntity extends DataObject
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Resource models, read/write adapater
     */
    /** @var $readAdapter \Magento\Framework\Db\Adapter\Pdo\Mysql */
    protected $readAdapter;

    /** @var $writeAdapter \Magento\Framework\Db\Adapter\Pdo\Mysql */
    protected $writeAdapter;

    /**
     * Database table name cache
     */
    protected $tableNames = [];

    /**
     * AbstractEntity constructor.
     *
     * @param array $data
     * @param ResourceConnection $resourceConnection
     * @param Registry $frameworkRegistry
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Registry $frameworkRegistry,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->registry = $frameworkRegistry;

        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');

        parent::__construct($data);
    }

    /**
     * Get database table name for entity
     *
     * @param $entity
     *
     * @return bool
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Return configuration value
     *
     * @param $key
     *
     * @return bool
     */
    public function getConfig($key)
    {
        $configuration = $this->getProfile()->getConfiguration();
        if (isset($configuration[$key])) {
            return $configuration[$key];
        } else {
            return false;
        }
    }

    public function getConfigFlag($key)
    {
        return (bool)$this->getConfig($key);
    }

    public function getLogEntry()
    {
        return $this->registry->registry('trackingimport_log');
    }
}