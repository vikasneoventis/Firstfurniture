<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2016-04-06T13:15:09+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Plugin/Model/Sales/Order/Status/HistoryPlugin.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Plugin\Model\Sales\Order\Status;

use Magento\Sales\Model\Order\Status\History;

class HistoryPlugin
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Adjust flag based on notification status
     *
     * @param History $subject
     * @param \Closure $proceed
     * @param null $flag
     * @return bool
     */
    public function aroundSetIsCustomerNotified(History $subject, \Closure $proceed, $flag = null)
    {
        if ($this->registry->registry('advancedorderstatus_notifications') !== null
            && $this->registry->registry('advancedorderstatus_notified')
        ) {
            $flag = true;
        }
        return $proceed($flag);
    }
}
