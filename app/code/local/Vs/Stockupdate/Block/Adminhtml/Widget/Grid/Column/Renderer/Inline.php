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
class Vs_Stockupdate_Block_Adminhtml_Widget_Grid_Column_Renderer_Inline extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
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
        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'id="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $row->getData($this->getColumn()->getIndex()) . '"';
        $html .= 'class="validate-number input-text ' . $this->getColumn()->getInlineCss() . '" onblur="updateField(this, '. $row->getId() .'); return false"/>';
        //$html .= '<button onclick="updateField(this, '. $row->getId() .'); return false">' . Mage::helper('deals')->__('Update') . '</button>';
 
        return $html;
    }
    
}