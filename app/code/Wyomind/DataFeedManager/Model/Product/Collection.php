<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Model\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    // n'existe plus dans Magento 2 !
    public function isEnabledFlat()
    {
        return false;
    }

    public function getCollection()
    {
        return $this;
    }
}
