<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Model\ResourceModel\Review;

/**
 * @copyright Wyomind 2016
 */
class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    
    public function getReviews()
    {
        
        $connection = $this->getConnection();
        
        $mainTable = $this->getMainTable();
        $tableRst = $this->getReviewStoreTable();
        $tableRov = $this->_resource->getTable('rating_option_vote');
        
        
        $sqlByStoreId = $connection->select();
        $sqlByStoreId->distinct('review_id')
                ->from(['r' => $mainTable], ['count(distinct(r.review_id)) as count', 'entity_pk_value'])
                ->joinLeft(['rs' => $tableRst], 'rs.review_id=r.review_id', 'rs.store_id')
                ->joinLeft(['rov' => $tableRov], 'rov.review_id=r.review_id', 'AVG(rov.percent) AS score')
                ->where("status_id=1 and entity_id=1")
                ->group(['r.entity_pk_value', 'rs.store_id']);

        
        $sqlAllStoreId = $connection->select();
        $sqlAllStoreId->distinct('review_id')
                ->from(
                    ['r' => $mainTable],
                    ['count(distinct(r.review_id)) as count', 'entity_pk_value', '(select 0) as store_id']
                )
                ->joinLeft(['rs' => $tableRst], 'rs.review_id=r.review_id', [])
                ->joinLeft(['rov' => $tableRov], 'rov.review_id=r.review_id', 'AVG(rov.percent) AS score')
                ->where("status_id=1 and entity_id=1")
                ->group(['r.entity_pk_value']);

        $select = $connection->select();
        $select->union([$sqlAllStoreId, $sqlByStoreId])
                ->order(['entity_pk_value', 'store_id']);

        $r = $connection->fetchAll($select);
        $reviews = [];
        foreach ($r as $review) {
            $reviews[$review['entity_pk_value']][$review['store_id']]["count"] = $review["count"];
            $reviews[$review['entity_pk_value']][$review['store_id']]['score'] = $review['score'];
        }
        return $reviews;
    }
}
