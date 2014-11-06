<?php
class Penalty extends AppModel{
	public $belongsTo = array('Player','Game');
}
?>