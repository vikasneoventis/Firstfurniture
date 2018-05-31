<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-03T15:19:18+00:00
 * File:          app/code/Xtento/TrackingImport/Model/System/Config/Backend/Server.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\System\Config\Backend;

class Server extends \Xtento\XtCore\Model\System\Config\Backend\Server
{
    protected $version = '4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=';

    /**
     * Server constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\TrackingImport\Helper\Module $moduleHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\TrackingImport\Helper\Module $moduleHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->extId = $moduleHelper->getExtId();
        $this->module = $moduleHelper->getModule();
        $this->configPath = $moduleHelper->getConfigPath();
        parent::__construct(
            $context,
            $registry,
            $request,
            $config,
            $cacheTypeList,
            $serverHelper,
            $urlBuilder,
            $moduleList,
            $resource,
            $resourceCollection,
            $data
        );
    }
}
