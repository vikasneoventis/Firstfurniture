<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2017-02-14T14:27:38+00:00
 * File:          app/code/Xtento/TrackingImport/Model/ResourceModel/Profile/Collection.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\ResourceModel\Profile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Xtento\TrackingImport\Model\Profile', 'Xtento\TrackingImport\Model\ResourceModel\Profile');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $configuration = $item->getData('configuration');
            if (!is_array($configuration)) {
                $item->setData('configuration', unserialize($configuration));
                $item->setDataChanges(false);
            }
        }
        return $this;
    }
}