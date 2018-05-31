<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Create new data feed action
 * Called NewAction because New will throw a syntax error !!
 */
class NewAction extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Execute action => redirect to edit
     */
    public function execute()
    {
        return parent::newAction();
    }
}
