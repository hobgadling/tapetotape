<?php
class Shift extends AppModel{
	public $belongsTo = array('Game','Player');
}
?>