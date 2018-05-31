<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Mostviewed\Block\Checkout\Cart;

use Magento\CatalogInventory\Helper\Stock as StockHelper;

class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    /**
     * @var \Amasty\Mostviewed\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Crosssell constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context          $context
     * @param \Magento\Checkout\Model\Session                 $checkoutSession
     * @param \Magento\Catalog\Model\Product\Visibility       $productVisibility
     * @param \Magento\Catalog\Model\Product\LinkFactory      $productLinkFactory
     * @param \Magento\Quote\Model\Quote\Item\RelatedProducts $itemRelationsList
     * @param StockHelper                                     $stockHelper
     * @param \Amasty\Mostviewed\Helper\Data                  $helper
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\LinkFactory $productLinkFactory,
        \Magento\Quote\Model\Quote\Item\RelatedProducts $itemRelationsList,
        StockHelper $stockHelper,
        \Amasty\Mostviewed\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        parent::__construct(
            $context, $checkoutSession, $productVisibility, $productLinkFactory,
            $itemRelationsList, $stockHelper, $data
        );
    }


    public function getItems()
    {
        $items = $this->getData('items');
        if (!is_null($items))
            return $items;

        $alreadyInCartIds = $this->_getCartProductIds();
        if (!$alreadyInCartIds){
            return parent::getItems();
        }
        if (!$this->helper->getBlockConfig('cross_sells', 'enabled')) {
            return parent::getItems();
        }

        $id = (int)$this->_getLastAddedProductId();
        if (!$id){
            $id = current($alreadyInCartIds);
        }
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);

        $replace = $this->helper->getBlockConfig('cross_sells', 'replace');
        switch($replace) {
            case \Amasty\Mostviewed\Model\Config\Source\Manually::REPLACE:
                $items =  $this->helper->getViewedWith($product, 'cross_sells', $alreadyInCartIds, $this->_catalogConfig);
                break;
            case \Amasty\Mostviewed\Model\Config\Source\Manually::APPEND:
                $items = parent::getItems();
                $this->_itemCollection = $this->helper->getViewedWith($product, 'cross_sells', $alreadyInCartIds, $this->_catalogConfig, $items);
                break;
            case \Amasty\Mostviewed\Model\Config\Source\Manually::NOTHING:
                $items = parent::getItems();
                if (!count($items)) {
                    $items = $this->helper->getViewedWith($product, 'cross_sells', $alreadyInCartIds, $this->_catalogConfig);
                }
                break;
        }

        $this->setData('items', $items);

        return $items;
    }
}
