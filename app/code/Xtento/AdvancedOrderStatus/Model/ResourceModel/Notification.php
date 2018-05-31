<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2016-02-11T13:52:49+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Model/ResourceModel/Notification.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Model\ResourceModel;

class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_status_notification', 'notification_id');
    }

    /**
     * @param $statusCode
     */
    public function removeNotifications($statusCode)
    {
        $adapter = $this->getConnection();
        $adapter->delete($this->getMainTable(), ['status_code = ?' => $statusCode]);
    }
}
