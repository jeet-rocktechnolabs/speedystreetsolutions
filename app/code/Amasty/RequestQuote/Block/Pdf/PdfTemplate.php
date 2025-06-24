<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Request a Quote Base for Magento 2
 */

namespace Amasty\RequestQuote\Block\Pdf;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Theme\Block\Html\Header\Logo;

class PdfTemplate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_RequestQuote::system/config/pdf_template.phtml';

    /**
     * @var Logo
     */
    private $logo;

    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        Template\Context $context,
        Logo $logo,
        Url $urlBuilder,
        Repository $assetRepo,
        Emulation $appEmulation,
        State $appState,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->logo = $logo;
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
        $this->appEmulation = $appEmulation;
        $this->appState = $appState;
    }

    public function getLogo(bool $useLogoUrl = true): string
    {
        if ($useLogoUrl) {
            $logoSrc = $this->getEmulatedResult($this->logo, 'getLogoSrc');
            if ($logoSrc) {
                return $logoSrc;
            }
        }

        $asset = $this->assetRepo->createAsset('images/logo.svg', ['area' => 'frontend']);
        return 'data:image/' . $asset->getContentType() . ';base64,' . base64_encode($asset->getContent());
    }

    public function getCustomerServiceUrl(): string
    {
        return $this->getEmulatedResult($this->urlBuilder, 'getUrl', ['customer-service']);
    }

    public function getContactUsUrl(): string
    {
        return $this->getEmulatedResult($this->urlBuilder, 'getUrl', ['contact']);
    }

    /**
     * @param $object
     * @param string $method
     * @param array $params
     * @return mixed
     */
    private function getEmulatedResult($object, string $method, array $params = [])
    {
        $this->appEmulation->startEnvironmentEmulation(Store::DEFAULT_STORE_ID);
        $url = $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$object, $method],
            $params
        );
        $this->appEmulation->stopEnvironmentEmulation();

        return $url;
    }
}
