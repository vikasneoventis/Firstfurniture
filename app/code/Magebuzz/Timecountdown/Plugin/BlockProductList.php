<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Timecountdown\Plugin;

class BlockProductList
{
    const XML_PATH_TIMER_CATEGORY_ENABLED = 'timecountdown/general/show_in_category_page';
    const XML_PATH_TIMER_STYLE = 'timecountdown/general/style_select';

    protected $scopeConfig;
    protected $_layout;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutInterface $layout
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_layout = $layout;
    }

    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    )
    {
        $result = $proceed($product);
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_TIMER_CATEGORY_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isPriceCountdown = $this->_layout->createBlock('Magebuzz\Timecountdown\Block\Timecountdown')->isPriceCountdown($product);
        if ($isEnabled && $isPriceCountdown) {
            $style = $this->scopeConfig->getValue(self::XML_PATH_TIMER_STYLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $toDate = $product->getSpecialToDate() ? strtotime($product->getSpecialToDate()) : '';
            $result .= '
            <div class="mb-timecountdown-container timer-category timer-'.$style.'" data-todate="' . $toDate . '" data-prd_id="' . $product->getId() . '">
                <div class="timer-heading">PRICE COUNTDOWN</div>
                <div class="timer-countbox" id="price-countdown-'.$product->getId().'"></div>
            </div>
            ';
        }
        return $result;
    }
}
