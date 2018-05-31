<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

namespace Amasty\Preorder\Block\Adminhtml\Product\Edit\Tab\Inventory;
/**
 * Copyright © 2016 Amasty. All rights reserved.
 */


class PreOrderJs extends \Magento\Backend\Block\Template
{

    public function getPreorderId()
    {
        return \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION;
    }

}
