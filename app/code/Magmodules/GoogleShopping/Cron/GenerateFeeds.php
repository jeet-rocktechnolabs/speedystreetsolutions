<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Cron;

use Magmodules\GoogleShopping\Api\Log\RepositoryInterface as LogRepository;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;
use Magmodules\GoogleShopping\Model\Feed as FeedModel;

class GenerateFeeds
{

    /**
     * @var FeedModel
     */
    private $feedModel;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var LogRepository
     */
    private $logger;

    /**
     * GenerateFeeds constructor.
     *
     * @param FeedModel $feedModel
     * @param GeneralHelper $generalHelper
     * @param LogRepository $logger
     */
    public function __construct(
        FeedModel $feedModel,
        GeneralHelper $generalHelper,
        LogRepository $logger
    ) {
        $this->feedModel = $feedModel;
        $this->generalHelper = $generalHelper;
        $this->logger = $logger;
    }

    /**
     * Execute: Run all GoogleShopping Feed generation.
     */
    public function execute()
    {
        try {
            $cronEnabled = $this->generalHelper->getCronEnabled();
            if ($cronEnabled) {
                $this->feedModel->generateAll();
            }
        } catch (\Exception $e) {
            $this->logger->addErrorLog('Cron', $e->getMessage());
        }

        return $this;
    }
}
