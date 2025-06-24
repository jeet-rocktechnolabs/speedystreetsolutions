<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */

namespace Magetrend\AbandonedCart\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;

/**
 * Sender source
 *
 * @category MageTrend
 * @package  Magetend/AbandonedCart
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-abandoned-cart
 */
class StoreEmail extends \Magento\Customer\Model\Config\Source\Group
{
    public $scopeConfig;

    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($groupManagement, $converter);
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $emails = $this->scopeConfig->getValue(
            'trans_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $options = [];
        foreach ($emails as $key => $email) {
            $options[] = [
                'value' => $key,
                'label' => $email['name'].' <'.$email['email'].'>'

            ];
        }
        return $options;
    }

    public function toArray()
    {
        $data = [];
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $data[$option['value']] = $option['label'];
        }

        return $data;
    }
}
