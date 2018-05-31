<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Aheadworks\Autorelated\Model\RuleStatusManager;

/**
 * Class ChangeStatus
 *
 * @package Aheadworks\Autorelated\Controller\Adminhtml\Rule
 */
class ChangeStatus extends \Magento\Backend\App\Action
{
    /**
     * @var RuleStatusManager
     */
    private $ruleStatusManager;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param RuleStatusManager $ruleStatusManager
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        RuleStatusManager $ruleStatusManager,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->ruleStatusManager = $ruleStatusManager;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Autorelated::rule';

    /**
     * Change Status action
     *
     * @return $this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                if ($this->ruleStatusManager->isRuleStatusLockedByWvtavFunctionality($id)) {
                    $noticeMessage = __(
                        'You need to enable "Who Viewed This Also Viewed" functionality in the settings first'
                    );
                    $this->messageManager->addNoticeMessage($noticeMessage);
                } else {
                    $this->ruleStatusManager->switchRuleStatus($id);
                    $this->messageManager->addSuccessMessage(__('Rule status was successfully changed'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
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
