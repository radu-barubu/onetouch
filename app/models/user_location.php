<?php

class UserLocation extends AppModel
{

    public $name = 'UserLocation';
    public $primaryKey = 'location_id';
    public $useTable = 'user_locations';

	public $belongsTo = array(
        'UserAccount' => array(
            'className' => 'UserAccount',
            'foreignKey' => 'user_id'
        )
    );
	
}

?>