<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class InstallSchema implements InstallSchemaInterface
{


    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_preorder_order_preorder'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'is_preorder',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default'=>'0']
            )
            ->addColumn(
                'warning',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default'=>null]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_preorder_order_preorder',
                    'order_id',
                    'sales_order',
                    'entity_id'
                ),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);


        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_preorder_order_item_preorder'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'is_preorder',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default'=>'0']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_preorder_order_item_preorder',
                    'order_item_id',
                    'sales_order_item',
                    'item_id'
                ),
                'order_item_id',
                $installer->getTable('sales_order_item'),
                'item_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}