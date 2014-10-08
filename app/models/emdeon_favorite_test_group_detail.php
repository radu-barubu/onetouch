<?php

class EmdeonFavoriteTestGroupDetail extends AppModel 
{
	public $name = 'EmdeonFavoriteTestGroupDetail';
	public $primaryKey = 'detail_id';
	public $useTable = 'emdeon_favorite_test_group_details';

	public function beforeSave($options)
	{
		$this->data['EmdeonFavoriteTestGroupDetail']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->data['EmdeonFavoriteTestGroupDetail']['modified_user_id'] = $_SESSION['UserAccount']['user_id'];
		return true;
	}
	
	public function deleteByForeignKey($test_group_id)
	{
		$items = $this->find('all', array('conditions' => array('EmdeonFavoriteTestGroupDetail.test_group_id' => $test_group_id)));
		
		foreach($items as $item)
		{
			$this->delete($item['EmdeonFavoriteTestGroupDetail']['detail_id'], false);
		}
	}
}

?>