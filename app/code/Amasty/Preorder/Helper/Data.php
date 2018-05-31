<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BACKORDERS_PREORDER_OPTION = 101;
    const ALLOWED_TAGS
        = '<b><a><i><strong><blockquote><code><del><em><img><kbd><p><s><sup><sub><br><hr><ul><li><h1><h2><h3><dd><dl>';

    protected $isOrderProcessing = false;

    /**
     * @var Templater
     */
    private $templater;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $outputHelper;

    /**
     * @var \Amasty\Preorder\Model\ResourceModel\OrderPreorderFactory
     */
    private $orderPreorderResourceFactory;

    /**
     * @var \Amasty\Preorder\Model\OrderPreorderFactory
     */
    private $orderPreorderFactory;

    /**
     * @var \Amasty\Preorder\Model\OrderItemPreorderFactory
     */
    private $orderItemPreorderFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    public function __construct(
        Context $context,
        \Amasty\Preorder\Helper\Templater $templater,
        \Magento\Catalog\Helper\Output $outputHelper,
        \Amasty\Preorder\Model\ResourceModel\OrderPreorderFactory $orderPreorderResourceFactory,
        \Amasty\Preorder\Model\OrderPreorderFactory $orderPreorderFactory,
        \Amasty\Preorder\Model\OrderItemPreorderFactory $orderItemPreorderFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->templater = $templater;
        $this->outputHelper = $outputHelper;
        $this->orderPreorderResourceFactory = $orderPreorderResourceFactory;
        $this->orderPreorderFactory = $orderPreorderFactory;
        $this->orderItemPreorderFactory = $orderItemPreorderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->orderFactory = $orderFactory;
        $this->filterManager = $filterManager;
    }

    public function checkNewOrder(\Magento\Sales\Model\Order $order)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->orderPreorderResourceFactory->create();

        $alreadyProcessed = $order->getId() && $orderPreorderResource->getIsOrderProcessed($order->getId());
        if (!$alreadyProcessed) {
            if ($order->getId() === null) {
                $order->save();
            }

            $this->processNewOrder($order);
        }

        // Will work for normal email flow only. Deprecated.
        if ($this->getOrderIsPreorderFlag($order)) {
            $order->setData('preorder_warning', $orderPreorderResource->getWarningByOrderId($order->getId()));
        }
    }

    protected function processNewOrder(\Magento\Sales\Model\Order $order)
    {
        $this->isOrderProcessing = true;
        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection */
        $itemCollection = $order->getItemsCollection();

        $orderIsPreorder = false;
        foreach ($itemCollection as $item) {

            if ($this->isProductQtyZero($item) && ($this->getOrderedQtyForItem($item) == 1)) {
                continue;
            }

            /** @var \Magento\Sales\Model\Order\Item $item */
            $orderItemIsPreorder = $this->getOrderItemIsPreorder($item);
            $this->saveOrderItemPreorderFlag($item, $orderItemIsPreorder);

            $orderIsPreorder |= $orderItemIsPreorder;
        }

        /** @var \Amasty\Preorder\Model\OrderPreorder $orderPreorder */
        $orderPreorder = $this->orderPreorderFactory->create();

        $orderPreorder->setOrderId($order->getId());
        $orderPreorder->setIsPreorder($orderIsPreorder);
        if ($orderIsPreorder) {
            $warningText = $this->getCurrentStoreConfig('ampreorder/general/orderpreorderwarning');
            $orderPreorder->setWarning($warningText);
        }

        $orderPreorder->save();
    }

    /**
     * @param $quoteItem
     * @return bool
     */
    protected function isProductQtyZero($quoteItem)
    {
        $extensionProductAttributes = $quoteItem->getProduct()->getExtensionAttributes();
        $productQty = $extensionProductAttributes->getStockItem()->getQty();

        if (($productQty !== false) && (int)$productQty == 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $quoteItem
     * @return mixed
     */
    protected function getOrderedQtyForItem($quoteItem)
    {
        return (int)$quoteItem->getQtyOrdered();
    }

    protected function getOrderItemIsPreorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        /** @var Product $product */
        $product = $orderItem->getProduct();
        $result = $this->getIsProductPreorder($product);

        if (!$result) {
            foreach ($orderItem->getChildrenItems() as $childItem) {
                $result = $this->getOrderItemIsPreorder($childItem);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

    protected function saveOrderItemPreorderFlag(\Magento\Sales\Model\Order\Item $orderItem, $isPreorder)
    {
        /** @var \Amasty\Preorder\Model\OrderItemPreorder $orderItemPreorder */
        $orderItemPreorder = $this->orderItemPreorderFactory->create();

        $orderItemPreorder->setOrderItemId($orderItem->getId());
        $orderItemPreorder->setIsPreorder($isPreorder);

        $orderItemPreorder->save();
    }

    public function getQuoteItemIsPreorder(\Magento\Quote\Model\Quote\Item $item, $qtyMultiplier = 1)
    {
        $product = $item->getProduct();
        $qty = $item->getQty() * $qtyMultiplier;

        if ($product->isComposite()) {
            $productTypeInstance = $product->getTypeInstance();

            if ($productTypeInstance instanceof Configurable) {
                /** @var Configurable $productTypeInstance */

                /** @var \Magento\Quote\Model\Quote\Item\Option $option */
                $option = $item->getOptionByCode('simple_product');
                $simpleProduct = $option->getProduct();
                if (!$simpleProduct instanceof Product) {
                    return false;
                }
                return $this->getIsSimpleProductPreorder($simpleProduct, $qty);
            }

            if ($productTypeInstance instanceof \Magento\Bundle\Model\Product\Type) {
                /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */

                $isPreorder = false;
                foreach ($item->getChildren() as $childItem) {
                    if ($this->getQuoteItemIsPreorder($childItem, $qty)) {
                        $isPreorder = true;
                        break;
                    }
                }
                return $isPreorder;
            }
        } else {
            return $this->getIsSimpleProductPreorder($product, $qty);
        }

        return false;
    }

    public function getIsProductPreorder(Product $product)
    {
        if (is_null($product->getIsPreorder())) {
            if ($product->isComposite()) {
                $result = $this->getIsCompositeProductPreorder($product);
            } else {
                $result = $this->getIsSimpleProductPreorder($product);
            }
            $product->setIsPreorder($result);
        }

        return $product->getIsPreorder();
    }

    protected function getIsCompositeProductPreorder(Product $product)
    {
        if (!$this->getCurrentStoreConfig('ampreorder/additional/discovercompositeoptions')) {
            // We never know what options customer will select
            return false;
        }

        $typeId = $product->getTypeId();
        $typeInstance = $product->getTypeInstance();

        switch ($typeId) {
            case 'grouped':
                $result = $this->getIsGroupedProductPreorder($typeInstance, $product);
                break;

            case 'configurable':
                $result = $this->getIsConfigurableProductPreorder($typeInstance, $product);
                break;

            case 'bundle':
                $result = $this->getIsBundleProductPreorder($typeInstance, $product);
                break;

            default:
                //Cannot determinate pre-order status of product of unknown product type
                $result = false;
        }

        // Still have no implementation for bundles
        return $result;
    }

    protected function getIsGroupedProductPreorder(Grouped $typeInstance, Product $product)
    {
        $elementaryProducts = $typeInstance->getAssociatedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while
        foreach ($elementaryProducts as $elementary) {
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getIsConfigurableProductPreorder(Configurable $typeInstance, Product $product)
    {
        $elementaryProducts = $typeInstance->getUsedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while
        foreach ($elementaryProducts as $elementary) {
            /** @var Product $elementary */
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getIsBundleProductPreorder(
        \Magento\Bundle\Model\Product\Type $typeInstance,
        Product $product
    ) {
        $optionIds = $optionSelectionCounts = $optionPreorder = [];

        $options = $typeInstance->getOptionsCollection($product);
        foreach ($options as $option) {
            /** @var \Magento\Bundle\Model\Option $option */
            if (!$option->getRequired()) {
                continue;
            }

            $id = $option->getId();
            $optionIds[] = $id;
            $optionSelectionCounts[$id] = 0; // for a while
            $optionPreorder[$id] = true; // for a while
        }
        if (!$optionIds) {
            return false;
        }

        $selections = $typeInstance->getSelectionsCollection($optionIds, $product);
        $products = $this->getProductCollectionBySelectionsCollection($selections);
        foreach ($selections as $selection) {
            /** @var \Magento\Bundle\Model\Selection $selection */

            /** @var Product $product */
            $product = $products->getItemById($selection->getProductId());

            $isPreorder = $this->getIsSimpleProductPreorder($product);
            $optionId = $selection->getOptionId();
            $optionSelectionCounts[$optionId]++;
            if (!$isPreorder) {
                $optionPreorder[$optionId] = false;
            }
        }

        $result = false; // for a while
        foreach ($optionPreorder as $id => $isPreorder) {
            if ($isPreorder && $optionSelectionCounts[$id] > 0) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    protected function getProductCollectionBySelectionsCollection($selections)
    {
        $productIds = [];
        foreach ($selections as $selection) {
            /** @var \Magento\Bundle\Model\Selection $selection */
            $productIds[] = $selection->getProductId();
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection =  $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in', $productIds]);

        return $collection;
    }

    protected function getIsSimpleProductPreorder(Product $product, $requiredQty = 1)
    {
        /** @var \Magento\CatalogInventory\Model\Stock\Item $inventory */
        $inventory = $this->stockRegistry->getStockItem($product->getId());

        $isPreorder = $inventory->getBackorders() == self::BACKORDERS_PREORDER_OPTION;
        $qtyStock = $inventory->getQty();

        $disabledByQty = $this->disableForPositiveQty() && $qtyStock > 0
            && ($qtyStock >= $requiredQty || $this->isOrderProcessing);

        $result = $isPreorder && !$disabledByQty;

        return $result;
    }

    public function getOrderIsPreorderFlagByIncrementId($incrementId)
    {
        // finally convert back to string to optimize SQL query
        $incrementId = ''. (int)$incrementId;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->load($incrementId, 'increment_id');

        if (!$order->getId()) {
            $message = 'Preorder: Cannot load order by incrementId = ' . $incrementId;
            return false;
        }

        return $this->getOrderIsPreorderFlag($order);
    }

    public function getOrderIsPreorderFlag(\Magento\Sales\Model\Order $order)
    {
        if ($order == null) {
            //Preorder: Cannot load preorder flag for null order. Processing as a regular order.
            return false;
        }

        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->orderPreorderFactory->create()->getResource();

        return $orderPreorderResource->getOrderIsPreorderFlag($order->getId());
    }

    public function getOrderPreorderWarning($orderId)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->orderPreorderFactory->create()->getResource();

        $warning = $orderPreorderResource->getWarningByOrderId($orderId);
        if ($warning == null) {
            $warning = $this->getCurrentStoreConfig('ampreorder/general/orderpreorderwarning');
        }

        return $warning;
    }

    public function getOrderItemIsPreorderFlag($itemId)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderItemPreorder\Collection $orderItemPreorderCollection */
        $orderItemPreorderCollection = $this->orderItemPreorderFactory->create()->getCollection()
            ->addFieldToFilter('order_item_id', $itemId)
            ->addFieldToSelect('is_preorder');

        $orderItemPreorder = $orderItemPreorderCollection->getFirstItem();

        return is_object($orderItemPreorder) ? $orderItemPreorder->getIsPreorder() : false;
    }

    public function getQuoteItemPreorderNote($quoteItem)
    {
        $note = '';
        $product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() == 'configurable') {
            $option = $quoteItem->getOptionByCode('simple_product');
            $product = $option->getProduct();
        }

        if ($this->getIsProductPreorder($product)) {
            $note = $this->getProductPreorderNote($product);
        }

        return $note;
    }

    public function getProductPreorderNote(Product $product)
    {
        $template = $product->getData('amasty_preorder_note');
        if ($template === null) {
            $template = $product->getResource()
                ->getAttributeRawValue($product->getId(), 'amasty_preorder_note', $product->getStoreId());
        }

        if (!$template) {
            $template = $this->getCurrentStoreConfig('ampreorder/general/defaultpreordernote');
        }

        $template = $this->filterManager->stripTags($template, ['allowableTags' => self::ALLOWED_TAGS]);

        /* validate output - remove to validate html*/
        /* $template = $this->outputHelper->productAttribute(
            $product,
            $template,
            'amasty_preorder_note'
        );*/

        $note = $this->templater->process($template, $product);
        if (is_array($note)) {
            $note = implode($note);
        }

        return $note;
    }

    public function getProductPreorderCartLabel(Product $product)
    {
        $template = $product->getData('amasty_preorder_cart_label');
        if ($template === null) {
            $template = $product->getResource()
                ->getAttributeRawValue($product->getId(), 'amasty_preorder_cart_label', $product->getStoreId());
        }

        if (!$template) {
            $template = $this->getCurrentStoreConfig('ampreorder/general/addtocartbuttontext');
        }

        $note = $this->templater->process($template, $product);
        if (is_array($note)) {
            $note = implode($note);
        }

        return $note;
    }

    public function getDefaultPreorderCartLabel()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/addtocartbuttontext');
    }

    public function preordersEnabled()
    {
        return $this->getCurrentStoreConfig('ampreorder/functional/enabled');
    }

    public function disableForPositiveQty()
    {
        return $this->getCurrentStoreConfig('ampreorder/functional/allowemptyqty')
            && $this->getCurrentStoreConfig('ampreorder/functional/disableforpositiveqty');
    }

    protected function getCurrentStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
    
    public function getOrderItemPreorderNote(\Magento\Sales\Model\Order\Item $orderItem)
    {
        $product = $orderItem->getProduct();
        if ($orderItem->getProductType() == 'configurable') {
            $option = $orderItem->getOptionByCode('simple_product');
            if ($option) {
                $product = $option->getProduct();
            }
        }

        $note = '';
        if ($product) {
            $note = $this->getProductPreorderNote($product);
        }

        return $note;
    }
}
