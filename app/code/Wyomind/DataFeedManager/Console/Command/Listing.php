<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * $ bin/magento help wyomind:dfm:list
 * Usage:
 * wyomind:dfm:list
 *
 * Options:
 * --help (-h)           Display this help message
 * --quiet (-q)          Do not output any message
 * --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * --version (-V)        Display this application version
 * --ansi                Force ANSI output
 * --no-ansi             Disable ANSI output
 * --no-interaction (-n) Do not ask any interactive question
 */
class Listing extends Command
{

    protected $_feedsCollectionFactory = null;
    protected $_state = null;
    protected $_dataHelper = null;

    public function __construct(
        \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory $feedsCollectionFactory,
        \Wyomind\DataFeedManager\Helper\Data $dataHelper,
        \Magento\Framework\App\State $state
    ) {
    
        $this->_state = $state;
        $this->_feedsCollectionFactory = $feedsCollectionFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:dfm:list')
                ->setDescription(__('Data Feed Manager : get list of available feeds'))
                ->setDefinition([]);
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
    

        try {
            $this->_state->setAreaCode('adminhtml');

            $collection = $this->_feedsCollectionFactory->create();
            foreach ($collection as $feed) {
                $row = sprintf(
                    "%-6d %-45s %-22s %s",
                    $feed->getId(),
                    $feed->getPath() . $feed->getName() . $this->_dataHelper->getExtFromType($feed->getType()),
                    $feed->getUpdatedAt(),
                    $feed->getStatus() ? __("Enabled") : __("Disabled")
                );
                $output->writeln($row);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;

        return $returnValue;
    }
}
