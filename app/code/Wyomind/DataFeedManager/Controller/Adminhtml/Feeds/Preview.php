<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Generate sample action
 */
class Preview extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Execute action
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}
