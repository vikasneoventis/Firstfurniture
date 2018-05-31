<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Helper;

/**
 * Attributes management
 */
class AttributesDefault extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * {image} attribute processing
     * @param \Wyomind\DataFeedManager\Model\Feeds $model
     * @param array                                     $options
     * @param \Magento\Catalog\Model\Product            $product
     * @param string                                    $reference
     * @return string product's image
     */
    public function visibility(
        $model,
        $options,
        $product,
        $reference
    ) {
        $item = $model->checkReference($reference, $product);
        if ($item == null) {
            return "";
        }
        return $item->getVisibility();
    }
}
