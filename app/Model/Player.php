<?php
class Player extends AppModel{
  public $belongsTo = array('Team');
  
  public $hasMany = array('Pass','Shift','Shot','Goal','Assist','Block','Penalty',
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
  
  public $recursive = 2;
}
?>