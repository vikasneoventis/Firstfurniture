<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Controller\Adminhtml\Rule;

use Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing;
use Magento\Backend\App\Action\Context;
use Aheadworks\Autorelated\Api\RuleRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Delete
 *
 * @package Aheadworks\Autorelated\Controller\Adminhtml\Rule
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param RuleRepositoryInterface $ruleRepository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        RuleRepositoryInterface $ruleRepository,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Autorelated::rule';

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->ruleRepository->deleteById($id);
                $this->messageManager->addSuccess(__('Rule was successfully deleted'));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
                if (!$this->getRequest()->isAjax()) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }
            }
        }

        if ($this->getRequest()->isAjax()) {
            $jsonData = [];
            $type = $this->getRequest()->getParam('type');
            if ($type) {
                $this->_view->loadLayout(['default', 'autorelated_admin_rule_listing'], true, true, false);
                $listing = $this->_view->getLayout()->getBlock('container_listing_renderer');
                $jsonData['listing'] = $listing->render($type);
            }

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($jsonData);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
