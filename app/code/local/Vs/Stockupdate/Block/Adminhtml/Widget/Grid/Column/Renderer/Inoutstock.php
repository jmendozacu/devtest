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
 * @category   Mage
 * @package    TBT_MassRelater
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grid column widget for rendering action grid cells
 *
 * @category   Mage
 * @package    
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Vs_Stockupdate_Block_Adminhtml_Widget_Grid_Column_Renderer_Inoutstock extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    /**
     * Prepares action data for html render
     *
     * @param array $action
     * @param string $actionCaption
     * @param Varien_Object $row
     * @return Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
     */
    public function render(Varien_Object $row)
    {
        
        $roleStoreId = Mage::getSingleton('adminhtml/session')->getRoleStoreId();

        $sites = $row->getData($this->getColumn()->getIndex());
        if(in_array($roleStoreId, $sites)){

                $msg = 'In stock';
            }
            else {
                 $msg = 'Out of stock';
            }
        
        return $msg;
    }
    
}