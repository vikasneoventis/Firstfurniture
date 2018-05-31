<?php

/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @version 10.0.0
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable('datafeedmanager_feeds')); // drop if exists
        $installer->getConnection()->dropTable($installer->getTable('datafeedmanager_functions'));
        $installer->getConnection()->dropTable($installer->getTable('datafeedmanager_variables'));

        $datafeedmanagerTable = $installer->getConnection()
                ->newTable($installer->getTable('datafeedmanager_feeds'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [ 'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true ],
                    'Data Feed ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [ 'nullable' => true, 'default' => '' ],
                    'Data Feed Name'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    3,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                    'Type of data feed'
                )
                ->addColumn(
                    'path',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [ 'nullable' => true, 'default' => '' ],
                    'Data Feed File path'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                    'Data feed status (enable/disable)'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Data Feed Last Update Time'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                    'Data Feed Associated Store ID'
                )
                ->addColumn(
                    'product_pattern',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed XML Item Pattern'
                )
                ->addColumn(
                    'category_filter',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
                    'Data Feed Categories Inclusion Type'
                )
                ->addColumn(
                    'categories',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                    [],
                    'Data Feed Categories Selection'
                )
                ->addColumn(
                    'type_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [],
                    'Data Feed Product Types Selection'
                )
                ->addColumn(
                    'category_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Data Feed Categories Filter (product/parent)'
                )
                ->addColumn(
                    'visibilities',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [],
                    'Data Feed Product Visibilities Selection'
                )
                ->addColumn(
                    'attribute_sets',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['default'=>'*'],
                    'Data Feed Attribute Sets Selection'
                )
                ->addColumn(
                    'attributes',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Advanced Filters'
                )
                ->addColumn(
                    'cron_expr',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    900,
                    [],
                    'Data Feed Schedule Task'
                )
                ->addColumn(
                    'taxonomy',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['default' => '[default] en_US.txt'],
                    'Data Feed Taxonomies File'
                )
                ->addColumn(
                    'include_header',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Data Feed Categories Include Header ?'
                )
                ->addColumn(
                    'header',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Header'
                )
                ->addColumn(
                    'footer',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Footer'
                )
                ->addColumn(
                    'field_separator',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    3,
                    [],
                    'Data Feed Field Separator'
                )
                ->addColumn(
                    'field_protector',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    3,
                    [],
                    'Data Feed Field Protector'
                )
                ->addColumn(
                    'field_escape',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    3,
                    [],
                    'Data Feed Escape Char'
                )
                ->addColumn(
                    'encoding',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    40,
                    ["defaul"=>'UTF-8'],
                    'Data Feed Encoding'
                )
                ->addColumn(
                    'enclose_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ["default"=>'1'],
                    'Data Feed Eclose Data ?'
                )
                ->addColumn(
                    'clean_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ["default"=>'1'],
                    'Data Feed Clean Data ?'
                )
                ->addColumn(
                    'extra_header',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Extra Header'
                )
                ->addColumn(
                    'extra_footer',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Data Feed Extra Footer'
                )
                ->addColumn(
                    'dateformat',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ["default"=>"{f}"],
                    'Data Feed Extra Header'
                )
                ->addColumn(
                    'ftp_enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ["default"=>"0"],
                    'Data Feed Enabled Ftp'
                )
                ->addColumn(
                    'use_sftp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ["default"=>"0"],
                    'Data Feed Use Sftp ?'
                )
                ->addColumn(
                    'ftp_host',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    [],
                    'Data Feed Ftp Host'
                )
                ->addColumn(
                    'ftp_port',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    5,
                    [],
                    'Data Feed Ftp Port'
                )
                ->addColumn(
                    'ftp_password',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    [],
                    'Data Feed Ftp Password'
                )
                ->addColumn(
                    'ftp_login',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    [],
                    'Data Feed Ftp Login'
                )
                ->addColumn(
                    'ftp_active',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ["default"=>"0"],
                    'Data Feed Ftp Active Mode'
                )
                ->addColumn(
                    'ftp_dir',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    [],
                    'Data Feed Ftp Dir'
                )
                ->setComment('Data Feed Manager Data Feeds Table');

        $installer->getConnection()->createTable($datafeedmanagerTable);

        $datafeedmanagerFunctionsTable = $installer->getConnection()
                ->newTable($installer->getTable('datafeedmanager_functions'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [ 'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true ],
                    'Custom Function ID'
                )
                ->addColumn(
                    'script',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Custom Function Script'
                )
                ->setComment('Data Feed Manager Custom Functions Table');

        $installer->getConnection()->createTable($datafeedmanagerFunctionsTable);
        
        $datafeedmanagerVariablesTable = $installer->getConnection()
                ->newTable($installer->getTable('datafeedmanager_variables'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [ 'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true ],
                    'Data Feed ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [],
                    'Custom Variable Name'
                )
                ->addColumn(
                    'comment',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Custom Variable Comment'
                )
                ->addColumn(
                    'script',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Custom Variable Script'
                )
                ->setComment('Data Feed Manager Custom Variables Table');

        $installer->getConnection()->createTable($datafeedmanagerVariablesTable);
        
        $installer->endSetup();
    }
}
