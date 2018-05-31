<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Controller\Adminhtml\Functions;

/**
 * Save action
 */
class Save extends \Wyomind\DataFeedManager\Controller\Adminhtml\Functions\AbstractFunctions
{

    /**
     * Execute action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $return = $this->_resultRedirectFactory->create()->setPath('datafeedmanager/functions/index');

        if ($data) {
            $model = $this->_objectManager->create($this->model);
            $id = $data['id'];
            if ($id) {
                $model->setId($id);
            }

            foreach ($data as $index => $value) {
                $model->setData($index, $value);
            }

            if (!$this->_validatePostData($data)) {
                $return = $this->_resultRedirectFactory->create()->setPath('datafeedmanager/functions/edit', ['id' => $model->getId(), '_current' => true]);
            } else {
                try {
                    if ($this->_attributesHelper->execPhp("?><?php function(){" . substr(trim($this->getRequest()->getParam('script')), 5, -2) . "} ?>") === false) {
                        $this->_coreRegistry->register('script', $data['script']);
                        $this->messageManager->addError(__("Invalid function declaration") . "<br>" . error_get_last()["message"]);
                        $return = $this->resultForwardFactory->create()->forward("edit");
                    } else {
                        $this->messageManager->addSuccess(__('The function has been saved.'));
                        $model->save();
                        $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                        $return = $this->_resultRedirectFactory->create()->setPath('datafeedmanager/functions/edit', ['id' => $model->getId(), '_current' => true]);
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Unable to save the profile.') . '<br/><br/>' . $e->getMessage());
                    $return = $this->_resultRedirectFactory->create()->setPath('datafeedmanager/functions/edit', ['id' => $model->getId(), '_current' => true]);
                }
            }
        }

        return $return;
    }
    
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }

            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                $data['custom_layout_update_xml']
            )
            ) {
                $errorNo = false;
            }

            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }

        return $errorNo;
    }
}
