<?php
/**
 * @copyright Copyright (c) 2016 www.magebuzz.com
 */

namespace Magebuzz\Timecountdown\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Backend system config datetime field renderer
 */
class Datetime extends \Magento\Config\Block\System\Config\Form\Field\Datetime
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        parent::__construct($context, $dateTimeFormatter, $data);
    }

//    protected function _getElementHtml(AbstractElement $element)
//    {
//        return '<input type="datetime-local" />';
//        sprintf(
//            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
//            $element->getHtmlId(),
//            $element->getHtmlId(),
//            $element->getLabel()
//        );
//    }
}
