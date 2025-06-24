<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\App\Emulation;
use Magmodules\GoogleShopping\Api\Log\RepositoryInterface as LogRepository;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;
use Magmodules\GoogleShopping\Model\Feed as FeedModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FeedGenerate for generation of feed
 */
class FeedGenerate extends Command
{

    public const COMMAND_NAME = 'googleshopping:feed:generate';
    /**
     * @var FeedModel
     */
    private $feedModel;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var AppState
     */
    private $appState;
    /**
     * @var Emulation
     */
    private $appEmulation;
    /**
     * @var LogRepository
     */
    private $logger;

    /**
     * FeedGenerate constructor.
     *
     * @param FeedModel $feedModel
     * @param GeneralHelper $generalHelper
     * @param AppState $appState
     * @param Emulation $appEmulation
     * @param LogRepository $logger
     */
    public function __construct(
        FeedModel $feedModel,
        GeneralHelper $generalHelper,
        AppState $appState,
        Emulation $appEmulation,
        LogRepository $logger
    ) {
        $this->feedModel = $feedModel;
        $this->generalHelper = $generalHelper;
        $this->appState = $appState;
        $this->appEmulation = $appEmulation;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Generate GoogleShopping XML Feed');
        $this->addOption(
            'store-id',
            null,
            InputOption::VALUE_OPTIONAL,
            'Store ID of the export feed. If not specified all enabled stores will be exported'
        );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $storeId = $input->getOption('store-id');
        $this->appState->setAreaCode('frontend');

        if (empty($storeId) || !is_numeric($storeId)) {
            $output->writeln('<info>Start Generating feed for all stores</info>');
            $storeIds = $this->generalHelper->getEnabledArray('magmodules_googleshopping/generate/enable');
            foreach ($storeIds as $storeId) {
                $this->generateFeed($storeId, $output);
            }
        } else {
            $output->writeln('<info>Start Generating feed for Store ID ' . $storeId . '</info>');
            $this->generateFeed($storeId, $output);
        }
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param                 $storeId
     * @param OutputInterface $output
     */
    private function generateFeed($storeId, OutputInterface $output)
    {
        try {
            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
            $result = $this->feedModel->generateByStore($storeId, 'cli');
            $msg = sprintf(
                'Store ID %s: Generated feed with %s product in %s',
                $storeId,
                $result['qty'],
                $result['time']
            );
        } catch (\Exception $e) {
            $this->logger->addErrorLog('Generate', $e->getMessage());
            $msg = sprintf('Store ID %s: %s', $storeId, $e->getMessage());
        } finally {
            $this->appEmulation->stopEnvironmentEmulation();
        }

        $output->writeln($msg);
    }
}
