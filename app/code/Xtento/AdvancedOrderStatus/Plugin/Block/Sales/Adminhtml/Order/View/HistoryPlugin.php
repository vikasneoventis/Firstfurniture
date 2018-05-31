<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2016-04-06T13:14:59+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Plugin/Block/Sales/Adminhtml/Order/View/HistoryPlugin.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Plugin\Block\Sales\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View\History;

class HistoryPlugin
{
    /**
     * @var \Xtento\AdvancedOrderStatus\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @param \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper
     */
    public function __construct(
        \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper
    ) {
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Rewrite getStatuses() so all statuses are shown in the "Status" dropdown when adding a comment
     *
     * @param History $subject
     * @param string $interceptedOutput
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetStatuses(History $subject, $interceptedOutput)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return $interceptedOutput;
        }

        $interceptedOutput = $subject->getOrder()->getConfig()->getStatuses();
        return $interceptedOutput;
    }
}
