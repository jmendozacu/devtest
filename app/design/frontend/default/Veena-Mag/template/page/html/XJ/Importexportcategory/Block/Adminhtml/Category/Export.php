<?php

/**
 * Export category block
 *
 * @category   XJ
 * @package    XJ_Export
 * @author     XJ 
 */
class XJ_Importexportcategory_Block_Adminhtml_Category_Export extends XJ_Importexportcategory_Block_Adminhtml_Export
{

	public function getTitle($type) {
		$types = array(
			'categories' => $this->__('Export Categories'),
			'attributes' => $this->__('Export Category Attributes')
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function getFilePath() {
		return 'media' . DS . 'export' . DS . 'category';
	}

}
