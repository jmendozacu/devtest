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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newslettersend Adminhtml newsletter queue controller
 *
 * @category   M0
 * @package    M0_NewsletterSend
 * @author      magent0 <magent0.com>
 */
class M0_Newslettersend_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Queue list action
     */
    public function indexAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
	        $countOfQueue  = 3;
	        $countOfSubscritions = 20;
	
	        $collection = Mage::getModel('newsletter/queue')->getCollection()
	            ->setPageSize($countOfQueue)
	            ->setCurPage(1)
	            ->addOnlyForSendingFilter()
	            ->load();
	
	        $collection->walk('sendPerSubscriber', array($countOfSubscritions));	                
        
            $this->_forward('grid');
            return;
            
        }
    }

    /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/newsletter_queue_grid')->toHtml());
    }
}

