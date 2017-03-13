<?php
class XJ_Importexportcategory_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Abstract
{
	public function toimportHtml() {
		$tmpFile = 'var/import/category/categories.csv';
        
        if( ! file_exists($tmpFile)) {
            echo 'Cant find Catalog File: ' . $tmpFile;
            Mage::log('Cant find Catalog File: ' . $tmpFile);
            exit;
        }

        $delimiter = ',';
        $enclosure = '"';
        $row = 0;
	$importid = array();	
	$updateid = array();
	$returnrowid = array();
        if (($handle = fopen($tmpFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 8192, $delimiter)) !== FALSE) 
	    {          
		if($row > 0) 
		{
			 $pathnotallow = true;
			 $explode = explode("/",$data[1]); 
		         $catid = array();
			 for($i=0;$i<count($explode);$i++)
			 {
				$j=$i+1;
				$array1 = array('1');
				$array2 = array("%");
				$result = array_merge($array1,$catid,$array2);
				$implode = implode("/",$result);
				$categoryCollection = Mage::getResourceModel('catalog/category_collection')
    						     ->addFieldToFilter('name',$explode[$i])
						     ->addFieldToFilter('path', array('like'=> $implode))
						     ->addFieldToFilter('level',$j)
						     ->load();	
				if($categoryCollection->count() == 0)
				{
					$returnrowid[] = $row;
					$this->showError("Invaild Row ".$row." because '".$explode[$i] ."' category not Import ");
					$pathnotallow = false;
				}
				foreach ($categoryCollection as $cat) {
    					$catid[] = $cat->getId();
				}
			 } 
			if($pathnotallow == "true")
			{
			$array11 = array('1');
			$array21 = array("%");
			$result1 = array_merge($array11,$catid,$array21);
			$implode1 = implode("/",$result1);	 
			$path = substr($implode1,0,-2);
			$level = count($explode) + 1;
			$categoryCollection = Mage::getResourceModel('catalog/category_collection')
					    ->addFieldToFilter('name',$data[0])
					    ->addFieldToFilter('path', array('like'=> $implode1))
					    ->addFieldToFilter('level',$level)
					    ->load();	
			$categoryCollectioncount = $categoryCollection->count();
			if($categoryCollectioncount == 0)
			{
				$categoryid = 0;
			}
			else
			{					
					foreach ($categoryCollection as $cat) 
					{
					 	$categoryid = $cat->getId();

					} 
			}			
			if($categoryid == 0)
			{			
				$returnid = $this->addcategory($data,$path);
				$importid[] = $returnid;
			}
			else
			{
				$returnid = $this->updatecategory($data,$path,$categoryid);	
				$updateid[] = $returnid;			
			}
			}
							
                }
                $row++;
            }
            fclose($handle);
        }
	$returnrowimplodeid = implode(",",$returnrowid);	
	$importimplodeid = implode(",",$importid);
	$updateimplodeid = implode(",",$updateid);
	if($returnrowimplodeid)
	{
		$str="Invalide row path in this Columns = ".$returnrowimplodeid;
	//	Mage::getSingleton('adminhtml/session')->addError($str);
	}	
	$message ="";
	if($importimplodeid){$message .= 'category import SuccessFully id = '.$importimplodeid.'<br>';}else{$message.="";}
	if($updateimplodeid){$message .= 'category update SuccessFully id = '.$updateimplodeid.'<br>';}else{$message.="";}		
	//Mage::getSingleton('adminhtml/session')->addSuccess('Category Import SuccessFully');	
	return;
	}
    	public function addcategory($data,$path)
	{
		$category = Mage::getModel('catalog/category');
		$category->setStoreId(0); 
		$general['name'] = $data[0];
		$general['path'] = $path; 
		$general['description'] = $data[5];
		$general['meta_title'] = $data[8]; 
		$general['meta_keywords'] = $data[9];
		$general['meta_description'] = $data[10];
		$general['display_mode'] = $data[12]; 
		$general['url_key'] = $data[4];
		if($data[3] == 'Yes'){$isactive = 1;}else{$isactive = 0;}			
		$general['is_active'] = $isactive;
		if($data[14] == 'Yes'){$isanchor = 1;}else{$isanchor = 0;}
		$general['is_anchor'] = $isanchor;
		$general['page_layout'] = $data[17];
		$general['thumbnail'] = $data[7];
		$general['image'] = $data[6];					
		$category->addData($general);
		 
		try {
		    $category->save();
		    $this->showSuccess($this->__("Insert Success! Id: ".$category->getId()));
		    return $category->getId();
		}
		catch (Exception $e){
		    echo $e->getMessage();
		}unset($category);
	}
	public function updatecategory($data,$path,$id)
	{
		$category = Mage::getModel('catalog/category')->load($id);
		$category->setStoreId(0); 
		$general['description'] = $data[5];
		$general['meta_title'] = $data[8]; 
		$general['meta_keywords'] = $data[9];
		$general['meta_description'] = $data[10];
		$general['display_mode'] = $data[12]; 
		$general['url_key'] = $data[4];
		if($data[3] == 'Yes'){$isactive = 1;}else{$isactive = 0;}			
		$general['is_active'] = $isactive;
		if($data[14] == 'Yes'){$isanchor = 1;}else{$isanchor = 0;}
		$general['is_anchor'] = $isanchor;
		$general['page_layout'] = $data[17];
		$general['thumbnail'] = $data[7];
		$general['image'] = $data[6];					
		$category->addData($general);
		 
		try {
		    $category->save();
		    $this->showSuccess($this->__("update Success! Id: ".$category->getId()));
		    return $category->getId();
		}
		catch (Exception $e){
		    echo $e->getMessage();
		}unset($category);
	}
	public function getHeadInfo($title) {
		echo '<html><head>';
		echo '<title>'.$title.'</title>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
		echo '<script type="text/javascript">var FORM_KEY = "'.Mage::getSingleton('core/session')->getFormKey().'";</script>';

		$headBlock = $this->getLayout()->createBlock('page/html_head');
		$headBlock->addJs('prototype/prototype.js');
		$headBlock->addJs('mage/adminhtml/loader.js');
		echo $headBlock->getCssJsHtml();

		echo '<style type="text/css">';
		echo 'ul { list-style-type:none; padding:0; margin:0; }';
		echo 'li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif; }';
		echo 'img { margin-right:5px; }';
		echo 'a.back {color: #ED6502; font-weight: bold; text-decoration: none; }';
		echo '</style>';
		echo '</head>';
	}

	public function getStartUpInfo() {
		echo '<body>';
		echo '<ul>';
		$this->showNote($this->__("Starting profile execution, please wait..."));
		$this->showWarning($this->__("Warning: Please do not close the window during exporting data"));
		echo '</ul>';
		echo '<ul id="profileRows">';
	}

	public function getEndInfo() {
		$this->showNote($this->__("Finished profile execution."));
		echo "</ul>";
		echo '</body></html>';
	}

	public function showError($text, $id = '') {
		echo '<li style="background-color:#FDD; " id="'.$id.'">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/error_msg_icon.gif').'" class="v-middle"/>';
		echo $text;
		echo "</li>";
	}

	public function showWarning($text, $id = '') {
		echo '<li id="'.$id.'" style="background-color:#FFD;">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/fam_bullet_error.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showNote($text, $id = '') {
		echo '<li id="'.$id.'">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showSuccess($text, $id = '') {
		echo '<li id="'.$id.'" style="background-color:#DDF;">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/fam_bullet_success.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}
}
?>


