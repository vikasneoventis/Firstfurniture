<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Model;

/**
 * Function modem
 */
class Functions extends \Magento\Framework\Model\AbstractModel
{

    public function _construct()
    {
        $this->_init('Wyomind\DataFeedManager\Model\ResourceModel\Functions');
    }
}
