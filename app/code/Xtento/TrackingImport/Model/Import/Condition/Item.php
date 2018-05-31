<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:37:14+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Condition/Item.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Condition;

class Item extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * @var CustomFactory
     */
    protected $conditionCustomFactory;

    /**
     * Item constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param CustomFactory $conditionCustomFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        \Xtento\TrackingImport\Model\Import\Condition\CustomFactory $conditionCustomFactory,
        array $data = []
    ) {
        $this->conditionCustomFactory = $conditionCustomFactory;
        parent::__construct(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
    }

    public function loadAttributeOptions()
    {
        $attributes = [];
        $attributes = array_merge(
            $attributes,
            $this->conditionCustomFactory->create()->getCustomNotMappedAttributes('_item')
        );
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'stock_id':
                return 'numeric';
        }
        // Get type for custom
        return 'string';
    }

    public function getValueElementType()
    {
        /*switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }*/
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData('value_select_options', []);
        }
        return $this->getData('value_select_options');
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
        #var_dump($this->validateAttribute($object->getData($this->getAttribute())), $object->getData($this->getAttribute()), $this->getAttribute(), $this->getValueParsed()); die();
        return $this->validateAttribute($object->getData($this->getAttribute()));
    }
}
