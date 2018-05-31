<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-04-12T11:23:57+00:00
 * File:          app/code/Xtento/TrackingImport/Model/Processor/Mapping/Action/Configuration.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Model\Processor\Mapping\Action;

use Xtento\TrackingImport\Model\Processor\Mapping\AbstractConfiguration;

class Configuration extends AbstractConfiguration
{
    protected $configurationType = 'action';

    /*
     * If "set" node is set in XML configuration, [...]
     */
    public function setValueBasedOnFieldData($updateData, $fieldConfiguration)
    {
        $changeData = -99;
        // Check if import of current row should be skipped
        if (isset($fieldConfiguration['set'])) {
            if (count($fieldConfiguration['set']) > 1) {
                // Multiple <set> nodes
                foreach ($fieldConfiguration['set'] as $config) {
                    $changeData = $this->changeCheck($updateData, $config);
                }
            } else {
                // One <set> node
                $config = $fieldConfiguration['set'];
                $changeData = $this->changeCheck($updateData, $config);
            }
        }
        if ($changeData === 'true') {
            $changeData = true;
        }
        if ($changeData === 'false') {
            $changeData = false;
        }
        return $changeData;
    }

    protected function changeCheck($updateData, $config)
    {
        if (isset($config['@'])) {
            $configAttributes = $config['@'];
            if (isset($configAttributes['if']) && isset($configAttributes['is']) && isset($configAttributes['value'])) {
                // Matching method
                #var_dump($updateData); die();
                if (isset($updateData[$configAttributes['if']])) {
                    $matchValue = $updateData[$configAttributes['if']];
                } else {
                    $matchValue = "";
                }
                if (!isset($configAttributes['method']) || (isset($configAttributes['method']) && $configAttributes['method'] == 'equals')) {
                    // No method specified, exact matching
                    if ($matchValue == $configAttributes['is']) { // If field "if" is "is" then use "field"
                        return $configAttributes['value'];
                    }
                } else {
                    if (trim($configAttributes['method']) == 'preg_match') {
                        // preg_match
                        if (!isset($configAttributes['regex_modifier'])) {
                            $configAttributes['regex_modifier'] = '';
                        } else {
                            $configAttributes['regex_modifier'] = str_replace(
                                "e",
                                "",
                                $configAttributes['regex_modifier']
                            );
                        }
                        if (preg_match(
                            '/' . str_replace(
                                '/',
                                '\\/',
                                $configAttributes['is']
                            ) . '/' . $configAttributes['regex_modifier'],
                            $matchValue
                        )) {
                            return $configAttributes['value'];
                        }
                    }
                }
            }
        }
        return -99;
    }
}
