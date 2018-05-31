<?php
namespace Shiv\Trackingonefeed\Block;
/*
 * Turiknox_Webgains

 * @category   Turiknox
 * @package    Turiknox_Webgains
 * @copyright  Copyright (c) 2017 Turiknox
 * @license    https://github.com/Turiknox/magento2-webgains-tracking/blob/master/LICENSE.md
 * @version    1.0.0
 */
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;


class Trackingonefeed extends Template
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Resolver
     */
    protected $_locale;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /*
     * Webgains XML Enable Path
     */
    const XML_PATH_OF_ENABLE = 'onefeed/general/enable';

    /**
     * Webgains Program ID
     */
    const XML_PATH_OF_PROGRAM_ID = 'onefeed/general/ss_id';

    
    /**
     * Webgains Version
     */
    const OF_VERSION = '1.2';

    /**
     * Webgains constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Resolver $locale
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Resolver $locale,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_locale = $locale;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Check if the module is enabled in admin
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_OF_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Webgains Program ID
     *
     * @return int
     */
    public function getOnefeedSsId()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_OF_PROGRAM_ID, ScopeInterface::SCOPE_STORE);
    }
    
  
    /**
     * Set order
     */
    public function setOrder()
    {
        $this->_order = $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->setOrder();
        }
        return $this->_order;
    }

    /**
     * Get the order ID
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();

    }

    /**
     * Get the order total
     */
    public function getGrandTotal()
    {
        return number_format($this->_order->getGrandTotal(), 2, '.' , '');
    }
    
    /**
     * Get Webgains Version
     *
     * @return string
     */
    public function getOnefeedVersion()
    {
        return self::OF_VERSION;
    }

   
    
}