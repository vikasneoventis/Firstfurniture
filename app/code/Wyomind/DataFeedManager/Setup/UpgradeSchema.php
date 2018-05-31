<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        // $context->getVersion() = version du module actuelle
        // 9.0.0 = version en cours d'installation
        if (version_compare($context->getVersion(), '9.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            // do what you have to do
            
            $installer->endSetup();
        }
    }
}
