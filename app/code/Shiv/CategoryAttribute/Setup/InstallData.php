<?php
/**
 * @author		Sashas
 * @category    Sashas
 * @package     Sashas_CustomerAttribute
 * @copyright   Copyright (c) 2015 Sashas IT Support Inc. (http://www.extensions.sashas.org) 
 */
namespace Shiv\CategoryAttribute\Setup;

/**
 * @codeCoverageIgnore
 */
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
 
    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
 
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $categorySetup->removeAttribute(
        \Magento\Catalog\Model\Category::ENTITY, 'is_top_category' );
        $categorySetup->addAttribute(
        \Magento\Catalog\Model\Category::ENTITY, 'is_top_category', [
					'type' => 'int',
					'label' => 'Top Category?',
					'input' => 'select',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'required' => false,
					'sort_order' => 100,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'group' => 'General Information',
			   ]
		);
		$installer->endSetup();
    }

}