<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-06T14:24:49+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Log/Grid/Renderer/Source.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Log\Grid\Renderer;

class Source extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public static $sources = [];

    /**
     * @var \Xtento\TrackingImport\Model\SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var \Xtento\TrackingImport\Model\System\Config\Source\Source\Type
     */
    protected $sourceType;

    /**
     * Source constructor.
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Xtento\TrackingImport\Model\SourceFactory $sourceFactory
     * @param \Xtento\TrackingImport\Model\System\Config\Source\Source\Type $sourceType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Xtento\TrackingImport\Model\SourceFactory $sourceFactory,
        \Xtento\TrackingImport\Model\System\Config\Source\Source\Type $sourceType,
        array $data = []
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->sourceType = $sourceType;
        parent::__construct($context, $data);
    }

    /**
     * Render log
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $sourceIds = $row->getSourceIds();
        $sourceText = "";
        if (empty($sourceIds)) {
            return __('No source selected. Enable in the "Import Sources" tab of the profile.');
        }
        foreach (explode("&", $sourceIds) as $sourceId) {
            if (!empty($sourceId) && is_numeric($sourceId)) {
                if (!isset(self::$sources[$sourceId])) {
                    $source = $this->sourceFactory->create()->load(
                        $sourceId
                    );
                    self::$sources[$sourceId] = $source;
                } else {
                    $source = self::$sources[$sourceId];
                }
                if ($source->getId()) {
                    $sourceText .= $source->getName() . " (" . $this->sourceType->getName(
                            $source->getType()
                        ) . ")<br>";
                }
            }
        }
        return $sourceText;
    }
}
