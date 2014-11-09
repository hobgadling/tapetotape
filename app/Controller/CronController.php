<?php
error_reporting(E_ALL);

class CronController extends AppController{
	public $uses = array('Season','Game','Team','Player','Shift','Shot','Faceoff','Goal','Block','Shot','Assist');
	
	public function getRosterData($season_id,$nhl_game_id){
		include_once '../Vendor/simple_html_dom.php';
		
		$data = array();
		
		$season = $this->Season->find('first',
			array('conditions'=>
				array(
					'start_year' => substr($season_id,0,4),
					'end_year' => substr($season_id,4)
				)
			)
		);
		if(!$season){
			$this->Season->create();
			$this->Season->set(array(
				'start_year' => substr($season_id,0,4),
				'end_year' => substr($season_id,4)
			));
			$this->Season->save();
			$season = $this->Season->read(null,$this->Season->id);
		}
		$roster_html = file_get_html('http://www.nhl.com/scores/htmlreports/' . $season_id . '/RO02' . str_pad($nhl_game_id,4,0,STR_PAD_LEFT) . '.HTM');
		$game = $this->Game->find('first',array('conditions' => array(
			'season_id' => $season['Season']['id'],
			'nhl_id' => $nhl_game_id
		)));
		$data['Game'][0]['id'] = $game['Game']['id'];
		
		
		$data['Team'] = array();
		foreach($roster_html->find('img') as $img){
			$shortname = substr(pathinfo(parse_url($img->src,PHP_URL_PATH))['filename'],5);
			if($shortname != 'nhl'){
				$team = $this->Team->find('first',array('conditions' => array('short_name' => $shortname)));
				$data['Team'][]['id'] = $team['Team']['id'];
			}
		}
		
		$team_index = 0;
		foreach($roster_html->find('table') as $table){
			if(count($table->children()) == 21){
				for($i = 1;$i < count($table->children());$i++){
					$row = $table->children($i);
					$p_name = $row->children(2)->plaintext;
					if(strpos($p_name,'(') !== false){
						$p_name = trim(substr($p_name, 0, strpos($p_name, '(')));
					}
					$p_name = strtolower($p_name);
					$player = $this->Player->find('first',array('conditions' => array('name' => $p_name,'number' => $row->children(0)->plaintext,'position' => $row->children(1)->plaintext, 'team_id' => $data['Team'][$team_index]['id'])));
					if(!$player){
						$player = array(
							'Team' => array('id' => $data['Team'][$team_index]['id']),
							'Player' => array(
								'name' => $p_name,
								'number' => $row->children(0)->plaintext,
								'position' => $row->children(1)->plaintext,
								'created_at' => date('Y-m-d H:i:s')
							),
							'Game' => array('id' => $game['Game']['id'])
						);
						$game = array(
							'Team' => array('id' => $data['Team'][$team_index]['id']),
							'Game' => array('id' => $game['Game']['id'])
						);
						$this->Player->saveAssociated($player);
						$this->Team->saveAssociated($game);
					} else {
						$player = array(
							'Game' => array('id' => $game['Game']['id']),
							'Player' => array('id' => $player['Player']['id']),
							'Team' => array('id' => $data['Team'][$team_index]['id'])
						);
						$game = array(
							'Team' => array('id' => $data['Team'][$team_index]['id']),
							'Game' => array('id' => $game['Game']['id'])
						);
						$this->Player->saveAssociated($player);
						$this->Team->saveAssociated($game);
					}
				}
				$team_index++;
			}
		}
	
	}
	
	public function getTOIData($season_id,$nhl_game_id){
		include_once '../Vendor/simple_html_dom.php';
		
		$toi_home_html = file_get_html('http://www.nhl.com/scores/htmlreports/' . $season_id . '/TH02' . str_pad($nhl_game_id,4,0,STR_PAD_LEFT) . '.HTM');
		$toi_away_html = file_get_html('http://www.nhl.com/scores/htmlreports/' . $season_id . '/TV02' . str_pad($nhl_game_id,4,0,STR_PAD_LEFT) . '.HTM');
		
		$game = $this->Game->find('first',array('conditions' => array('Game.nhl_id' => $nhl_game_id)));
		
		foreach($game['Team'] as $team){
			if($team['id'] == $game['Game']['home_team_id']){
				$curr_html = $toi_home_html;
			} else {
				$curr_html = $toi_away_html;
			}
			
			$player = null;
			foreach($curr_html->find('tr') as $row){
				if($row->first_child()->class == 'playerHeading + border'){
					$text = trim($row->plaintext);
					$number = substr($text, 0, strpos($text, ' '));
					
					$name = trim(substr($text, strpos($text,' ')));
					$lname = substr($name, 0, strpos($name, ','));
					$fname = substr($name, strpos($name,',') + 1);
					$query_name = trim(strtolower($fname . ' ' . $lname));
					$player = $this->Player->find('first',array('conditions' => array(
						'Player.number' => $number,
						'Player.name' => $query_name
					)));
				} else if(($row->class == 'oddColor' || $row->class == 'evenColor') && count($row->children()) == 6){
					$start_col = $row->children(2)->plaintext;
					$end_col = $row->children(3)->plaintext;
					$period = $row->children(1)->plaintext;
					
					$start_time = trim(substr($start_col, 0, strpos($start_col, '/')));
					$end_time = trim(substr($end_col, 0, strpos($end_col, '/')));
					
					$start_seconds = intval(substr($start_time,strpos($start_time,':') + 1));
					$start_minutes = intval(substr($start_time,0,strpos($start_time,':')));
					$start_seconds += 60*$start_minutes;
					
					$end_seconds = intval(substr($end_time,strpos($end_time,':') + 1));
					$end_minutes = intval(substr($end_time,0,strpos($end_time,':')));
					$end_seconds += 60*$end_minutes;
					
					if($period == '2'){
						$start_seconds += 20*60;
						$end_seconds += 20*60;
					} else if($period == '3'){
						$start_seconds += 40*60;
						$end_seconds += 40*60;
					} else if($period == 'OT'){
						$start_seconds += 60*60;
						$end_seconds += 60*60;
					}
					
					$shift = array(
						'Game' => array('id' => $game['Game']['id']),
						'Player' => array('id' => $player['Player']['id']),
						'Shift' => array(
							'time_start' => $start_seconds,
							'time_end' => $end_seconds
						)
					);
					
					$this->Shift->saveAssociated($shift);
				}
			}
		}
		die('finished');
	}
	
	public function getPBPData($season_id,$nhl_game_id){
		include_once '../Vendor/simple_html_dom.php';
		
		$pbp_html = file_get_html('http://www.nhl.com/scores/htmlreports/' . $season_id . '/PL02' . str_pad($nhl_game_id,4,0,STR_PAD_LEFT) . '.HTM');
		
		$game = $this->Game->find('first',array('conditions' => array('Game.nhl_id' => $nhl_game_id)));
		
		foreach($pbp_html->find('.evenColor') as $row){
			$time = substr(trim($row->children(3)->innertext), 0, strpos(trim($row->children(3)->innertext), '<'));
			$seconds = intval(substr($time,strpos($time,':') + 1));
			$minutes = intval(substr($time,0,strpos($time,':')));
			$seconds += 60*$minutes;
			
			$period = intval($row->children(1)->plaintext);
			$seconds += 60*(($period-1)*20);
			
			$count1 = count($row->children(6)->find('td table')) - 1;
			$count2 = count($row->children(7)->find('td table')) - 1;
			
			if($count1 == $count2){
				$situation = $count1 . 'v' . $count2;
			} else if($count2 < $count1){
				$situation = $count1 . 'v' . $count2;
			} else {
				$situation = $count2 . 'v' . $count1;
			}
			
			$desc = $row->children(5)->plaintext;
			$desc_tokens = explode(' ', $desc);
			
			switch($row->children(4)->plaintext){
				case 'FAC':
					$winning_team_short = $desc_tokens[0];
					$winning_team = $this->Team->find('first',array('conditions' => array('Team.pbp_short_name' => $winning_team_short)));
					$zone_string = $desc_tokens[2];
					switch($zone_string){
						case 'Neu.':
							$zone = 'Neutral';
							break;
						case 'Off.':
							if($winning_team['Team']['id'] == $game['Game']['home_team_id']){
								$zone = 'Home';
							} else {
								$zone = 'Away';
							}
							break;
						case 'Def.':
							if($winning_team['Team']['id'] == $game['Game']['home_team_id']){
								$zone = 'Away';
							} else {
								$zone = 'Home';
							}
							break;
					}
					foreach($desc_tokens as $id=>$token){
						if($token[0] == '#'){
							$number = substr($token, 1);
							$pbp_team_short = $desc_tokens[$id-1];
							$lname = str_replace(',','',strtolower($desc_tokens[$id+1]));
							$player = $this->Game->Team->Player->find('first',array('conditions' => array(
								'Player.number' => $number,
								'Team.pbp_short_name' => $pbp_team_short
							)));
							if($pbp_team_short == $winning_team_short){
								$win_id = $player['Player']['id'];
							} else {
								$los_id = $player['Player']['id'];
							}
						}
					}
					$faceoff = array(
						'Game' => array('id' => $game['Game']['id']),
						'Winner' => array('id' => $win_id),
						'Loser' => array('id' => $los_id),
						'Faceoff' => array(
							'zone' => $zone,
							'time' => $seconds,
							'situation' => $situation
						)
					);
					$this->Faceoff->saveAssociated($faceoff);
					break;
				case 'BLOCK':
					$number = substr($desc_tokens[1], 1);
					$pbp_team_short = $desc_tokens[0];
					$lname = str_replace(',','',strtolower($desc_tokens[2]));
					$player = $this->Game->Team->Player->find('first',array('conditions' => array(
						'Player.number' => $number,
						'Team.pbp_short_name' => $pbp_team_short
					)));
					$shot = array(
						'Game' => array('id' => $game['Game']['id']),
						'Player' => array('id' => $player['Player']['id']),
						'Shot' => array(
							'type' => 'Blocked',
							'time' => $seconds,
							'situation' => $situation
						)
					);
					$this->Shot->SaveAssociated($shot);
					
					$number = substr($desc_tokens[7], 1);
					$pbp_team_short = $desc_tokens[6];
					$lname = str_replace(',','',strtolower($desc_tokens[8]));
					$player = $this->Game->Team->Player->find('first',array('conditions' => array(
						'Player.number' => $number,
						'Team.pbp_short_name' => $pbp_team_short
					)));
					$block = array(
						'Player' => array('id' => $player['Player']['id']),
						'Shot' => array('id' => $this->Shot->id)
					);
					$this->Block->saveAssociated($block);
					break;
				case 'SHOT':
					foreach($desc_tokens as $id=>$token){
						if($token[0] == '#'){
							$number = substr($token, 1);
							$pbp_team_short = $desc_tokens[0];
							$lname = str_replace(',','',strtolower($desc_tokens[$id+1]));
							$player = $this->Game->Team->Player->find('first',array('conditions' => array(
								'Player.number' => $number,
								'Team.pbp_short_name' => $pbp_team_short
							)));
						}
					}
					$shot = array(
						'Game' => array('id' => $game['Game']['id']),
						'Player' => array('id' => $player['Player']['id']),
						'Shot' => array(
							'type' => 'On Goal',
							'time' => $seconds,
							'situation' => $situation
						)
					);
					$this->Shot->SaveAssociated($shot);
					break;
				case 'MISS':
					foreach($desc_tokens as $id=>$token){
						if($token[0] == '#'){
							$number = substr($token, 1);
							$pbp_team_short = $desc_tokens[0];
							$lname = str_replace(',','',strtolower($desc_tokens[$id+1]));
							$player = $this->Game->Team->Player->find('first',array('conditions' => array(
								'Player.number' => $number,
								'Team.pbp_short_name' => $pbp_team_short
							)));
						}
					}
					$shot = array(
						'Game' => array('id' => $game['Game']['id']),
						'Player' => array('id' => $player['Player']['id']),
						'Shot' => array(
							'type' => 'Missed',
							'time' => $seconds,
							'situation' => $situation
						)
					);
					$this->Shot->SaveAssociated($shot);
					break;
				case 'GOAL':
					$number = substr($desc_tokens[1], 1);
					$pbp_team_short = $desc_tokens[0];
					$lname = preg_replace("/[^A-Za-z]+/", "",strtolower($desc_tokens[2]));
					$player = $this->Game->Team->Player->find('first',array('conditions' => array(
						'Player.number' => $number,
						'Team.pbp_short_name' => $pbp_team_short
					)));
					$shot = array(
						'Game' => array('id' => $game['Game']['id']),
						'Player' => array('id' => $player['Player']['id']),
						'Shot' => array(
							'type' => 'On Goal',
							'time' => $seconds,
							'situation' => $situation
						)
					);
					$this->Shot->SaveAssociated($shot);
					$goal = array(
						'Game' => array('id' => $game['Game']['id']),
						'Player' => array('id' => $player['Player']['id']),
						'Shot' => array('id' => $this->Shot->id),
						'Goal' => array(
							'time' => $seconds,
							'situation' => $situation
						)
					);
					$this->Goal->saveAssociated($goal);
					$goal_id = $this->Goal->id;
					
					foreach($desc_tokens as $id => $token){
						if($token[0] == '#' && $id != 1){
							$number = substr($token, 1);
							$pbp_team_short = $desc_tokens[0];
							$lname = preg_replace("/[^A-Za-z]+/", "",strtolower($desc_tokens[$id+1]));
							$player = $this->Game->Team->Player->find('first',array('conditions' => array(
								'Player.number' => $number,
								'Team.pbp_short_name' => $pbp_team_short
							)));
							$assist = array(
								'Player' => array('id' => $player['Player']['id']),
								'Goal' => array('id' => $goal_id)
							);
							$this->Assist->saveAssociated($assist);
						}
					}
					break;
			}
		}
		die('finished');
	}
	
	public function importSeason($season_id){
		include_once '../Vendor/simple_html_dom.php';
		
		$months = array('201410','201411','201412','201501','201502','201503','201504');
		
		foreach($months as $month){
			$month_html = file_get_html('http://www.nhl.com/ice/schedulebymonth.htm?month=' . $month);
			foreach($month_html->find('.schedTbl tbody tr') as $game){
				if(!$game->class || $game->class == ''){
					if(count($game->find('.skedLinks a')) > 0){
						if(substr($game->find('.skedLinks a')[0]->plaintext,0,5) == 'RECAP' || substr($game->find('.skedLinks a')[0]->plaintext,0,5) == 'PREVI'){
							$game_id = (int)substr($game->find('.skedLinks a')[0]->href,-4);
							$date = date('Y-m-d H:i:s',strtotime($game->find('.date')[0]->first_child()->plaintext . ' ' . substr($game->find('.time .skedStartTimeEST')[0]->plaintext,0,-3)));
							
							$away_team_short = strtolower($game->find('.team a')[0]->rel);
							$away_team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $away_team_short)));
							$home_team_short = strtolower($game->find('.team a')[3]->rel);
							$home_team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $home_team_short)));
							
							$game = array(
								'Team' => array(
									'Team' => array(
										$away_team['Team']['id'],
										$home_team['Team']['id']
									)
								),
								'Game' => array(
									'nhl_id' => $game_id,
									'event_date' => $date,
									'created_at' => date('Y-m-d H:i:s'),
									'season_id' => 1,
									'home_team_id' => $home_team['Team']['id']
								)
							);
							print_r($game);
							$this->Game->saveAll($game,array('deep' => true));
						} else if(substr($game->find('.skedLinks a')[0]->plaintext,0,5) == 'TICKE'){
							$date = date('Y-m-d H:i:s',strtotime($game->find('.date')[0]->first_child()->plaintext . ' ' . substr($game->find('.time .skedStartTimeEST')[0]->plaintext,0,-3)));
							
							$away_team_short = strtolower($game->find('.team a')[0]->rel);
							$away_team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $away_team_short)));
							$home_team_short = strtolower($game->find('.team a')[3]->rel);
							$home_team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $home_team_short)));
							
							$game = array(
								'Team' => array(
									'Team' => array(
										$away_team['Team']['id'],
										$home_team['Team']['id']
									)
								),
								'Game' => array(
									'nhl_id' => null,
									'event_date' => $date,
									'created_at' => date('Y-m-d H:i:s'),
									'season_id' => 1,
									'home_team_id' => $home_team['Team']['id']
								)
							);
							print_r($game);
							$this->Game->saveAll($game,array('deep' => true));
						}
					}
				}
			}
		}
	}
	
	public function getTodaysIds(){
		include_once '../Vendor/simple_html_dom.php';
		
		$month = date('Ym');
		$month_html = file_get_html('http://www.nhl.com/ice/schedulebymonth.htm?month=' . $month);
		foreach($month_html->find('.schedTbl tbody tr') as $game){
			if(!$game->class || $game->class == ''){
				if(count($game->find('.skedLinks a')) > 0){
					if(substr($game->find('.skedLinks a')[0]->plaintext,0,5) == 'PREVI'){
						$game_id = (int)substr($game->find('.skedLinks a')[0]->href,-4);
						$date = date('Y-m-d H:i:s',strtotime($game->find('.date')[0]->first_child()->plaintext . ' ' . substr($game->find('.time .skedStartTimeEST')[0]->plaintext,0,-3)));
						
						$away_team_short = strtolower($game->find('.team a')[0]->rel);
						$away_team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $away_team_short)));
						$home_team_short = strtolower($game->find('.team a')[3]->rel);
						$home_team = $this->Team->find('first',array('conditions' => array('Team.short_name' => $home_team_short)));
						
						$game = $this->Game->find('first',array('conditions' => array(
							'Game.event_date' => $date,
							'Game.home_team_id' => $home_team['Team']['id']
						)));
						$data = array('id' => $game['Game']['id'],'nhl_id' => $game_id);
						$this->Game->save($data);
					}
				}
			}
		}
	}
}
?>