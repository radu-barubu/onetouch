<?php

class AdministrationPointOfCareCategory extends AppModel {

	public $name = 'AdministrationPointOfCareCategory';
	public $primaryKey = 'point_of_care_category_id';
	public $useTable = 'administration_point_of_care_categories';
	
	
	public function saveCategory($pocType, $item) {

		$item = trim($item);
		if (!$item) {
			return false;
		}
		
		$category = $this->getCategories($pocType, false);
		
		$list = array();
		if ($category['AdministrationPointOfCareCategory']['point_of_care_category']) {
			$list = json_decode($category['AdministrationPointOfCareCategory']['point_of_care_category']);
		}	
		
		if (!in_array($item, $list)) {
			$list[] = $item;
		}
	
		$category['AdministrationPointOfCareCategory']['point_of_care_category'] = json_encode($list);
		$category['AdministrationPointOfCareCategory']['modified_timestamp'] = __date('Y-m-d H:i:s');
		$category['AdministrationPointOfCareCategory']['modified_user_id'] = EMR_Account::getCurretUserId();
		$this->save($category);
		
		return true;
	}
	
	public function getCategories($pocType, $output = 'list') {
		$category = $this->find('first', array(
			'conditions' => array(
				'AdministrationPointOfCareCategory.point_of_care_type' => $pocType,
			),
		));

		if (!$category) {
			$this->create();
			$this->save(array(
				'AdministrationPointOfCareCategory' => array(
					'point_of_care_type' => $pocType,
					'modified_user_id' => EMR_Account::getCurretUserId(),
					'modified_timestamp' => __date('Y-m-d H:i:s'),
				),
			));
			
			$category = $this->find('first', array(
				'conditions' => array(
					'AdministrationPointOfCareCategory.point_of_care_type' => $pocType,
				),
			));			
			
		}
		
		if ($output !== 'list') {
			return $category;
		}
		
		$list = array();
		if ($category['AdministrationPointOfCareCategory']['point_of_care_category']) {
			$list = json_decode($category['AdministrationPointOfCareCategory']['point_of_care_category']);
		}		
		
		return $list;
	}
	
	

}
