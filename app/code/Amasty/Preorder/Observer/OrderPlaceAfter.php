<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


namespace Amasty\Preorder\Observer;

use Magento\Framework\Event\Observer;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    private $dataHelper;

    public function __construct(
        \Amasty\Preorder\Helper\Data $helper
    ) {
        $this->dataHelper = $helper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->dataHelper->preordersEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        $this->dataHelper->checkNewOrder($order);
    }
}
