<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{

    private $_feedsCollection = null;
    private $_variablesCollection = null;
    private $_functionsCollection = null;
    private $_state = null;

    public function __construct(
        \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\Collection $feedsCollection,
        \Wyomind\DataFeedManager\Model\ResourceModel\Variables\Collection $variablesCollection,
        \Wyomind\DataFeedManager\Model\ResourceModel\Functions\Collection $functionsCollection,
        \Magento\Framework\App\State $state
    ) {
    
        $this->_feedsCollection = $feedsCollection;
        $this->_variablesCollection = $variablesCollection;
        $this->_functionsCollection = $functionsCollection;
        $this->_state = $state;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /**
         * upgrade to 9.0.0
         */
        if (version_compare($context->getVersion(), '9.0.0') < 0) {
            try {
                $this->_state->setAreaCode('admin');
            } catch (\Exception $e) {
            }
            foreach ($this->_feedsCollection as $feed) {
                $pattern = str_replace(["'{{", "}}'",'"{{', '}}"', "php="], ["{{", "}}","{{", "}}", "output="], $feed->getProductPattern());
                $feed->setProductPattern($pattern);
                $feed->save();
            }
        }
        /**
         * upgrade to 9.0.1
         * $myPattern = null; becomes $this->skip = true;
         */
        if (version_compare($context->getVersion(), '9.0.1') < 0) {
            try {
                $this->_state->setAreaCode('admin');
            } catch (\Exception $e) {
            }
            $re = '/\$myPattern\s*=\s*null;/';
            foreach ($this->_feedsCollection as $feed) {
                $pattern = $feed->getSimplegoogleshoppingXmlitempattern();
                preg_match_all($re, $pattern, $matches);
                foreach ($matches[0] as $match) {
                    $pattern = str_replace($match, '$this->skip();', $pattern);
                }
                $feed->setSimplegoogleshoppingXmlitempattern($pattern);
                $feed->save();
            }
            foreach ($this->_variablesCollection as $variable) {
                $script = $variable->getScript();
                preg_match_all($re, $script, $matches);
                foreach ($matches[0] as $match) {
                    $pattern = str_replace($match, '$this->skip();', $pattern);
                }
                $variable->getScript($pattern);
                $variable->save();
            }
            foreach ($this->_functionsCollection as $function) {
                $script = $function->getScript();
                preg_match_all($re, $script, $matches);
                foreach ($matches[0] as $match) {
                    $pattern = str_replace($match, '$this->skip();', $pattern);
                }
                $function->getScript($pattern);
                $function->save();
            }
        }
        
        
        if (version_compare($context->getVersion(), '10.0.0') < 0) {
            //.categories index=
            try {
                $this->_state->setAreaCode('admin');
            } catch (\Exception $e) {
            }
            $re = '/.categories([^|}]+)index="?\'?([0-9]+)"?\'?/';
            foreach ($this->_feedsCollection as $feed) {
                $pattern = $feed->getProductPattern();
                $pattern = preg_replace($re, '.categories${1}nth="${2}"', $pattern);
                $feed->setProductPattern($pattern);
                $feed->save();
            }
        }
    }
}
