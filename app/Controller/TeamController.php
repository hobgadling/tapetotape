<?php
class TeamController extends AppController{
	var $uses = array('Game','Team','TeamStat');
	
	public function index($situation='All',$close='0'){
		$teams = $this->Team->find('all',array('order' => 'team_name ASC'));
		$this->set('teams',$teams);
		
		$games = $this->Game->find('all',array('conditions' => array('Game.complete' => 1),'order' => 'Game.event_date DESC','limit' => 5));
		$this->set('games',$games);
		$this->set('situation',$situation);
		$this->set('close',$close);
	}
	
	public function detail($short_name,$situation='All',$close='0'){
		$team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $short_name),'recursive' => 2));
		$this->set('team',$team);
		
		$games = $this->Game->find('all',array('conditions' => array(
			'Game.complete' => 1),
			'contain' => array('Team' => array(
				'Team.id' => $team['Team']['id']
			)),
			'order' => 'Game.event_date DESC','limit' => 5));
		$this->set('games',$games);
		$this->set('situation',$situation);
		$this->set('close',$close);
	}
	
}	
?>