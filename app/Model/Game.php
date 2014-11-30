<?php
class Game extends AppModel{
      public $belongsTo = array('Season');
      public $hasMany = array('Pass','Shift','Shot','Goal','Faceoff','TeamGameStat','PlayerGameStat');
      public $hasAndBelongsToMany = array(
      	     'Player',
	     'Team'
      );
}
?>