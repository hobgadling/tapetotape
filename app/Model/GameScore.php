<?php
class GameScore extends AppModel{
	public $name = 'GameScore';
	public $belongsTo = array('Game');
}	
?>