<?php
class Game extends AppModel{
      public $belongsTo = array('Season');
      public $hasMany = array('Pass','Shift','Shot','Goal','Faceoff','TeamGameStat','PlayerGameStat','GameScore');
      public $hasAndBelongsToMany = array(
      	     'Player',
	     'Team'
      );
}
?>