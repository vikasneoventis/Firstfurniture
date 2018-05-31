<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-03-13T19:37:14+00:00
 * File:          app/code/Xtento/TrackingImport/Controller/Adminhtml/Source/Edit.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Controller\Adminhtml\Source;

class Edit extends \Xtento\TrackingImport\Controller\Adminhtml\Source
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $healthCheck = $this->healthCheck();
        if ($healthCheck !== true) {
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath($healthCheck);
        }

        $id = $this->getRequest()->getParam('id');
        $model = $this->sourceFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This source no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                return $resultRedirect->setPath('*/*/');
            }
            if ($model->getType() == \Xtento\TrackingImport\Model\Source::TYPE_LOCAL) {
                if (!$model->getPath()) {
                    $model->setPath('./var/trackingimport/');
                    $model->setArchivePath('./var/trackingimport/archive/');
                }
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->registry->unregister('trackingimport_source');
        $this->registry->register('trackingimport_source', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $this->updateMenu($resultPage);

        if ($this->registry->registry('trackingimport_source') && $this->registry->registry(
                'trackingimport_source'
            )->getId()
        ) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Source'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Source'));
        }

        return $resultPage;
    }
}