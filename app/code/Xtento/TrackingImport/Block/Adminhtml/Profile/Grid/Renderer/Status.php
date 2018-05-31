<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:40:19+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Profile/Grid/Renderer/Status.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Profile\Grid\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render profile status
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = '';
        $text = '';
        switch ($this->_getValue($row)) {
            case 0:
                $class = 'grid-severity-critical';
                $text = __('Disabled');
                break;
            case 1:
                $class = 'grid-severity-notice';
                $text = __('Enabled');
                break;
        }
        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
