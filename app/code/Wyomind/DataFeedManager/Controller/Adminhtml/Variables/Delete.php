<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Variables;

/**
 * Delete action (grid)
 */
class Delete extends \Wyomind\DataFeedManager\Controller\Adminhtml\Variables\AbstractVariables
{

    /**
     * Execute action
     */
    public function execute()
    {
        return parent::delete();
    }
}
