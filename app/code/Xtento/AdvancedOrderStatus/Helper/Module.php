<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2016-05-30T13:09:35+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Helper/Module.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Helper;

class Module extends \Xtento\XtCore\Helper\AbstractModule
{
    protected $edition = 'CE';
    protected $module = 'Xtento_AdvancedOrderStatus';
    protected $extId = 'MTWOXtento_AdvancedOrderStatus988909';
    protected $configPath = 'advancedorderstatus/general/';

    // Module specific functionality below

    /**
     * @return array
     */
    public function getControllerNames()
    {
        return ['order', 'sales_order', 'adminhtml_sales_order', 'admin_sales_order'];
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return parent::isModuleEnabled();
    }
}
