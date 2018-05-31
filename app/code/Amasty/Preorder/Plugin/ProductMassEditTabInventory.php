<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Plugin;

/**
 * Plugin for class \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory
 */
class ProductMassEditTabInventory
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    private $attributeFactory;

    public function __construct(\Magento\Eav\Model\Entity\AttributeFactory $attributeFactory)
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Add Pre-Order text attributes to Inventory tab
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory $subject
     * @param string                                                                       $html
     *
     * @return string
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory $subject,
        $html
    ) {
        /**
         * block class @see \Amasty\Preorder\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory\PreOrder
         * block initialized in adminhtml/layout/catalog_product_action_attribute_edit.xml
         */
        $preOrderHtml = $subject->getLayout()
            ->getBlock('amasty_pre_order')
            ->toHtml();

        return $html . $preOrderHtml;
    }
}
