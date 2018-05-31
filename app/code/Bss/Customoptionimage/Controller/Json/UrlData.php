<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Customoptionimage
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Customoptionimage\Controller\json;
 
use Magento\Framework\App\Action\Context;
 
class UrlData extends \Magento\Framework\App\Action\Action
{
    private $coiData;

    private $resultJsonFactory;

    public function __construct(
        Context $context,
        \Bss\Customoptionimage\Helper\Data $coiData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->coiData = $coiData;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $productId = $this->getRequest()->getParam('productId');
            $result = $this->coiData->getUrlData($productId);
            return $resultJson->setData($result);
        } else {
            return $resultJson->setData(null);
        }
    }
}
