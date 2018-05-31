<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Block\Adminhtml\Product\Edit\Tab\Inventory;

use Magento\Backend\Block\Widget\Form\Generic;

class PreOrder extends Generic
{
    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('amasty_preorder_fieldset', ['legend' => __('Pre-Order')]);

        $fieldset->addField(
            'amasty_preorder_note',
            'text',
            [
                'name' => 'product[amasty_preorder_note]',
                'label' => __('Pre-Order Note'),
                'title' => __('Pre-Order Note'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'amasty_preorder_cart_label',
            'text',
            [
                'name' => 'product[amasty_preorder_cart_label]',
                'label' => __('Pre-Order Cart Button'),
                'title' => __('Pre-Order Cart Button'),
                'required' => false
            ]
        );

        $product = $this->getProduct();
        if ($product !== null) {
            $form->setValues($product->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
