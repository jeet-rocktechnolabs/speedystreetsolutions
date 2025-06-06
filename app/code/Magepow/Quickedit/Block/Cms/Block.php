<?php

/**
 * @Author: nguyen
 * @Date:   2020-04-25 20:10:45
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-04-28 18:26:50
 */

namespace Magepow\Quickedit\Block\Cms;

use Magento\Cms\Ui\Component\Listing\Column\BlockActions;

class Block extends \Magento\Cms\Block\Block
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
        $blockId = $this->getBlockId();
        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block  = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);
            $id     = $block->getId();
            if ($id) {
                $routeParams = [
                    // 'id' => $id,
                    'block_id' => $id // not use $blockId
                ];
                $editUrl = $this->devHelper->getAdminUrl(BlockActions::URL_PATH_EDIT, $routeParams);
                $infoHints = [
                    [
                        'title' => __('Content > Blocks > Id is: %1', $id),
                        'url'   => $editUrl
                    ],
                    [
                        'title' => __('Content > Blocks > Identifier is: %1', $block->getIdentifier()),
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
}
