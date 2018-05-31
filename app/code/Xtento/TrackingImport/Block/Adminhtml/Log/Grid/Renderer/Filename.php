<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-11T17:40:19+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Log/Grid/Renderer/Filename.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Log\Grid\Renderer;

class Filename extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $rowFiles = $row->getFiles();
        if (empty($rowFiles)) {
            return __('No files saved.');
        }
        $filenames = explode("|", $rowFiles);
        $baseFilenames = [];
        foreach ($filenames as $filename) {
            array_push($baseFilenames, basename($filename));
        }
        $baseFilenames = array_unique($baseFilenames);
        $rowText = "";
        foreach ($baseFilenames as $filename) {
            $rowText .= $filename . "<br>";
        }
        return $rowText;
    }
}
