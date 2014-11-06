<?php
class Shot extends AppModel{
	public $belongsTo = array('Player','Game');
	public $hasMany = array('Block','Goal','Pass');
}
?>