<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Edit\Tab;
 
class Cron extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
 
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('data_feed');
 
        $form = $this->_formFactory->create();
 
        $form->setHtmlIdPrefix('');
 
        $form->setValues($model->getData());
        $this->setForm($form);
        
        
        $this->setTemplate('edit/cron.phtml');
 
        return parent::_prepareForm();
    }
 
 
    public function getDFMCronExpr()
    {
        $model = $this->_coreRegistry->registry('data_feed');
        return $model->getCronExpr();
    }
 
    public function getTabLabel()
    {
        return __('Cron schedule');
    }
 
    public function getTabTitle()
    {
        return __('Cron schedule');
    }
 
    public function canShowTab()
    {
        return true;
    }
 
    public function isHidden()
    {
        return false;
    }
}
