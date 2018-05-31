<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Customoptionimage
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Customoptionimage\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->recreateImageTable($setup);
        }
    }
    public function recreateImageTable($setup)
    {
        $setup->startSetup();
        
        $bssImageTable = $setup->getTable('bss_catalog_product_option_type_image');
        $connection = $setup->getConnection();
        if ($connection->isTableExists($bssImageTable)) {
            $connection->dropIndex(
                $bssImageTable,
                'PRIMARY'
            );
            $connection->dropIndex(
                $bssImageTable,
                'INDEX'
            );
            $connection->dropColumn(
                $bssImageTable,
                'option_type_image_id'
            );
            $connection->modifyColumn(
                $bssImageTable,
                'option_type_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'unsigned' => true,
                    'comment' => 'Option type ID'
                ]
            );
            $connection->addColumn(
                $bssImageTable,
                'image_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'auto_increment' => true,
                    'primary' => true,
                    'index' => true,
                    'nullable' => false,
                    'comment' => 'image_id'
                ]
            );
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $bssImageTable,
                    'option_type_id',
                    $setup->getTable('catalog_product_option_type_value'),
                    'option_type_id'
                ),
                $bssImageTable,
                'option_type_id',
                $setup->getTable('catalog_product_option_type_value'),
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        }
        $setup->endSetup();
    }
}
