<?php
class Faceoff extends AppModel{
	public $belongsTo = array(
		'Winner' => array(
			'className' => 'Player',
			'foreignKey' => 'winner_id'
		),
		'Loser' => array(
			'className' => 'Player',
			'foreignKey' => 'loser_id'
		),
		'Game'
	);
}
?>