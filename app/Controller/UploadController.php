<?php
class UploadController extends AppController{
	public $uses = array('Game','Player','Pass','Shot');
	
	public function index(){
		$first_game = $this->Game->find('first',array('order' => 'event_date ASC'));
		$date = date('Y-m-d',strtotime($first_game['Game']['event_date']));
		$todays_date = date('Y-m-d');
		$games = array();
		$i = 1;
		while($date != $todays_date){
			$games[$date] = $this->Game->find('all',array('conditions' => array(
				'Game.event_date LIKE' => $date . '%',
			)));
			$date = date('Y-m-d',strtotime($first_game['Game']['event_date'] . ' +' . $i . ' days'));
			$i++;
		}
		$this->set('games',$games);
		if(count($_POST) > 0){
			
		}
	}
	
	public function check(){
		$games = $this->Game->find('all',array('conditions' => array(
			'NOT' => array(
				'Game.nhl_id' => null
			)
		)));
		foreach($games as $game){
			if(count($game['Team']) < 2){
				print_r($game);
			}
		}
		die('done');
	}
}
?>