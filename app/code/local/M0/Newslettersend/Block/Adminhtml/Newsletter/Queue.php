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
 * @category    M0
 * @package     M0_NewsletterSend
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml queue grid block.
 *
 * @category   M0
 * @package    M0_NewsletterSend
 * @author      magent0 <magent0.com>
 */
class M0_NewsletterSend_Block_Adminhtml_Newsletter_Queue extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {

        $this->setTemplate('newslettersend/queue/list.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/newsletter_queue_grid', 'newsletter.queue.grid'));
        return parent::_beforeToHtml();
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('sendButton',
            $this->getLayout()->createBlock('adminhtml/widget_button','send.button')
                ->setData(
                    array(
                        'label' => Mage::helper('newsletter')->__('Send Newsletter'),
                        'onclick' => 'queueController.sendNewsletter();'
                    )
                )
        );

        return parent::_prepareLayout();
    }

    public function getSendButtonHtml()
    {
        return $this->getChildHtml('sendButton');
    }

    public function getShowButtons()
    {
        return  Mage::getResourceSingleton('newsletter/queue_collection')->getSize() > 0;
    }    

}
