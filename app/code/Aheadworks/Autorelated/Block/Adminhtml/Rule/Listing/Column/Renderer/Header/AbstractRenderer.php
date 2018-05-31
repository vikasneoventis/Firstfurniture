<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header;

/**
 * Class AbstractRenderer
 *
 * @method \Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column getColumn()
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header
 */
abstract class AbstractRenderer extends \Magento\Backend\Block\Template
{
    /**
     * @var int
     */
    private $listingType;

    /**
     * Render column header
     *
     * @return string
     */
    abstract public function render();

    /**
     * Set listing type for header rendering
     *
     * @param int $listingType
     * @return $this
     */
    public function setListingType($listingType)
    {
        $this->listingType = $listingType;
        return $this;
    }

    /**
     * Retrieve listing type for header rendering
     *
     * @return int
     */
    public function getListingType()
    {
        return $this->listingType;
    }
}
