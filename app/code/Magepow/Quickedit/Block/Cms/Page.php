<?php

/**
 * @Author: nguyen
 * @Date:   2020-04-25 20:10:45
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-04-28 18:26:57
 */

namespace Magepow\Quickedit\Block\Cms;

use Magento\Cms\Ui\Component\Listing\Column\PageActions;

class Page extends \Magento\Cms\Block\Page
{
    /**
     * @var \Magepow\Quickedit\Helper\DevHelper
     */
    protected $devHelper;

    protected function _construct()
    {
        $this->devHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magepow\Quickedit\Helper\DevHelper::class
        );

        parent::_construct();
    }

    public function getQuickedit()
    {
        $page   = $this->getPage();
        if ($page) {
            $pagId  = $page->getId();
            $routeParams = [
                // 'id' => $pagId,
                'page_id' => $pagId
            ];
            $editUrl = $this->devHelper->getAdminUrl(PageActions::CMS_URL_PATH_EDIT, $routeParams);
            $infoHints = [
                [
                    'title' => __('Content > Pages > Id is: %1', $pagId),
                    'url'   => $editUrl
                ],
                [
                    'title' => __('Content >Pages > Identifier is: %1', $page->getIdentifier()),
                    'url'   => $editUrl
                ],
                [
                    'title' => __('Edit'),
                    'url'   => $editUrl
                ]
            ];
            
            return $infoHints;
        }          
    }
}
