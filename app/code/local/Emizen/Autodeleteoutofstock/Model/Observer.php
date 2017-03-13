<?php
class Emizen_Autodeleteoutofstock_Model_Observer
{

			public function autodelete(Varien_Event_Observer $observer)
			{
				
				 if(!Mage::getStoreConfig('emizen/emizen/general')) // if not enable extension return false
      				 return;
					
					
					$session = Mage::getSingleton("checkout/session"); 
					$quote = $session->getQuote();
					$cartItems = $quote->getAllItems();
					foreach ($cartItems as $item)
					{
						//$productType = $item->getProduct()->getTypeId();
						//if($productType!='configurable') {
						$productId = $item->getProductId();
						$product = Mage::getModel('catalog/product')->load($productId);
						$stockItem = $product->getStockItem();
						if(!$stockItem->getIsInStock())
						{
								
								//$produclink	=	' <a href="'.$product->getProductUrl().'">'.$product->getName().'</a> is deleted.';
								$produclink	=	Mage::helper('checkout')->__('Unfortunately, <a href="%s"> %s</a> is not on stock anymore and hence it is removed from cart.',$product->getProductUrl(),$product->getName());	
    							$session->addError($produclink);
								Mage::helper('checkout/cart')->getCart()->removeItem($item->getId())->save();
								$quote->setHasError(false);
								
								Mage::getSingleton('checkout/session')->setCartWasUpdated(true); 
						}
						//}
					}
				
				
				
			}
		
}