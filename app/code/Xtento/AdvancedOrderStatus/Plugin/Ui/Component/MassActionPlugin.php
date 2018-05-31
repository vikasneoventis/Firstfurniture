<?php

/**
 * Product:       Xtento_AdvancedOrderStatus (2.0.8)
 * ID:            VWr/9REjZDQhBVdZRTowyNozwhIes3xEpdPbOm7d5OI=
 * Packaged:      2017-06-14T12:06:11+00:00
 * Last Modified: 2017-02-02T15:37:41+00:00
 * File:          app/code/Xtento/AdvancedOrderStatus/Plugin/Ui/Component/MassActionPlugin.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\AdvancedOrderStatus\Plugin\Ui\Component;

use Magento\Ui\Component\MassAction;

class MassActionPlugin
{
    /**
     * @var \Xtento\AdvancedOrderStatus\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $adminhtmlData = null;

    /**
     * @var \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses
     */
    protected $orderStatusSource;

    /**
     * @param \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses $orderStatusSource
     */
    public function __construct(
        \Xtento\AdvancedOrderStatus\Helper\Module $moduleHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses $orderStatusSource
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->request = $request;
        $this->scopeConfig = $config;
        $this->registry = $registry;
        $this->authorization = $authorization;
        $this->adminhtmlData = $adminhtmlData;
        $this->orderStatusSource = $orderStatusSource;
    }

    /**
     *
     *
     * @param MassAction $subject
     * @param string $interceptedOutput
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepare(MassAction $subject, $interceptedOutput)
    {
        if ($subject->getContext()->getNamespace() !== 'sales_order_grid') {
            return;
        }
        if (!$this->moduleHelper->isModuleEnabled()) {
            return;
        }
        if ($this->registry->registry('xtDisabled') !== false) {
            return;
        }

        $orderStatuses = $this->orderStatusSource->toOptionArray();
        array_shift($orderStatuses);
        $visibleOrderStatuses = explode(
            ",",
            $this->scopeConfig->getValue('advancedorderstatus/general/visible_order_statuses')
        );
        if (!empty($visibleOrderStatuses)) {
            if (isset($visibleOrderStatuses[0]) && $visibleOrderStatuses[0] === '') {
                # All statuses
            } else {
                foreach ($orderStatuses as $key => $orderStatus) {
                    if (!in_array($orderStatus['value'], $visibleOrderStatuses)) {
                        unset($orderStatuses[$key]);
                    }
                }
            }
        }

        $config = $subject->getData('config');

        if (!isset($config['component']) || strstr($config['component'], 'tree') === false) {
            // Temporary until added to core to support multi-level selects
            $config['component'] = 'Magento_Ui/js/grid/tree-massactions';
        }

        if ($this->authorization->isAllowed('Xtento_AdvancedOrderStatus::changestatus')) {
            $subActions = [];
            foreach ($orderStatuses as $orderStatus) {
                $subActions[] = [
                    'type' => $orderStatus['value'],
                    'label' => __('%1', $orderStatus['label']),
                    'url' => $this->adminhtmlData->getUrl(
                        'advancedorderstatus/grid/mass',
                        [
                            'new_order_status' => $orderStatus['value'],
                            'namespace' => $subject->getContext()->getNamespace()
                        ]
                    ),
                    'callback' => [
                        'provider' => 'sales_order_grid.sales_order_grid.advancedOrderStatusGrid',
                        'target' => 'bulkActionCallback'
                    ]
                ];
            }

            $config['actions'][] = [
                'type' => 'change_orderstatus',
                'label' => __('Change order status'),
                'actions' => $subActions
            ];
        }

        /*if ($this->authorization->isAllowed('Xtento_AdvancedOrderStatus::changestatus')) {
            foreach ($orderStatuses as $orderStatus) {
                $config['actions'][] = [
                    'type' => $orderStatus['value'],
                    'label' => __('Change order status to \'%1\'', $orderStatus['label']),
                    'url' => $this->adminhtmlData->getUrl(
                        'advancedorderstatus/grid/mass',
                        [
                            'new_order_status' => $orderStatus['value']
                        ]
                    )
                ];
            }
        }*/

        #print_r($config); die();

        $subject->setData('config', $config);
    }
}
