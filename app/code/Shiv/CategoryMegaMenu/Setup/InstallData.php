<?php
/**
 * @author		Sashas
 * @category    Sashas
 * @package     Sashas_CustomerAttribute
 * @copyright   Copyright (c) 2015 Sashas IT Support Inc. (http://www.extensions.sashas.org) 
 */
namespace Sashas\CustomerAttribute\Setup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    
    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }
 
    
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $customerSetup->addAttribute(Customer::ENTITY, 'is_core_password_reset', [
            'type' => 'int',
            'label' => 'Require password reset after data migrate?',
			'input' => 'select',
			'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required' => false,
			'default' => '1',
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
        ]);
        
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_core_password_reset')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],
        ]);
        
        $attribute->save();
          
      
    }
}