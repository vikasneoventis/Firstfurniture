<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-10-20T14:10:29+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Condition/Address/Shipping.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */
namespace Xtento\TrackingImport\Model\Import\Condition\Address;

use Xtento\TrackingImport\Model\Import\Condition\ObjectCondition;

class Shipping extends ObjectCondition
{
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $address = $object;
        if (!$address instanceof \Magento\Sales\Model\Order\Address) {
            $address = $object->getShippingAddress();
            if (!$address) {
                return false;
            }
        }

        return $this->validateAttribute($address->getData($this->getAttribute()));
    }
}
