<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Inventory;

class PreOrder extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeAction,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $productFactory, $attributeAction, $data);
        $this->attributeFactory = $attributeFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('amasty_preorder_fieldset', ['legend' => __('Pre-Order')]);

        $preOrderAttributes = $this->getAttributes();
        /**
         * Initialize product object as form property
         * for using it in elements generation
         */
        $form->setDataObject($this->_productFactory->create());
        $this->_setFieldset($preOrderAttributes, $fieldset, []);
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }

    /**
     * Retrieve attributes for product mass update
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes()
    {
        // entity type model
        $type = $this->eavConfig->getEntityType(
            \Magento\Catalog\Model\Product::ENTITY
        );

        $preOrderAttributes = $this->attributeFactory->create()
            ->getCollection();

        $objectsModel = $type->getAttributeModel();
        if ($objectsModel) {
            // set catalog entity type model (methods isScopeGlobal etc.)
            $preOrderAttributes->setModel($objectsModel);
        }

        $preOrderAttributes
            ->setEntityTypeFilter($type)
            ->addFieldToFilter('attribute_code', ['in' => ['amasty_preorder_note', 'amasty_preorder_cart_label']])
            ->getItems();

        foreach ($preOrderAttributes as $preOrderAttribute) {
            $preOrderAttribute->setIsVisible(true);
            $preOrderAttribute->setFrontendInput('text');
        }

        return $preOrderAttributes;
    }
}
