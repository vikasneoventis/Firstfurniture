<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Model\ResourceModel;

class TierPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    public function _construct()
    {
        $this->_init('datafeedmanager_feeds', 'id');
    }

    public function getTierPrices($websiteId)
    {
        $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
        $connection = $this->getConnection();
        $sql = $connection->select();

        $tableCpetp = $connection->getTableName("catalog_product_entity_tier_price");

        $sql->from(["cpetp" => $tableCpetp], [$linkField, "all_groups", "customer_group_id", "value", "qty"]);
        $sql->order(["cpetp.{$linkField}", "cpetp.customer_group_id", "cpetp.qty"]);
        $sql->where("cpetp.website_id=" . $websiteId . " OR cpetp.website_id=0");
        $result = $connection->fetchAll($sql);

        $tierPrices = [];


        foreach ($result as $tp) {
            if ($tp['all_groups'] == 1) {
                $tierPrices[$tp["entity_id"]][32000][] = ["qty" => $tp['qty'], "value" => $tp['value']];
            } else {
                $tierPrices[$tp["entity_id"]][$tp["customer_group_id"]][] = ["qty" => $tp['qty'], "value" => $tp['value']];
            }
        }

        return $tierPrices;
    }
}
