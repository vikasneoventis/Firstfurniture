<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Rule\Viewed;

use Aheadworks\Autorelated\Model\Rule\Viewed\Condition\CombineFactory;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as ActionCollectionFactory;
use Aheadworks\Autorelated\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Product
 *
 * @package Aheadworks\Autorelated\Model\Rule\Viewed
 */
class Product extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    private $productIds = null;

    /**
     * @var CombineFactory
     */
    private $combineFactory;

    /**
     * @var ActionCollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param ActionCollectionFactory $actionCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineFactory $combineFactory,
        ActionCollectionFactory $actionCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, null, null, $data);
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Aheadworks\Autorelated\Model\Rule\Viewed\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Reset rule combine conditions
     *
     * @param \Aheadworks\Autorelated\Model\Rule\Viewed\Condition\Combine|null $conditions
     * @return $this
     */
    protected function _resetConditions($conditions = null)
    {
        parent::_resetConditions($conditions);
        $this->getConditions($conditions)
            ->setId('viewed_conditions')
            ->setPrefix('viewed');
        return $this;
    }

    /**
     * Get validated product ids
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $this->setCollectedAttributes([]);

            /**
             * @var ProductCollection $productCollection
             */
            $productCollection = $this->productCollectionFactory->create();
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $productIds = $productCollection->getAllIds();
            $this->productIds = array_unique($productIds);
        }
        return $this->productIds;
    }
}
