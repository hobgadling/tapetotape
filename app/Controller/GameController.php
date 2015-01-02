<?php
class GameController extends AppController{
	public $uses = array('Game','Team','Player','TeamGameStat','PlayerGameStat','Shot','Pass');
	
	public function detail($game_id,$situation='All',$close='0'){
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id),'recursive' => 2));
		$this->set('game',$game);
		
		$shots = $this->Shot->find('all',array(
			'conditions' => array('Shot.game_id' => $game_id),
			'order' => 'Shot.time ASC',
			'contain' => array('Pass' => array('Player'),'Player' => array('Team'))
		));
		foreach($shots as $id=>$shot){
			if(count($shot['Pass']) == 0){
				unset($shots[$id]);
			}
		}
		$this->set('shots',$shots);
		$this->set('situation',$situation);
		$this->set('close',$close);
	}
}	
?>