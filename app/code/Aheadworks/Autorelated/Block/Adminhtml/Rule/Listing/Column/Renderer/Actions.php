<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Class Action
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml\Rule\Listing\Column\Renderer
 */
class Actions extends AbstractRenderer
{
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Autorelated::listing/column/actions.phtml';

    /**
     * @var array
     */
    private $buttons;

    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $this->buttons = [];
        foreach ($this->getColumn()->getActionsButton() as $buttonName => $button) {
            $this->prepareButton($buttonName, $button, $row);
        }

        return $this->toHtml();
    }

    /**
     * Return action buttons data
     *
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * Prepare and set action buttons data
     *
     * @param string $buttonName
     * @param array $button
     * @param array $row
     * @return $this
     */
    private function prepareButton($buttonName, $button, $row)
    {
        $params = [
            '_current' => true,
            $this->getColumn()->getPrimaryFieldName() => $row->getData($this->getColumn()->getPrimaryFieldName()),
            $this->getColumn()->getTypeFieldName() => $row->getData($this->getColumn()->getTypeFieldName())
        ];

        $statusFieldName = isset($button['status_field_name']) ? $button['status_field_name'] : '';
        $this->buttons[] = [
            'class' => $button['css_class'],
            'url' => $this->getUrl($button['url'], $params),
            'listingType' => $row->getData($this->getColumn()->getTypeFieldName()),
            'confirmation' => isset($button['confirm_message']) ? $button['confirm_message'] : '',
            'content' => $this->getButtonContent(
                $buttonName,
                ['status' => $row->getData($statusFieldName)]
            )
        ];

        return $this;
    }

    /**
     * Return html content for action button
     *
     * @param string $name
     * @param array $param
     * @return \Magento\Framework\Phrase|string
     */
    private function getButtonContent($name, $param = [])
    {
        switch ($name) {
            case 'status':
                return (int)$param['status'] ? __('Disable') : __('Enable');
            case 'delete':
                return '<span class="action-delete-icon"></span>';
        }

        return '';
    }
}
