<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Mostviewed\Block\Catalog\Product\ProductList;


class Upsell extends \Magento\Catalog\Block\Product\ProductList\Upsell
{
    /**
     * @var \Amasty\Mostviewed\Helper\Data
     */
    protected $helper;

    /**
     * Upsell constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context     $context
     * @param \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Product\Visibility  $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Magento\Framework\Module\Manager          $moduleManager
     * @param \Amasty\Mostviewed\Helper\Data             $helper
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\Mostviewed\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context, $checkoutCart, $catalogProductVisibility,
            $checkoutSession, $moduleManager, $data
        );
    }


    protected function _prepareData()
    {
        if (!$this->helper->getBlockConfig('up_sells', 'enabled')) {
            return parent::_prepareData();
        }

        $product = $this->getProduct();

        $replace = $this->helper->getBlockConfig('up_sells', 'replace');
        switch($replace) {
            case \Amasty\Mostviewed\Model\Config\Source\Manually::REPLACE:
                $this->_itemCollection = $this->helper->getViewedWith($product, 'up_sells', [], $this->_catalogConfig);
                break;
            case \Amasty\Mostviewed\Model\Config\Source\Manually::APPEND:
                parent::_prepareData();
                $this->_itemCollection = $this->helper->getViewedWith($product, 'up_sells', [], $this->_catalogConfig, $this->_itemCollection);
                break;
            case \Amasty\Mostviewed\Model\Config\Source\Manually::NOTHING:
                parent::_prepareData();
                if (!count($this->_itemCollection)) {
                    $this->_itemCollection = $this->helper->getViewedWith($product, 'up_sells', [], $this->_catalogConfig);
                }
                break;
        }

        return $this;
    }
}
