<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Autorelated\Model\Rule\Related\Condition\Product\Special;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;

/**
 * Class Price
 *
 * @package Aheadworks\Autorelated\Model\Rule\Related\Condition\Product\Special
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Aheadworks\Autorelated\Model\Rule\Related\Condition\Product\Special
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Rule\Block\Editable $editable
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Rule\Block\Editable $editable,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $editable,
            $type,
            $productCollectionFactory,
            $storeManager,
            $metadataPool,
            $scopeConfig,
            $data
        );
        $this->setType(Price::class);
        $this->setAttribute('price');
        $this->setValue(100);
        $this->metadataPool = $metadataPool;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve operator select options array
     *
     * @return array
     */
    private function getOperatorOptionArray()
    {
        return [
            '==' => __('equal to'),
            '>' => __('more'),
            '>=' => __('equals or greater than'),
            '<' => __('less'),
            '<=' => __('equals or less than')
        ];
    }

    /**
     * Set operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption($this->getOperatorOptionArray());
        return $this;
    }

    /**
     * Retrieve rule as HTML formated string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml() . __(
            'Product Price is %1 %2% of Current Product Price',
            $this->getOperatorElementHtml(),
            $this->getValueElementHtml()
        ) . $this->getRemoveLinkHtml();
    }

    /**
     * Collect valid attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int|null $productId
     * @param array $additionalParams
     * @return $this
     * @throws \Exception
     */
    public function collectValidatedAttributes($productCollection, $productId = null, $additionalParams = [])
    {
        if (!$productId) {
            return $this;
        }
        $this->setAttribute('price');
        $this->prepareAttrValueForProductId($productId, $additionalParams);

        $method = $this->getMethod();
        $productCollection->getSelect()->group("e.entity_id");
        $productCollection->addPriceData();
        $productCollection->load();
        $productValue = $this->getProudctValue();
        if (null === $productValue) {
            $productValue = [''];
        }
        $value = array_shift($productValue);
        $condition = $this->_productResource->getConnection()->prepareSqlCondition(
            "price_index.price",
            [$method => $value / 100 * $this->getValue()]
        );
        $productCollection = $this->addWhereConditionToCollection($productCollection, $condition);

        return $this;
    }
}
