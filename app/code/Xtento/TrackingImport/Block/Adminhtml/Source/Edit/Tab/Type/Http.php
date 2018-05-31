<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:40:23+00:00
 * File:          app/code/Xtento/TrackingImport/Block/Adminhtml/Source/Edit/Tab/Type/Http.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Block\Adminhtml\Source\Edit\Tab\Type;

class Http extends AbstractType
{
    // HTTP Configuration
    public function getFields(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset(
            'config_fieldset',
            [
                'legend' => __('HTTP Configuration'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldset->addField(
            'http_note',
            'note',
            [
                'text' => __(
                    '<b>Instructions</b>: To import data to a HTTP server, please follow the following steps:<br>1) Go into the <i>app/code/Xtento/TrackingImport/Model/Source/</i> directory and rename the file "Http.php.sample" to "Http.php"<br>2) Enter the function name you want to call in the Http.php class in the field below.<br>3) Open the Http.php file and add a function that matches the function name you entered. This function will be called by this source upon importing then.<br><br><b>Example:</b> If you enter server1 in the function name field below, a method called server1() must exist in the Http.php file. This way multiple HTTP servers can be added to the HTTP class, and can be called from different import source, separated by the function name that is called. The function you add then gets called whenever this source is executed by an import profile.'
                )
            ]
        );

        $fieldset->addField(
            'custom_function',
            'text',
            [
                'label' => __('Custom Function'),
                'name' => 'custom_function',
                'note' => __(
                    'Please make sure the function you enter exists like this in the app/code/Xtento/TrackingImport/Model/Source/Http.php file:<br>public function <i>yourFunctionName</i>() { ... }'
                ),
                'required' => true
            ]
        );
    }
}