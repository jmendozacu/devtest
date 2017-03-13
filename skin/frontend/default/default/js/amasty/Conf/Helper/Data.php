<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_NOIMG_IMG            = 'amconf/general/noimage_img';
    const XML_PATH_USE_SIMPLE_PRICE     = 'amconf/general/use_simple_price';
    const XML_PATH_OPTIONS_IMAGE_SIZE   = 'amconf/list/listimg_size';
    
    protected $onClick;
    
    protected $amConf;
    
    public function getImageUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'amconf' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . 'amconf' . '/' . 'images' . '/' . $optionId . '.jpg';
        }
        return '';
    }
    
    public function getNoimgImgUrl()
    {
        if (Mage::getStoreConfig(self::XML_PATH_NOIMG_IMG))
        {
            return Mage::getBaseUrl('media') . 'amconf/noimg/' . Mage::getStoreConfig(self::XML_PATH_NOIMG_IMG);
        }
        return '';
    }
    
    public function getConfigUseSimplePrice()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_SIMPLE_PRICE);
    } 
    
    public function getOptionsImageSize()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPTIONS_IMAGE_SIZE);
    } 
    
    public function getAmconfAttr()
    {
        return $this->amConf;
    } 
    
    public function getHtmlBlock($_product, $html)
    {
        $blockForForm = Mage::app()->getLayout()->createBlock('amconf/catalog_product_view_type_configurablel', 'amconf.catalog_product_view_type_configurable', array('template'=>"amasty/amconf/configurable.phtml"));
        $blockForForm->setProduct($_product);
        $blockForForm->setNameInLayout('product.info.options.configurable');
        $submitUrl = $blockForForm->getSubmitUrl($_product);
        $html .= '<div class="hover_block"><form action="'.Mage::helper('checkout/cart')->getAddUrl($_product).'" method="post" id="product_addtocart_form_'.$_product->getId().'">';
        $html .= '<div id="insert" style="display:none;"></div><div id="amconf-block">' . $blockForForm->toHtml() . '</div>';
        $attributes = $blockForForm->getAttributes();
        if(Mage::getStoreConfig('amconf/product_image_size/have_button')) {
            $onClick = "formSubmit(this,'".$submitUrl."', '".$_product->getId()."', ".$attributes.")";
            $amConf = "createForm('".$submitUrl."', '".$_product->getId()."', ".$attributes.")";
            $onClick = "productAddToCartForm_".$_product->getId().".submit()";
            //onclick="' . $onClick . '"
            $html .=  '<div class="actions">
                    <div class="quantity">
                    <input type="text" name="qty" id="qty" maxlength="12" value="1" title="Qty" class="input-text qty" />
                    </div>
                      <button style="margin-right:-107px;" type="submit" title="' . $this->__('Add to Cart') . '" class="button btn-cart"  amconf="' . $amConf . '">
                            <span>
                                <span>'.$this->__('Add').'</span>
                            </span>
                      </button>' ;                
                $html .=  '</form></div>';    
        }
        return $html;
    }
	public function cartItems() {
        $product_ids = array();
        $products = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        foreach ($products as $_products){

            if ($_products->getProductType()=="configurable") {
                $simpleSku =  $_products->getSku();
                $simpleProduct=Mage::getModel('catalog/product')->loadByAttribute('sku', $simpleSku);

                $simpleId =  $simpleProduct->getId();
                $id = $simpleId;
   
                if($id) {
                    if($product_ids [$id]=="")
                        $product_ids [$id] = $_products->getQty();
                    else {
                        $currentTotalspl = $product_ids [$id];
                        $newTotalspl = $_products->getQty();
                        $product_ids [$id] = $currentTotalspl + $newTotalspl ;
                    }

                }
            } else
                if($product_ids [$_products->getProductId()]=="")
                    $product_ids [$_products->getProductId()] = $_products->getQty();
                else {
                    $currentTotal = $product_ids [$_products->getProductId()];
                    $newTotal = $_products->getQty();
                    $product_ids [$_products->getProductId()] = $currentTotal + $newTotal ;
                }
            }
        return $product_ids;
    }
}