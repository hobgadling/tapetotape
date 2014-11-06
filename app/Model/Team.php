<?php
class Team extends AppModel{
 	 public $hasMany = array('Player' => array(
 	 	'order' => 'Player.position',
 	 	'conditions' => 'Player.name IS NOT NULL'
 	 ));
     public $hasAndBelongsToMany = array('Game');
}
?>