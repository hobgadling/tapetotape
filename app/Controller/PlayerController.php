<?php
class PlayerController extends AppController{
	public $uses = array('Game','Team','Player','PlayerStat');
	
	public function detail($player_id){
		$player = $this->Player->find('first',array('conditions' => array('Player.id' => $player_id)));
		$this->set('player',$player);
	}
}	
?>