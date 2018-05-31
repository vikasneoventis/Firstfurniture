<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-31T15:05:01+00:00
 * File:          app/code/Xtento/TrackingImport/Setup/InstallSchema.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    // @codingStandardsIgnoreStart
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // @codingStandardsIgnoreEnd
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'xtento_trackingimport_source'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_trackingimport_source')
        )->addColumn(
            'source_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'hostname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Hostname'
        )->addColumn(
            'port',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['nullable' => true],
            'Port'
        )->addColumn(
            'username',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Username'
        )->addColumn(
            'password',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Password'
        )->addColumn(
            'timeout',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'default' => 15],
            'Timeout'
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Path'
        )->addColumn(
            'filename_pattern',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => '//'],
            'Filename Pattern'
        )->addColumn(
            'archive_path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Archive Path'
        )->addColumn(
            'delete_imported_files',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Delete imported files'
        )->addColumn(
            'ftp_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'FTP Server Type'
        )->addColumn(
            'ftp_pasv',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'FTP Use Passive Mode'
        )->addColumn(
            'custom_class',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Custom Class'
        )->addColumn(
            'custom_function',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Custom Function'
        )->addColumn(
            'last_result',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Last Result'
        )->addColumn(
            'last_result_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Last Result Message'
        )->addColumn(
            'last_modification',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Last Modification'
        )->setComment(
            'Xtento_TrackingImport Sources table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'xtento_trackingimport_log'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_trackingimport_log')
        )->addColumn(
            'log_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Profile ID'
        )->addColumn(
            'files',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Imported Files'
        )->addColumn(
            'source_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Source IDs'
        )->addColumn(
            'import_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            5,
            ['nullable' => false],
            'Import Type (ID)'
        )->addColumn(
            'import_event',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Import Event'
        )->addColumn(
            'records_imported',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            8,
            ['nullable' => false],
            'Records Imported'
        )->addColumn(
            'result',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            2,
            ['nullable' => false],
            'Import Result'
        )->addColumn(
            'result_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Import Result Message'
        )->addIndex(
            $installer->getIdxName(
                'xtento_trackingimport_log',
                ['profile_id', 'created_at'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['profile_id', 'created_at'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment(
            'Xtento_TrackingImport Log table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'xtento_trackingimport_profile'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtento_trackingimport_profile')
        )->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'entity',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Import Entity'
        )->addColumn(
            'processor',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Import Processor'
        )->addColumn(
            'enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Profile Enabled'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Profile Name'
        )->addColumn(
            'source_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Source IDs'
        )->addColumn(
            'last_execution',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Last Execution'
        )->addColumn(
            'last_modification',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Last Modification'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Conditions / Filters'
        )->addColumn(
            'cronjob_enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Cronjob import enabled'
        )->addColumn(
            'cronjob_frequency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Cronjob frequency'
        )->addColumn(
            'cronjob_custom_frequency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65535,
            ['nullable' => false],
            'Cronjob custom frequency expression'
        )->addColumn(
            'configuration',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            16777215,
            ['nullable' => false],
            'Configuration'
        )->setComment(
            'Xtento_TrackingImport Profile table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
