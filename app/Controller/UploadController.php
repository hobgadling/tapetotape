<?php
class UploadController extends AppController{
	public $uses = array('Game','Player','Pass','Shot','Team');
	
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
			require_once('../Vendor/PHPExcel.php');
			
			foreach($_FILES as $id=>$file){
				if($file['error'] == 0){
					move_uploaded_file($file['tmp_name'], '../webroot/uploads/' . $file['name']);
					$filepath = '../webroot/uploads/' . $file['name'];
					$objPHPExcel = PHPExcel_IOFactory::load($filepath);
					$data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$team_id = explode('_', $id)[1];
					$this->Game->id = $_POST['game_id'];
					foreach($data as $shot){
						if($shot['A'] == 1 || $shot['A'] == 2 || $shot['A'] == 3){
							$period_left = $shot['B'];
							$left_seconds = intval(substr($period_left,strpos($period_left,':') + 1));
							$left_minutes = intval(substr($period_left,0,strpos($period_left,':')));
							$left_seconds += 60*$left_minutes;
							$seconds = (20*60) - $left_seconds;
							$game_seconds = ((intval($shot['A']) - 1) * (20*60)) + $seconds;
							
							$player_number = $shot['C'];
							$player = $this->Game->Player->find('first',array('conditions' => array(
								'Player.number' => $player_number,
								'Player.team_id' => $team_id
							),
								'contain' => array('Game' => array(
									'Game.id' => $_POST['game_id']
								)),
							));
							$nhl_shot = $this->Game->Shot->find('first',array('conditions' => array(
								'Shot.player_id' => $player['Player']['id'],
								'Game.id' => $_POST['game_id'],
								'AND' => array(
									'Shot.time >=' => $game_seconds - 10,
									'Shot.time <=' => $game_seconds + 10
								),
								
							)));
							if(!$nhl_shot){
								$nhl_shot = $this->Game->Player->Shot->find('first',array('conditions' => array(
									'Player.id' => $player['Player']['id'],
									'AND' => array(
										'Shot.time >=' => $game_seconds - 60,
										'Shot.time <=' => $game_seconds + 60
									)
								)));
							}
							$shot_id = $nhl_shot['Shot']['id'];
							$primary_player = $this->Game->Player->find('first',array('conditions' => array(
								'Player.number' => $shot['D'],
								'Player.team_id' => $team_id
							),
								'contain' => array('Game' => array(
									'Game.id' => $_POST['game_id']
								)),
							));
							$type = ($shot['I'] == 'Y' ? 'SG' : 'SAG');
							if($shot['H'] == 'Y'){
								$location = 'SC';
							} else if($shot['G'] == 'Y'){
								$location = 'D/NZ';
							} else {
								$location = 'OZ';
							}
							$primary_pass = array(
								'Game' => array('id' => $_POST['game_id']),
								'Player' => array('id' => $primary_player['Player']['id']),
								'Shot' => array('id' => $shot_id),
								'Pass' => array(
									'type' => $type,
									'location' => $location,
									'order' => 'A',
									'created_at' => date('Y-m-d H:i:s')
								)
							);
							$this->Pass->saveAssociated($primary_pass);
							if(intval($shot['E']) != 0){
								$secondary_player = $this->Game->Player->find('first',array('conditions' => array(
									'Player.number' => $shot['E'],
									'Player.team_id' => $team_id
								),
									'contain' => array('Game' => array(
										'Game.id' => $_POST['game_id']
									)),
								));
								$type = ($shot['I'] == 'Y' ? 'SG' : 'SAG');
								if($shot['H'] == 'Y'){
									$location = 'SC';
								} else if($shot['F'] == 'Y'){
									$location = 'D/NZ';
								} else {
									$location = 'OZ';
								}
								$secondary_pass = array(
									'Game' => array('id' => $_POST['game_id']),
									'Player' => array('id' => $secondary_player['Player']['id']),
									'Shot' => array('id' => $shot_id),
									'Pass' => array(
										'type' => $type,
										'location' => $location,
										'order' => 'A2',
										'created_at' => date('Y-m-d H:i:s')
									)
								);
								$this->Pass->saveAssociated($secondary_pass);
							}
						}
					}
				}
			}
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