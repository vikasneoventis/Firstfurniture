<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:37:14+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Condition/Product/Subselect.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Condition\Product;

use Xtento\TrackingImport\Model\Import\Condition\Combine;
use Xtento\TrackingImport\Model\Import\Condition\CustomFactory;

class Subselect extends Combine
{
    /**
     * Subselect constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CustomFactory $conditionCustomFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CustomFactory $conditionCustomFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $eventManager, $conditionCustomFactory, $registry, $data);
        $this->setType('Xtento\TrackingImport\Model\Import\Condition\Product\Subselect')->setValue(null);
    }


    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' . $this->getAttribute() . '</attribute>'
            . '<operator>' . $this->getOperator() . '</operator>'
            . parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'qty_ordered' => __('total quantity ordered (for order imports)'),
                'qty_invoiced' => __('total quantity invoiced (for order imports)'),
                'qty_refunded' => __('total quantity refunded (for order imports)'),
                'qty' => __('total quantity (for invoice/shipment/credit memo imports)'),
                'base_row_total' => __('total amount'),
            ]
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If %1 %2 %3 for a subselection of items matching %4 of these conditions:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * validate
     *
     * @param \Magento\Framework\Model\AbstractModel $object Quote
     *
     * @return boolean
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        #var_dump($object->getAllItems()); die();

        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getAllItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }

        #var_dump($attr, $total); die();

        return $this->validateAttribute($total);
    }
}
