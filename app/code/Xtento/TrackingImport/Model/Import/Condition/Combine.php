<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-10-20T14:10:29+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Import/Condition/Combine.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Import\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Core event manager proxy
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomFactory
     */
    protected $conditionCustomFactory;

    /**
     * Combine constructor.
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
        $this->eventManager = $eventManager;
        $this->conditionCustomFactory = $conditionCustomFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->setType('Xtento\TrackingImport\Model\Import\Condition\Combine');
    }

    /**
     * Get new child select options
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditionCustom = $this->conditionCustomFactory->create();
        $attributes = [];
        $otherAttributes = [];
        $customAttributes = $conditionCustom->getCustomAttributes();
        foreach ($customAttributes as $code => $label) {
            if (preg_match('/xt\_billing\_/', $code)) {
                $attributes[] = [
                    'value' => 'Xtento\TrackingImport\Model\Import\Condition\Address\Billing|' . str_replace(
                            'xt_billing_',
                            '',
                            $code
                        ),
                    'label' => $label
                ];
            } else {
                if (preg_match('/xt\_shipping\_/', $code)) {
                    $attributes[] = [
                        'value' => 'Xtento\TrackingImport\Model\Import\Condition\Address\Shipping|' . str_replace(
                                'xt_shipping_',
                                '',
                                $code
                            ),
                        'label' => $label
                    ];
                } else {
                    $attributes[] = [
                        'value' => 'Xtento\TrackingImport\Model\Import\Condition\ObjectCondition|' . $code,
                        'label' => $label
                    ];
                }
            }
        }

        $customOtherAttributes = $conditionCustom->getCustomNotMappedAttributes();
        foreach ($customOtherAttributes as $code => $label) {
            $otherAttributes[] = [
                'value' => 'Xtento\TrackingImport\Model\Import\Condition\ObjectCondition|' . $code,
                'label' => $label
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Xtento\TrackingImport\Model\Import\Condition\Product\Found',
                    'label' => __('Product / Item attribute combination')
                ],
                [
                    'value' => 'Xtento\TrackingImport\Model\Import\Condition\Product\Subselect',
                    'label' => __('Products subselection')
                ],
                [
                    'value' => 'Xtento\TrackingImport\Model\Import\Condition\Combine',
                    'label' => __('Conditions combination')
                ],
                [
                    'label' => __(
                        '%1 Attributes',
                        ucfirst($this->registry->registry('trackingimport_profile')->getEntity())
                    ),
                    'value' => $attributes
                ],
                [
                    'label' => __(
                        'Misc. %1 Attributes',
                        ucfirst($this->registry->registry('trackingimport_profile')->getEntity())
                    ),
                    'value' => $otherAttributes
                ],
            ]
        );

        $additional = new \Magento\Framework\DataObject();
        $this->eventManager->dispatch('xtento_trackingimport_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
