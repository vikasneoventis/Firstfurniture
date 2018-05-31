<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-07T12:24:34+00:00
 * File:          app/code/Xtento/TrackingImport/Helper/Module.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Helper;

class Module extends \Xtento\XtCore\Helper\AbstractModule
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Module constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context, $registry, $serverHelper, $utilsHelper);
        $this->resource = $resource;
    }

    protected $edition = 'CE';
    protected $module = 'Xtento_TrackingImport';
    protected $extId = 'MTWOXtento_OrderStatusImport';
    protected $configPath = 'trackingimport/general/';

    // Module specific functionality below
    public function getDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag($this->configPath . 'debug');
    }

    public function isDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            $this->configPath . 'debug'
        ) && ($debug_email = $this->scopeConfig->getValue(
            $this->configPath . 'debug_email'
        )) && !empty($debug_email);
    }

    public function getDebugEmail()
    {
        return $this->scopeConfig->getValue($this->configPath . 'debug_email');
    }

    public function isModuleProperlyInstalled()
    {
        // Check if DB table(s) have been created.
        return ($this->resource->getConnection('core_read')->showTableStatus(
                $this->resource->getTableName('xtento_trackingimport_profile')
            ) !== false);
    }
}
