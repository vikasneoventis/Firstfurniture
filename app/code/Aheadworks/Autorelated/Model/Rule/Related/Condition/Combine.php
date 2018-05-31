<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Rule\Related\Condition;

use Magento\Rule\Model\Condition\Context;

/**
 * Class Combine
 *
 * @package Aheadworks\Autorelated\Model\Rule\Related\Condition
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Aheadworks\Autorelated\Model\Rule\Related\Condition\Product\AttributesFactory
     */
    protected $attributeFactory;

    /**
     * @var \Aheadworks\Autorelated\Model\Rule\Related\Condition\Product\SpecialFactory
     */
    protected $specialFactory;

    /**
     * @param Context $context
     * @param Product\AttributesFactory $attributesFactory
     * @param Product\SpecialFactory $specialFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Product\AttributesFactory $attributesFactory,
        Product\SpecialFactory $specialFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeFactory = $attributesFactory;
        $this->specialFactory = $specialFactory;
        $this->setType(Combine::class);
    }

    /**
     * Prepare child rules option list
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = [
            [
                'value' => $this->getType(),
                'label' => __('Conditions Combination')
            ],
            $this->attributeFactory->create()->getNewChildSelectOptions(),
            $this->specialFactory->create()->getNewChildSelectOptions(),
        ];
        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }

    /**
     * Return conditions
     *
     * @return array|mixed
     */
    public function getConditions()
    {
        if ($this->getData($this->getPrefix()) === null) {
            $this->setData($this->getPrefix(), []);
        }
        return $this->getData($this->getPrefix());
    }

    /**
     * Collect the valid attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int $productId
     * @param array $additionalParams
     * @return $this
     */
    public function collectValidatedAttributes($productCollection, $productId, $additionalParams = [])
    {
        foreach ($this->getConditions() as $condition) {
            /**
             * @var Combine $condition
             */
            $condition->setAggregator($this->getAggregator());
            $condition->setTrue((bool)$this->getValue());
            $condition->collectValidatedAttributes($productCollection, $productId, $additionalParams);
        }
        return $this;
    }
}
