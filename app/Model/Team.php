<?php
class Team extends AppModel{
 	 public $hasMany = array('Player','TeamStat','TeamGameStat');
     public $hasAndBelongsToMany = array('Game');
}
?>