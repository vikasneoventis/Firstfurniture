<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header;

/**
 * Class AbstractRenderer
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header
 */
class DefaultRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->getColumn()->getHeader();
    }
}
