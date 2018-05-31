<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Plugin\Community;

abstract class AbstractProduct
{
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
     * AbstractProduct constructor.
     * @param \Amasty\Mostviewed\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        \Amasty\Mostviewed\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_catalogConfig = $catalogConfig;
        $this->_currentProduct = $this->_registry->registry('current_product');
        $this->_checkoutSession = $session;
    }

    /**
     * @param string $type
     * @param $collection
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection|mixed
     */
    protected function _prepareCollection($type, $collection)
    {
        $excludedProducts = [];
        if ($type === \Amasty\Mostviewed\Helper\Data::CROSS_SELLS_CONFIG_NAMESPACE) {
            $quoteItems = $this->_checkoutSession->getQuote()->getItemsCollection();
            $excludedProducts = $this->_getExcludedProducts($quoteItems);
            $this->_setCurrentProductForCart($quoteItems);
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
                    $collection,
                    $excludedProducts
                );
                $this->_registry->register('amcollection_is_modified_' . $type, $modifiedCollection);

                return $modifiedCollection;
            }
        } else {
            return $collection;
        }
    }

    /**
     * @param $quoteItems
     */
    protected function _setCurrentProductForCart($quoteItems)
    {
        if ($quoteItems->getSize()) {
            $this->_currentProduct = $this->_helper->getLastAddedProductInCart($quoteItems);
        } else {
            $this->_currentProduct = null;
        }
    }

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $quoteItems
     * @return array
     */
    protected function _getExcludedProducts($quoteItems)
    {
        $excludedProducts = [];
        if ($quoteItems->getSize()) {
            $excludedProducts = $this->_helper->getCartProductIds($quoteItems->getItems());

            return $excludedProducts;
        } else {
            return $excludedProducts;
        }
    }
}
