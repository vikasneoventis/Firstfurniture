<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Class AbstractRenderer
 *
 * @method \Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column getColumn()
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing
 */
abstract class AbstractRenderer extends \Magento\Backend\Block\Template
{
    /**
     * Retrieve data from row
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        return $row->getData($this->getColumn()->getColumnName());
    }
}
