<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Autorelated\Block\Adminhtml;

/**
 * Class Rule
 *
 * @package Aheadworks\Autorelated\Block\Adminhtml
 */
class Rule extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Autorelated::rule.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addButton(
            'add',
            [
                'id' => 'create_new_rule',
                'label' => __('Create New Rule'),
                'class' => 'add primary',
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/new') . '\')',
            ]
        );
    }
}
