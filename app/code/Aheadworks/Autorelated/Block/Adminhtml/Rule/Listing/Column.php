<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing;

use Magento\Framework\DataObject;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\AbstractRenderer;
use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer\Header\AbstractRenderer
    as HeaderAbstractRenderer;

/**
 * Class Column
 *
 * @method string getRenderer()
 * @method string getHeaderRenderer()
 * @method string getType()
 * @method string getHeader()
 * @method string getColumnName()
 * @method string getPrimaryFieldName()
 * @method string getTypeFieldName()
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing
 */
class Column extends \Magento\Framework\DataObject
{
    /**
     * @var array
     */
    private $classTypes = [
        'actions' => Column\Renderer\Actions::class,
        'text' => Column\Renderer\Text::class,
        'default' => Column\Renderer\Text::class
    ];

    /**
     * @var string
     */
    private $defaultHeaderRendererClass = Column\Renderer\Header\DefaultRenderer::class;

    /**
     * @var AbstractRenderer
     */
    private $renderer;

    /**
     * @var HeaderAbstractRenderer
     */
    private $headerRenderer;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
    ) {
        parent::__construct($data);
        $this->layout = $layout;
    }

    /**
     * Retrieve column renderer
     *
     * @return AbstractRenderer
     */
    public function getColumnRenderer()
    {
        if ($this->renderer === null) {
            if ($this->getRenderer()) {
                $rendererClass = $this->getRenderer();
            } else {
                $rendererClass = $this->getClassType($this->getType());
            }

            $this->renderer = $this->layout->createBlock(
                $rendererClass,
                '',
                ['data' => ['column' => $this]]
            );
        }

        return $this->renderer;
    }

    /**
     * Retrieve class type
     *
     * @param string $type
     * @return $this
     */
    private function getClassType($type)
    {
        $type = strtolower($type);
        if (isset($this->classTypes[$type])) {
            return $this->classTypes[$type];
        }

        return $this->classTypes['default'];
    }

    /**
     * Retrieve css class name for current column
     *
     * @return string
     */
    public function getCssClass()
    {
        return 'col-' . $this->getColumnName();
    }

    /**
     * Retrieve header for specified listing type
     *
     * @param int $listingType
     * @return string
     */
    public function getHeaderHtml($listingType)
    {
        return $this->getHeaderRendererObject()
            ->setListingType($listingType)
            ->render();
    }

    /**
     * Retrieve column header renderer object
     *
     * @return HeaderAbstractRenderer
     */
    private function getHeaderRendererObject()
    {
        if ($this->headerRenderer === null) {
            if ($this->getHeaderRenderer()) {
                $headerRendererClass = $this->getHeaderRenderer();
            } else {
                $headerRendererClass = $this->defaultHeaderRendererClass;
            }

            $this->headerRenderer = $this->layout->createBlock(
                $headerRendererClass,
                '',
                ['data' => ['column' => $this]]
            );
        }

        return $this->headerRenderer;
    }
}
