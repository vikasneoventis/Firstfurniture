<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2017-02-02T15:34:43+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Controller/Adminhtml/Grid/Mass.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Controller\Adminhtml\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Handling mass actions
 *
 * @package Xtento\AdvancedOrderStatus\Controller\Adminhtml\Grid
 */
class Mass extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Xtento\AdvancedOrderStatus\Model\Processor
     */
    protected $orderProcessor;

    /**
     * Mass constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param \Xtento\AdvancedOrderStatus\Model\Processor $orderProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Xtento\AdvancedOrderStatus\Model\Processor $orderProcessor
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * Update selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->orderProcessor->processOrders($collection->getAllIds());
        $resultRedirect->setPath('sales/order');
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xtento_AdvancedOrderStatus::actions');
    }
}
