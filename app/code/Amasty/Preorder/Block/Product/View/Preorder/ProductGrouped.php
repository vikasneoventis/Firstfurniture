<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Block\Product\View\Preorder;


class ProductGrouped extends ProductAbstract
{
    /**
     * @return array
     */
    public function getGroupPreorderMap()
    {
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
        $typeInstance = $this->getProduct()->getTypeInstance();

        $elementaryProducts = $typeInstance->getAssociatedProducts($this->getProduct());

        $map = [];
        foreach ($elementaryProducts as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            if($this->helper->getIsProductPreorder($product)) {
                $map[$product->getId()] = [
                    'cartLabel' => $this->helper->getProductPreorderCartLabel($product),
                    'note'      => $this->helper->getProductPreorderNote($product),
                ];
            }
        }

        return $map;
    }
}
