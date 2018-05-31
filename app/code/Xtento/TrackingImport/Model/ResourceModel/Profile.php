<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:15:44+00:00
 * File:          app/code/Xtento/TrackingImport/Model/ResourceModel/Profile.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\ResourceModel;

class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_serializableFields = [
        'configuration' => [null, []]
    ];

    protected function _construct()
    {
        $this->_init('xtento_trackingimport_profile', 'profile_id');
    }
}
