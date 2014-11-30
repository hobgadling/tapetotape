<?php
class Player extends AppModel{
  public $belongsTo = array('Team');
  
  public $hasMany = array('PlayerStat','PlayerGameStat','Pass','Shift','Shot','Goal','Assist','Block',
  	'FaceoffWins' => array(
  		'className' => 'Faceoff',
  		'foreignKey' => 'winner_id'
  	),
  	'FaceoffLosses' => array(
  		'className' => 'Faceoff',
  		'foreignKey' => 'loser_id'
  	)
  );
  
  public $hasAndBelongsToMany = array('Game');
}
?>