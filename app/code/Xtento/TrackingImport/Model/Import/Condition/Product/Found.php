<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:37:14+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Condition/Product/Found.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Condition\Product;

class Found extends \Magento\SalesRule\Model\Rule\Condition\Product\Found
{
    /**
     * @var \Xtento\TrackingImport\Model\Import\Condition\Product
     */
    protected $conditionProduct;

    /**
     * @var \Xtento\TrackingImport\Model\Import\Condition\Custom
     */
    protected $conditionCustom;

    /**
     * Found constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param \Xtento\TrackingImport\Model\Import\Condition\Product $conditionProduct
     * @param \Xtento\TrackingImport\Model\Import\Condition\Custom $conditionCustom
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Xtento\TrackingImport\Model\Import\Condition\Product $conditionProduct,
        \Xtento\TrackingImport\Model\Import\Condition\Custom $conditionCustom,
        array $data = []
    ) {
        $this->conditionProduct = $conditionProduct;
        $this->conditionCustom = $conditionCustom;
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Xtento\TrackingImport\Model\Import\Condition\Product\Found');
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If an item is %1 with %2 of these conditions true:",
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = $this->conditionProduct;
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        foreach ($productAttributes as $code => $label) {
            $pAttributes[] = [
                'value' => 'Xtento\TrackingImport\Model\Import\Condition\Product|' . $code,
                'label' => $label
            ];
        }

        $itemAttributes = [];
        $customItemAttributes = $this->conditionCustom->getCustomNotMappedAttributes('_item');
        foreach ($customItemAttributes as $code => $label) {
            $itemAttributes[] = [
                'value' => 'Xtento\TrackingImport\Model\Import\Condition\Item|' . $code,
                'label' => $label
            ];
        }

        $conditions = [
            ['value' => '', 'label' => __('Please choose a condition to add.')],
            [
                'value' => 'Xtento\TrackingImport\Model\Import\Condition\Combine',
                'label' => __('Conditions combination')
            ],
            ['label' => __('Product Attribute'), 'value' => $pAttributes],
            ['label' => __('Item Attribute'), 'value' => $itemAttributes],
        ];
        return $conditions;
    }
}