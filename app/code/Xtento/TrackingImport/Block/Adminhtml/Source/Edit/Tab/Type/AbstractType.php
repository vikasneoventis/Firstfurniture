<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:40:23+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Source/Edit/Tab/Type/AbstractType.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Source\Edit\Tab\Type;

use Magento\Config\Model\Config\Source\Yesno;

abstract class AbstractType extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * AbstractType constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $registry,
        Yesno $yesNo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->yesNo = $yesNo;
    }
}