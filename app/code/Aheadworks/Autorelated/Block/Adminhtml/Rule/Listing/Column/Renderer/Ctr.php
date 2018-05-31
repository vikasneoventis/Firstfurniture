<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Class Ctr
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer
 */
class Ctr extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        return (int)$row->getCtr() . '%';
    }
}
