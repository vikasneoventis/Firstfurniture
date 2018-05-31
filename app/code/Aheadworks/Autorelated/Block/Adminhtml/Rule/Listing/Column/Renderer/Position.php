<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

use Magento\Framework\DataObject;
use Aheadworks\Autorelated\Model\Source;
use Magento\Backend\Block\Template\Context;

/**
 * Class Position
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer
 */
class Position extends AbstractRenderer
{
    /**
     * @var Source\Position
     */
    private $positionSource;

    /**
     * @param Context $context
     * @param Source\Position $positionSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Source\Position $positionSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->positionSource = $positionSource;
    }

    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $position = $row->getPosition();

        return $this->positionSource->getPositionLabel($position);
    }
}
