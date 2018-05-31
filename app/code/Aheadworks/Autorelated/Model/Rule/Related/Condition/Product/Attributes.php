<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Model\Rule\Related\Condition\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;

/**
 * Class Attributes
 *
 * @package Aheadworks\Autorelated\Model\Rule\Related\Condition\Product
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attributes extends \Aheadworks\Autorelated\Model\Rule\Viewed\Condition\Product\Attributes
{
    /**
     * Value type constants
     */
    const VALUE_TYPE_CONSTANT = 'constant';
    const VALUE_TYPE_SAME_AS = 'same_as';
    const VALUE_TYPE_CHILD_OF = 'child_of';

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $type;

    /**
     * @var \Magento\Rule\Block\Editable
     */
    private $editable;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Rule\Model\Condition\Context  $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Rule\Block\Editable $editable
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
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
        $this->editable = $editable;
        $this->type = $type;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $storeManager,
            $metadataPool,
            $scopeConfig,
            $data
        );
        $this->setType(Attributes::class);
        $this->setValue(null);
        $this->setValueType(self::VALUE_TYPE_SAME_AS);
        $this->metadataPool = $metadataPool;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Add special action product attributes
     *
     * @param array &$attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['type_id'] = __('Type');
    }

    /**
     * Retrieve value by option
     * Rewrite for Retrieve options by Product Type attribute
     *
     * @param mixed $option
     * @return string
     */
    public function getValueOption($option = null)
    {
        if (!$this->getData('value_option') && $this->getAttribute() == 'type_id') {
            $this->setData('value_option', $this->type->getAllOption());
        }
        return parent::getValueOption($option);
    }

    /**
     * Retrieve select option values
     * Rewrite Rewrite for Retrieve options by Product Type attribute
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options') && $this->getAttribute() == 'type_id') {
            $this->setData('value_select_options', $this->type->getAllOption());
        }
        return parent::getValueSelectOptions();
    }

    /**
     * Retrieve input type
     * Rewrite for define input type for Product Type attribute
     *
     * @return string
     */
    public function getInputType()
    {
        $attributeCode = $this->getAttribute();
        if ($attributeCode == 'type_id') {
            return 'select';
        }
        return parent::getInputType();
    }

    /**
     * Retrieve value element type
     * Rewrite for define value element type for Product Type attribute
     *
     * @return string
     */
    public function getValueElementType()
    {
        $attributeCode = $this->getAttribute();
        if ($attributeCode == 'type_id') {
            return 'select';
        }
        return parent::getValueElementType();
    }

    /**
     * Retrieve model content as HTML
     * Rewrite for add value type chooser
     *
     * @return \Magento\Framework\Phrase
     */
    public function asHtml()
    {
        return __(
            'Product %1%2%3%4%5%6%7',
            $this->getTypeElementHtml(),
            $this->getAttributeElementHtml(),
            $this->getOperatorElementHtml(),
            $this->getValueTypeElementHtml(),
            $this->getValueElementHtml(),
            $this->getRemoveLinkHtml(),
            $this->getChooserContainerHtml()
        );
    }

    /**
     * Returns options for value type select box
     *
     * @return array
     */
    public function getValueTypeOptions()
    {
        $options = [['value' => self::VALUE_TYPE_CONSTANT, 'label' => __('Exact Value')]];

        if ($this->getAttribute() == 'category_ids') {
            $options[] = [
                'value' => self::VALUE_TYPE_SAME_AS,
                'label' => __('Same as Current Product Category'),
            ];
            $options[] = [
                'value' => self::VALUE_TYPE_CHILD_OF,
                'label' => __('Subcategory of Current Product Category'),
            ];
        } else {
            $options[] = [
                'value' => self::VALUE_TYPE_SAME_AS,
                'label' => __('Current Product %1', $this->getAttributeName()),
            ];
        }

        return $options;
    }

    /**
     * Retrieve Value Type display name
     *
     * @return string
     */
    public function getValueTypeName()
    {
        $options = $this->getValueTypeOptions();
        foreach ($options as $option) {
            if ($option['value'] == $this->getValueType()) {
                return $option['label'];
            }
        }
        return '...';
    }

    /**
     * Retrieve Value Type Select Element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getValueTypeElement()
    {
        $elementId = $this->getPrefix() . '__' . $this->getId() . '__value_type';
        $element = $this->getForm()->addField(
            $elementId,
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value_type]',
                'values' => $this->getValueTypeOptions(),
                'value' => $this->getValueType(),
                'value_name' => $this->getValueTypeName(),
                'class' => 'value-type-chooser',
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->editable
        );
        return $element;
    }

    /**
     * Retrieve value type element HTML code
     *
     * @return string
     */
    public function getValueTypeElementHtml()
    {
        $element = $this->getValueTypeElement();
        return $element->getHtml();
    }

    /**
     * Add JS observer
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = parent::getValueAfterElementHtml();
        $valueFieldId = $this->getPrefix() . '__' . $this->getId() . '__value';
        $valueTypeFieldId = $this->getPrefix() . '__' . $this->getId() . '__value_type';
        $constantTypeValue = self::VALUE_TYPE_CONSTANT;
        $html .= "<script type='text/javascript'>
            require([\"jquery\", \"jquery/ui\"],
                (function($) {
                    if (typeof($('#{$valueTypeFieldId}').val()) != 'undefined'
                            && $('#{$valueTypeFieldId}').val() != '{$constantTypeValue}'
                    ) {
                        $('#{$valueFieldId}').parent().parent().hide();
                    } else {
                        $('#{$valueFieldId}').parent().parent().show();
                    }
                    $('#{$valueTypeFieldId}').change(function(){
                        if (this.value != '{$constantTypeValue}') {
                            $('#{$valueFieldId}').parent().parent().hide();
                        } else {
                            $('#{$valueFieldId}').parent().parent().show();
                        }
                    });
                }))
        </script>";

        return $html;
    }

    /**
     * Load attribute property from array
     *
     * @param array $array
     * @return $this
     */
    public function loadArray($array)
    {
        parent::loadArray($array);

        if (isset($array['value_type'])) {
            $this->setValueType($array['value_type']);
        }
        return $this;
    }

    /**
     * Prepare attribute value for product id
     *
     * @param int $productId
     * @param array $additionalParams
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareAttrValueForProductId($productId, $additionalParams = [])
    {
        $linkField = 'entity_id';
        $aliasLinkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $configPath = Custom::XML_PATH_CATALOG_FRONTEND_FLAT_CATALOG_PRODUCT;
        if (!$this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE)) {
            $linkField = $aliasLinkField;
        }

        $attribute = $this->getAttributeObject();
        $attributeCode = $attribute->getAttributeCode();
        $valueType = $this->getValueType();
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($productId);

        if ($attribute->getAttributeCode() == 'category_ids') {
            if (!$productCollection->getFlag('aw_autorelated_collection_category_joined')) {
                $catProductTable = $this->_productResource->getTable('catalog_category_product');
                $productCollection
                    ->getSelect()
                    ->joinLeft(
                        ['cat_product' => $catProductTable],
                        "e.entity_id = cat_product.product_id",
                        []
                    )
                ;
                $productCollection->setFlag('aw_autorelated_collection_category_joined', true);
            }
            if ($valueType == self::VALUE_TYPE_SAME_AS) {
                $productCollection->getSelect()->columns(['attr_value' => "cat_product.category_id"]);
            } elseif ($valueType == self::VALUE_TYPE_CHILD_OF) {
                $catEntityTable = $this->_productResource->getTable('catalog_category_entity');
                $productCollection
                    ->getSelect()
                    ->joinLeft(
                        ['cat_entity' => $catEntityTable],
                        'cat_product.category_id = SUBSTR(cat_entity.path, (LOCATE(CONCAT("/", '
                        . 'cat_product.category_id, "/"), cat_entity.path) + 1), LENGTH(cat_product.category_id))',
                        null
                    );
                $productCollection->getSelect()->columns(
                    ['attr_value' => "COALESCE(GROUP_CONCAT(cat_entity.entity_id), cat_product.category_id)"]
                );
            }
            $productCollection = $this->addFilterByCategoryIdsIfSet($productCollection, $additionalParams);
        } else {
            if ($attribute->isStatic()) {
                $productCollection->getSelect()->columns(['attr_value' => "e.{$attributeCode}"]);
            } else {
                $table = $attribute->getBackendTable();
                if (!$productCollection->getFlag("aw_autorelated_{$table}_joined")) {
                    $productCollection
                        ->getSelect()
                        ->joinLeft(
                            ['attr_table' => $table],
                            "e.{$linkField} = attr_table.{$aliasLinkField}",
                            null
                        )
                        ->where('attr_table.attribute_id=?', $attribute->getId())
                    ;
                    $productCollection->setFlag("aw_autorelated_{$table}_joined", true);
                }
                if ($attribute->isScopeGlobal()) {
                    $productCollection->getSelect()->where('attr_table.store_id=?', 0);
                } else {
                    $productCollection->getSelect()->where('attr_table.store_id=?', 0);
                }
                $productCollection->getSelect()->columns(['attr_value' => "attr_table.value"]);
            }
        }

        $value = null;
        foreach ($productCollection->getData() as $row) {
            if (isset($row['attr_value'])) {
                $value[] = $row['attr_value'];
            }
        }
        if (!$this->getValue()) {
            $this->setValue($value);
        }
        $this->setProudctValue($value);
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param array $additionalParams
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function addFilterByCategoryIdsIfSet($productCollection, $additionalParams)
    {
        if (!empty($additionalParams) && is_array($additionalParams['category_ids'])) {
            $productCollection->getSelect()->where(
                'cat_product.category_id IN(?)',
                $additionalParams['category_ids']
            );
        }
        return $productCollection;
    }

    /**
     * Collect valid attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int|null $productId
     * @param array $additionalParams
     * @return $this|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collectValidatedAttributes($productCollection, $productId = null, $additionalParams = [])
    {
        $valueType = $this->getValueType();
        if ($valueType == self::VALUE_TYPE_CONSTANT) {
            return parent::collectValidatedAttributes($productCollection);
        }

        if (!$productId) {
            return $this;
        }

        $this->prepareAttrValueForProductId($productId, $additionalParams);
        $attribute = $this->getAttributeObject();

        $productValue = $this->getProudctValue();
        $operator = $this->getOperator();
        if (null === $productValue) {
            $productValue = [''];
        }
        if ($attribute->getAttributeCode() == 'category_ids') {
            if ($valueType == self::VALUE_TYPE_CHILD_OF) {
                switch ($operator) {
                    case '==':
                    case '{}':
                    case '()':
                        $this->setOperator('==');
                        break;
                    case '!=':
                    case '!{}':
                    case '!()':
                        $this->setOperator('!=');
                        break;
                }
                $this->setValue(array_shift($productValue));
                return parent::collectValidatedAttributes($productCollection);
            } else {
                $this->setValue(array_shift($productValue));
                return parent::collectValidatedAttributes($productCollection);
            }
        } else {
            $this->setValue(array_shift($productValue));
            return parent::collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
