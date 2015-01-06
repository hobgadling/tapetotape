<?php
class TeamGameStat extends AppModel{
	public $name = 'TeamGameStat';
	public $belongsTo = array('Team','Game');
}
?>