<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * $ bin/magento help wyomind:msu:run
 * Usage:
 * wyomind:msu:run [profile_id1] ... [profile_idN]
 *
 * Arguments:
 * profile_id            Space-separated list of import profiles (run all profiles if empty)
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
class Generate extends Command
{

    const FEED_ID_ARG = "feed_id";

    protected $_feedsCollectionFactory = null;
    protected $_state = null;

    public function __construct(
        \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory $feedsCollectionFactory,
        \Magento\Framework\App\State $state
    ) {
    
        $this->_state = $state;
        $this->_feedsCollectionFactory = $feedsCollectionFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:dfm:generate')
                ->setDescription(__('Data Feed Manager : generate data feeds'))
                ->setDefinition([
                    new InputArgument(
                        self::FEED_ID_ARG,
                        InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                        __('Space-separated list of data feed (generate all feeds if empty)')
                    )
                ]);
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
    

        try {
            $this->_state->setAreaCode('adminhtml');
            $feedsIds = $input->getArgument(self::FEED_ID_ARG);
            $collection = $this->_feedsCollectionFactory->create()->getList($feedsIds);
            $first = true;
            foreach ($collection as $feed) {
                $feed->isCron = true;
                if ($first) {
                    $feed->loadCustomFunctions();
                    $first = false;
                }
                $output->write("Generating feed #" . $feed->getId());
                if (!$feed->getStatus()) {
                    $output->writeln("  => the data feed must be enabled to be generated");
                } else {
                    $feed->generateFile();
                    $output->writeln("  => generated");
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;

        return $returnValue;
    }
}
