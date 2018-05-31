<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin;

use \Magento\CatalogWidget\Block\Product\ProductsList as WidgetProductList;
use \Magento\Catalog\Block\Product\ListProduct as CatalogProductList;
use \Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;

abstract class AbstractProductList
{
    const PREORDER = 'amasty_preorder_product_observer';

    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $currentProduct;

    public function __construct(
        \Amasty\Preorder\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param \Magento\Catalog\Model\Product $product
     */
    public function beforeGetProductPrice($subject, $product)
    {
        $this->currentProduct = $product;
    }

    /**
     * @param CatalogProductList | WidgetProductList $subject
     * @param string $resultHtml
     *
     * @return string
     */
    public function afterGetProductPrice($subject, $resultHtml)
    {
        return $this->getHtmlPreorder($subject, $this->currentProduct) . $resultHtml;
    }

    /**
     * @param $subject
     * @param \Magento\Catalog\Model\Product $product
     */
    public function beforeGetProductPriceHtml($subject, $product)
    {
        $this->currentProduct = $product;
    }

    /**
     * @param CatalogProductList | WidgetProductList $subject
     * @param string $resultHtml
     *
     * @return string
     */
    public function afterGetProductPriceHtml($subject, $resultHtml)
    {
        return $this->getHtmlPreorder($subject, $this->currentProduct) . $resultHtml;
    }

    /**
     * Check if product product is already preordered.
     *
     * @param CatalogProductList | WidgetProductList $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    protected function getHtmlPreorder(
        $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $htmlPreorder = '';
        if (!in_array($product->getId(), $this->getRegistryCustomVariable())) {
            if ($this->helper->preordersEnabled() && $this->helper->getIsProductPreorder($product)) {
                $htmlPreorder = $subject->getLayout()
                    ->createBlock('Amasty\Preorder\Block\Product\ListProduct\Preorder')
                    ->setProduct($product)->setTemplate('product/list/preorder.phtml')->toHtml();
            }

            if ($this->helper->preordersEnabled()
                && ($product->getTypeId() == ProductTypeConfigurable::TYPE_CODE)
            ) {
                $htmlPreorder = $subject->getLayout()
                    ->createBlock('Amasty\Preorder\Block\Product\View\Preorder\ProductConfigurable')
                    ->setTemplate('product/list/configurable.phtml')->setProduct($product)->toHtml();
            }

            $this->setRegistryCustomVariable($product->getId());
        }

        return $htmlPreorder;
    }

    /**
     * @param $id
     */
    protected function setRegistryCustomVariable($id)
    {
        $preOrderedIds = $this->getRegistryCustomVariable();
        $this->registry->unregister(self::PREORDER);
        $preOrderedIds[] = $id;
        $this->registry->register(self::PREORDER, $preOrderedIds);
    }

    /**
     * @return string
     */
    protected function getRegistryCustomVariable()
    {
        $arrayWithId = $this->registry->registry(self::PREORDER);
        if (!is_array($arrayWithId)) {
            $arrayWithId = [];
        }

        return $arrayWithId;
    }
}
