<?php
class XJ_Importexportcategory_Block_Adminhtml_Category_Import extends XJ_Importexportcategory_Block_Adminhtml_Import
{

	public function getTitle($type) {
		$types = array(
			'categories' => $this->__('Import Categories'),
			'attributes' => $this->__('Import Category Attributes')
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function getFilePath() {
		return 'var' . DS . 'export' . DS . 'category';
	}

}
