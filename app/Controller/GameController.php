<?php
class GameController extends AppController{
	public $uses = array('Game','Team','Player','TeamGameStat','PlayerGameStat');
	
	public function detail($game_id){
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id),'recursive' => 2));
		$this->set('game',$game);
	}
}	
?>