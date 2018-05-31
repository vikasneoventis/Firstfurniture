<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2016-05-07T11:59:57+00:00
 * File:          app/code/Xtento/TrackingImport/Controller/Adminhtml/Profile/Save.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Controller\Adminhtml\Profile;

class Save extends \Xtento\TrackingImport\Controller\Adminhtml\Profile
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\TrackingImport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\TrackingImport\Helper\Entity $entityHelper
     * @param \Xtento\TrackingImport\Model\ProfileFactory $profileFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\TrackingImport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\TrackingImport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\TrackingImport\Helper\Entity $entityHelper,
        \Xtento\TrackingImport\Model\ProfileFactory $profileFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $context,
            $moduleHelper,
            $cronHelper,
            $profileCollectionFactory,
            $registry,
            $escaper,
            $scopeConfig,
            $entityHelper,
            $profileFactory
        );
        $this->resource = $resource;
    }

    /**
     * Save profile
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        /** @var $postData \Zend\Stdlib\Parameters */
        if ($postData = $this->getRequest()->getPost()) {
            $postData = $postData->toArray();
            if (!isset($postData['name'])) {
                $this->messageManager->addErrorMessage(
                    __('Could not find any data to save in the POST request. POST request too long maybe?')
                );
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }
            $model = $this->profileFactory->create();
            if (isset($postData['rule']['conditions'])) {
                $postData['conditions'] = $postData['rule']['conditions'];
                unset($postData['rule']);
            }
            #var_dump($postData); die();
            $model->setData($postData);
            if ($model->getId()) {
                $profile = $model->load($model->getId());
                $this->registry->unregister('trackingimport_profile');
                $this->registry->register('trackingimport_profile', $profile);
                try {
                    $model->loadPost($postData);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('An error occurred while saving this import profile: %1', $e->getMessage())
                    );
                }
            }
            $model->setLastModification(time());

            if (!$model->getId()) {
                $model->setEnabled(1);
            }

            // Prepare mapping
            if (isset($postData['mapping'])) {
                $mapping = $this->prepareMappingForSave($postData['mapping']);
                if ($mapping !== false) {
                    $postData['mapping'] = $mapping;
                } else {
                    unset($postData['mapping']);
                }
            }
            // Prepare actions
            if (isset($postData['action'])) {
                $actions = $this->prepareMappingForSave($postData['action']);
                if ($actions !== false) {
                    $postData['action'] = $actions;
                } else {
                    unset($postData['action']);
                }
            }

            $skippedFields = ['form_key', 'page', 'limit', 'log_id'];
            $configurationToSave = [];
            $tableFields = $this->resource->getConnection()->describeTable(
                $this->resource->getTableName('xtento_trackingimport_profile')
            );
            foreach ($postData as $confKey => $confValue) {
                if (!isset($tableFields[$confKey]) && !in_array($confKey, $skippedFields) && !preg_match(
                        '/col_/',
                        $confKey
                    )
                ) {
                    if (is_array($confValue) && isset($confValue['from']) && isset($confValue['to'])) {
                        continue;
                    }
                    $configurationToSave[$confKey] = $confValue;
                }
            }
            $model->setConfiguration($configurationToSave);

            try {
                #echo "<pre>";
                #var_dump($model->getData()); die();
                $model->save();
                $this->_session->setFormData(false);
                $this->messageManager->addSuccessMessage(__('The import profile has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $model->getId(), 'active_tab' => $this->getRequest()->getParam('active_tab')]
                    );
                    return $resultRedirect;
                } else {
                    $resultRedirect->setPath('*/*');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while saving this import profile: %1', $e->getTraceAsString())
                );
            }

            $this->_session->setFormData($postData);
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        } else {
            $this->messageManager->addErrorMessage(
                __('Could not find any data to save in the POST request. POST request too long maybe?')
            );
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
    }

    protected function prepareMappingForSave($mapping)
    {
        if (is_array($mapping)) {
            if (!isset($mapping['__save_data']) && isset($mapping['__type'])) {
                // save_data was not set by our Javascript.. let's better load the fail-safe database configuration instead of risking losing the mapping
                return false;
            } else {
                unset($mapping['__empty']);
                unset($mapping['__type']);
                unset($mapping['__save_data']);
                foreach ($mapping as $id => $data) {
                    if (!isset($data['field'])) {
                        unset($mapping[$id]);
                        continue;
                    }
                    if ($data['field'] == '') {
                        unset($mapping[$id]);
                    }
                }
            }
        }
        return $mapping;
    }
}