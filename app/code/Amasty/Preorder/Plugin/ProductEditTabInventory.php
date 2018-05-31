<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Plugin;

class ProductEditTabInventory
{
    protected $helper;

    public function __construct(\Amasty\Preorder\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function aroundToHtml(
        \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Inventory $subject,
        \Closure $closure
    ) {
        $html = $closure();
        $preorderHtml = $subject->getLayout()
            ->createBlock('Amasty\Preorder\Block\Adminhtml\Product\Edit\Tab\Inventory\PreOrder')
            ->toHtml();

        $preorderJsHtml = $subject->getLayout()
            ->createBlock('Amasty\Preorder\Block\Adminhtml\Product\Edit\Tab\Inventory\PreOrderJs')
            ->setTemplate('Amasty_Preorder::product_inventory_js.phtml')
            ->toHtml();

        $html .= $preorderHtml . PHP_EOL . $preorderJsHtml;

        return $html;

    }
}
