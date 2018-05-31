<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2015-11-26T12:57:04+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Model/ResourceModel/Notification/Collection.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Model\ResourceModel\Notification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Xtento\AdvancedOrderStatus\Model\Notification',
            'Xtento\AdvancedOrderStatus\Model\ResourceModel\Notification'
        );
    }
}
