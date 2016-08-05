<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Rss
 * @copyright Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Mage_Rss_Block_Abstract extends Mage_Core_Block_Template
{
    protected function _getStoreId()
    {
        //store id is store view id
        $storeId =   (int) $this->getRequest()->getParam('store_id');
        if($storeId == null) {
           $storeId = Mage::app()->getStore()->getId();
        }
        return $storeId;
    }

    protected function _getCustomerGroupId()
    {
        //customer group id
        $custGroupID =   (int) $this->getRequest()->getParam('cid');
        if($custGroupID == null) {
            $custGroupID = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return $custGroupID;
    }
}
