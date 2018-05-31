<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Model\ResourceModel;

class Feeds extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
 
    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('datafeedmanager_feeds', 'id');
    }
    
    public function importDataFeed($request)
    {
        
        
        $connection = $this->getConnection('write');
        $request = str_replace("{{datafeedmanager_feeds}}", $connection->getTableName('datafeedmanager_feeds'), $request);
        return $connection->query($request);
        
    }
}
