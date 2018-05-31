<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Model\ResourceModel\Product\Option;

/**
 * @copyright Wyomind 2016
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
{
    
    public function getCustomOptions()
    {
        
        $connection = $this->_resource;
        $tableCpot = $connection->getTable("catalog_product_option_title");
        $tableCpotv = $connection->getTable("catalog_product_option_type_value");
        $tableCpott = $connection->getTable("catalog_product_option_type_title");
        $tableCpotp = $connection->getTable("catalog_product_option_type_price");
        
        $this->getSelect()
                ->columns(['product_id'])
                ->joinleft(["cpot" => $tableCpot], "cpot.option_id=main_table.option_id AND cpot.store_id=0", ["option" => "title", "option_id", "store_id"])
                ->joinleft(["cpotv" => $tableCpotv], "cpotv.option_id = main_table.option_id", "sku")
                ->joinleft(["cpott" => $tableCpott], "cpott.option_type_id=cpotv.option_type_id AND cpott.store_id=cpot.store_id", "title AS value")
                ->joinleft(["cpotp" => $tableCpotp], "cpotp.option_type_id=cpotv.option_type_id AND cpotp.store_id=cpot.store_id", ["price", "price_type"])
                ->order(["product_id", "cpotv.sort_order ASC"]);


        $r = $this->getData();
        $customOptions = [];
        $i = 0;
        foreach ($r as $customOption) {
            $customOptions[$customOption["product_id"]][$customOption["option"]]["options"][] = [
                "value" => $customOption["value"],
                "sku" => $customOption["sku"],
                "price" => $customOption["price"],
                "price_type" => $customOption["price_type"]
            ];

            $i++;
        }
        
        return $customOptions;
    }
}
