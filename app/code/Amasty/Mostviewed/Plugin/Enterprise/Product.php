<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */

namespace Amasty\Mostviewed\Plugin\Enterprise;

class Product
{
    const UP_SELL_TYPE_NAME = 'upsell-rule';

    const RELATED_TYPE_NAME = 'related-rule';

    const CROSSSELL_TYPE_NAME = 'crosssell-rule';

    /**
     * @var \Amasty\Mostviewed\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var mixed
     */
    protected $_currentProduct;

    /**
     * Product constructor.
     * @param \Amasty\Mostviewed\Helper\Data $helper
     */
    public function __construct(
        \Amasty\Mostviewed\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Checkout\Model\Session $session
    )
    {
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_catalogConfig = $catalogConfig;
        $this->_currentProduct = $this->_registry->registry('current_product');
        $this->_checkoutSession = $session;
    }

    /**
     * @param $items
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection
     */
    public function afterGetItemCollection($items, $findedItems)
    {
        $type = '';
        $excludedProducts = [];
        switch ($items->getData('type')) {
            case self::RELATED_TYPE_NAME:
                $type = \Amasty\Mostviewed\Helper\Data::RELATED_PRODUCTS_CONFIG_NAMESPACE;
                break;
            case self::UP_SELL_TYPE_NAME:
                $type = \Amasty\Mostviewed\Helper\Data::UP_SELLS_CONFIG_NAMESPACE;
                break;
            case self::CROSSSELL_TYPE_NAME:
                $type = \Amasty\Mostviewed\Helper\Data::CROSS_SELLS_CONFIG_NAMESPACE;
                $quoteItems = $this->_checkoutSession->getQuote()->getItemsCollection();
                if ($quoteItems->getSize()) {
                    $excludedProducts = $this->_helper->getCartProductIds($quoteItems->getItems());
                    $this->_currentProduct = $this->_helper->getLastAddedProductInCart($quoteItems);
                }
        }

        if ($this->_currentProduct && $type) {
            $registry = $this->_registry->registry('amcollection_is_modified_' . $type);
            if ($registry !== null) {
                return $this->_registry->registry('amcollection_is_modified_' . $type);
            } else {
                $modifiedCollection = $this->_helper->itemsCollectionModifiedByType(
                    $type,
                    $this->_currentProduct,
                    $this->_catalogConfig,
                    $findedItems,
                    $excludedProducts
                );

                $mergedItems = [];

                switch (true) {
                    case (is_array($modifiedCollection) && sizeof($modifiedCollection)):
                        $mergedItems = $modifiedCollection;
                        break;
                    case (is_object($modifiedCollection) && $modifiedCollection->getItems()):
                        $mergedItems = $modifiedCollection->getItems();
                        break;
                }

                $this->_registry->register('amcollection_is_modified_' . $type, $mergedItems);

                return $mergedItems;
            }
        } else {
            return $items;
        }
    }
}