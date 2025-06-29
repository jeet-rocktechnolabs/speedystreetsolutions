<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Utp
 * @package    PaymentGateway
 * @copyright  Copyright (c) 2017 Utp
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Utp\PaymentGateway\Model\Resource;

class Trans extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
	 * Model Initialization
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('utp_trans', 'id');
	}
}
