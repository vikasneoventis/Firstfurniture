<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Class Text
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer
 */
class Text extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        return '<span>' . parent::render($row) . '</span>';
    }
}
