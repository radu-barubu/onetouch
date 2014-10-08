<?php
/* Here we refer the database table and primary key , this will be loaded insidde the preferences_contraller.php -> fucntion account.
 * Table in DB: user_preferences
 * Primary key: user_preference_id
 */
  class UserPreference extends AppModel 
  { 
    var $name = 'UserPreference'; 
    var $primaryKey = 'user_preference_id';
    var $useTable = 'user_preferences';
  }
?>
