<?php

/**
 * Product:       Xtento_TrackingImport (2.1.9)
 * ID:            4wmf19Yp9HCIHo7KOs8fxcH61vi7Ff3lLVajZHUke48=
 * Packaged:      2017-06-14T12:47:31+00:00
 * Last Modified: 2017-04-27T20:01:45+00:00
 * File:          app/code/Xtento/TrackingImport/Console/Command/ConfigImportCommand.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\TrackingImport\Console\Command;

use Magento\Framework\App\State as AppState;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigImportCommand extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var \Xtento\TrackingImport\Helper\Tools
     */
    protected $toolsHelper;

    /**
     * ConfigImportCommand constructor.
     *
     * @param AppState $appState
     * @param \Xtento\TrackingImport\Helper\Tools $toolsHelper
     */
    public function __construct(
        AppState $appState,
        \Xtento\TrackingImport\Helper\Tools $toolsHelper
    ) {
        $this->appState = $appState;
        $this->toolsHelper = $toolsHelper;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('xtento:trackingimport:config:import')
            ->setDescription('Import "XTENTO tracking import module" configuration from JSON file (functionality in admin: Tracking Import > Tools)')
            ->setDefinition(
                [
                    new InputArgument(
                        'file',
                        InputArgument::REQUIRED,
                        'File to read settings from. Example: /tmp/settings.json'
                    ),
                    new InputOption(
                        'updateByName',
                        '-u',
                        InputOption::VALUE_NONE,
                        'Add this parameter to update existing profiles if the profile/source name matches'
                    )
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // intentionally left empty
        }

        $inputFile = $input->getArgument('file');
        $updateByName = $input->getOption('updateByName');

        // Counters
        $addedCounter = ['profiles' => 0, 'sources' => 0];
        $updatedCounter = ['profiles' => 0, 'sources' => 0];
        $errorMessage = "";

        // Load JSON settings
        $jsonData = file_get_contents($inputFile);
        if (empty($jsonData)) {
            $output->writeln(sprintf("<error>Could not read file %s or file is empty</error>", $inputFile));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if (!$this->toolsHelper->importSettingsFromJson($jsonData, $addedCounter, $updatedCounter, $updateByName, $errorMessage)) {
            $output->writeln(sprintf("<error>Error while importing settings: %s</error>", $errorMessage));
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->writeln(sprintf(__('<info>%1 profiles have been added, %2 profiles have been updated, %3 sources have been added, %4 sources have been updated.<info>', $addedCounter['profiles'], $updatedCounter['profiles'], $addedCounter['sources'], $updatedCounter['sources'])));
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
