<?php
class Goal extends AppModel{
	public $belongsTo = array('Game','Player','Shot');
	public $hasMany = array('Assist');
}
?>