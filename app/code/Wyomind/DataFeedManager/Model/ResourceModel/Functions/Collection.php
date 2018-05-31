<?php

namespace Wyomind\DataFeedManager\Model\ResourceModel\Functions;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wyomind\DataFeedManager\Model\Functions', 'Wyomind\DataFeedManager\Model\ResourceModel\Functions');
    }
}
