<?php

/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Timecountdown\Block;

class Carousel extends \Magento\Catalog\Block\Product\AbstractProduct {
    const XML_PATH_TIMER_STYLE = 'timecountdown/general/style_select';

    protected $scopeConfig;
    protected $_productCollectionFactory;
    protected $urlHelper;
    protected $_date;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
        \Magento\Framework\Url\Helper\Data $urlHelper, 
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->urlHelper = $urlHelper;
        $this->_date = $date;
        parent::__construct($context, $data);
    }

    /**
     * get banner collection of slider.
     *
     * @return \Magebuzz\Bannermanager\Model\ResourceModel\Banner\Collection
     */
    public function getProductCollection() {
        $nowTime = date('Y-m-d H:i:s', time());
        $collection = $this->_productCollectionFactory->create()
                ->addAttributeToFilter('is_show_countdown', ['=' => 1])
                ->addAttributeToFilter('is_show_homepage', ['=' => 1])
                ->setOrder('sort_order', 'ASC');

        return $collection;
    }

    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product) {
        $url = $this->getAddToCartUrl($product);

        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
    
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $result = parent::getProductPrice($product);
        if ($this->getScopeConfig('timecountdown/general/display_in_one') == '0') {
            $style = $this->getStyle();
            $toDate = $product->getSpecialToDate() ? strtotime($product->getSpecialToDate()) : '';
            $result .= '
            <div class="mb-timecountdown-container timer-homepage timer-'.$style.'" data-todate="' . $toDate . '" data-prd_id="' . $product->getId() . '">
                <div class="timer-heading">PRICE COUNTDOWN</div>
                <div class="timer-countbox" id="price-countdown-'.$product->getId().'"></div>
            </div>
            ';
        }
        return $result;
    }

    public function getScopeConfig($path) {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getStyle() {
        return $this->getScopeConfig(self::XML_PATH_TIMER_STYLE);
    }

    public function isPriceCountdownInOne()
    { 
        $currentDate = strtotime($this->_date->gmtDate());
        $todate = $this->getScopeConfig('timecountdown/general/todate');
        $todate = strtotime($todate) ? (strtotime($todate)-$this->_date->getGmtOffset()) : 0;
        $fromdate = $this->getScopeConfig('timecountdown/general/fromdate');
        $fromdate = strtotime($fromdate) ? (strtotime($fromdate)-$this->_date->getGmtOffset()) : 0;
        if ($todate && $todate >= $currentDate && $fromdate <= $currentDate) {
            return true;
        }
        return false;
    }
    
    public function isPriceCountdownHomepage($product)
    { 
        if ($product->getSpecialPrice() && $product->getIsShowCountdown()) {
            $isDisplayInOne = $this->getScopeConfig('timecountdown/general/display_in_one');
            if ($isDisplayInOne == '1') {
                if ($this->isPriceCountdownInOne()) {
                    return true;
                }
            } else {
                $currentDate = strtotime($this->_date->gmtDate());
                $todate = $product->getSpecialToDate() ? (strtotime($product->getSpecialToDate())-$this->_date->getGmtOffset()) : 0;
                $fromdate = $product->getSpecialFromDate() ? (strtotime($product->getSpecialFromDate())-$this->_date->getGmtOffset()) : 0;
                if ($todate && $todate >= $currentDate && $fromdate <= $currentDate) {
                    return true;
                }
            }
        }
        return false;
    }
}
