<?php
class Game extends AppModel{
      public $belongsTo = array('Season');
      public $hasMany = array('Pass','Shift','Shot','Goal','Faceoff','Penalty');
      public $hasAndBelongsToMany = array(
      	     'Player',
	     'Team'
      );
}
?>