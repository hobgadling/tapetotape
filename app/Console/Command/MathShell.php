<?php
class MathShell extends AppShell{
	public $uses = array('Season','Game','Team','Player','Shift','Shot','Faceoff','Goal','Block','Shot','Assist','TeamStat','TeamGameStat','PlayerStat','PlayerGameStat','GameScore');
	
	public function createTeamRecords(){
		$teams = $this->Team->find('all');
		$situations = array('All','5v5','5v4','4v5','4v4','5v3','3v5','4v3','3v4','3v3');
		$stat = array(
			'TeamStat' => array(
				'cf' => 0,
				'ff' => 0,
				'gf' => 0,
				'ca' => 0,
				'fa' => 0,
				'ga' => 0,
				'cf_p' => 0,
				'ff_p' => 0,
				'gf_p' => 0,
				'sv_p' => 0,
				'sh_p' => 0,
				'pdo' => 0
			),
			'Team' => array()	
		);
		foreach($teams as $team){
			foreach($situations as $situation){
				for($i = 0; $i < 2; $i++){
					$stat['TeamStat']['situation'] = $situation;
					$stat['TeamStat']['close'] = $i;
					$stat['Team']['id'] = $team['Team']['id'];
					$this->TeamStat->saveAssociated($stat);
				}
			}
		}
	}
	
	public function createPlayerRecords(){
		$players = $this->Player->find('all',array('recursive' => -1));
		$situations = array('All','5v5','5v4','4v5','4v4','5v3','3v5','4v3','3v4','3v3');
		$stat = array(
			'PlayerStat' => array(
				'cf' => 0,
				'ff' => 0,
				'gf' => 0,
				'ca' => 0,
				'fa' => 0,
				'ga' => 0,
				'cf_p' => 0,
				'ff_p' => 0,
				'gf_p' => 0,
				'sv_p' => 0,
				'sh_p' => 0,
				'pdo' => 0
			),
			'Player' => array()	
		);
		foreach($players as $player){
			foreach($situations as $situation){
				for($i = 0; $i < 2; $i++){
					$stat['PlayerStat']['situation'] = $situation;
					$stat['PlayerStat']['close'] = $i;
					$stat['Player']['id'] = $player['Player']['id'];
					$this->PlayerStat->saveAssociated($stat);
				}
			}
		}
	}
	
	public function createSingleGameScores(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		$close_array = array();
		$close_array[0] = 1;
		$score = array();
		foreach($game['Team'] as $team){
			$score[$team['id']] = 0;
		}
		foreach($game['Goal'] as $goal){
			$player = $this->Player->find('first',array('conditions' => array('Player.id' => $goal['player_id'])));
			$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $goal['shot_id'])));
			$score[$player['Player']['team_id']]++;
			list($score1) = array_slice($score, 0, 1);
			list($score2) = array_slice($score, 1, 1);
			$diff = abs($score1-$score2);
			if($shot['Shot']['time'] < 40*60 && $diff < 2){
				$close_array[$shot['Shot']['time']] = 1;
			} else if($diff == 0){
				$close_array[$shot['Shot']['time']] = 1;
			} else {
				$close_array[$shot['Shot']['time']] = 0;
			}
		}
		for($i=0;$i<=60*60;$i++){
			if(!isset($close_array[$i])){
				$close_array[$i] = $close_array[$i-1];
			}
			$this->GameScore->create();
			$close_score = array(
				'GameScore' => array('game_id' => $game['Game']['id'],'close' => $close_array[$i],'time' => $i)
			);
			$this->GameScore->save($close_score);
		}
	}
	
	public function isClose($game_id,$time){
		if($time > 60*60){
			return true;
		} else {
			$gamescore = $this->GameScore->find('first',array('conditions' => array('GameScore.game_id' => $game_id,'GameScore.time' => $time),'recursive' => -1));
			return $gamescore['GameScore']['close'] == 1;
		}
	}
	
	public function getSituation($game_id,$time){
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		$time_diff = 9999;
		foreach($game['Shot'] as $shot){
			if(abs($shot['time'] - $time) < $time_diff){
				$time_diff = abs($shot['time'] - $time);
				$situation = $shot['situation'];
			}
		}
		
		return $situation;
	}
	
	public function getTeamSituation($game_id,$time,$team_id){
		$shifts = $this->Shift->find('all',array('conditions' => array('Shift.game_id' => $game_id,'Shift.time_start <' => $time,'Shift.time_end >=' => $time)));
		$curr_team_onice = 0;
		$other_team_onice = 0;
		foreach($shifts as $shift){
			if($shift['Player']['team_id'] == $team_id){
				$curr_team_onice++;
			} else {
				$other_team_onice++;
			}
		}
		
		if($curr_team_onice == 0  || $other_team_onice == 0){
			$shifts = $this->Shift->find('all',array('conditions' => array('Shift.game_id' => $game_id,'Shift.time_start <=' => $time,'Shift.time_end >=' => $time)));
			$curr_team_onice = 0;
			$other_team_onice = 0;
			foreach($shifts as $shift){
				if($shift['Player']['team_id'] == $team_id){
					$curr_team_onice++;
				} else {
					$other_team_onice++;
				}
			}
		}
		return ($curr_team_onice-1).'v'.($other_team_onice-1);
	}
	
	public function onIce($player_id,$game_id,$time){
		$shift = $this->Shift->find('first',array('conditions' => array(
			'Shift.player_id' => $player_id,
			'Shift.game_id' => $game_id,
			'Shift.time_start <' => $time,
			'Shift.time_end >=' => $time
		),'recursive' => -1));
		
		return !!$shift;
	}
	
	public function getTeamGameBySituation($game_id = 0,$team_id = 0,$situation = 0,$close = -1){
		if($game_id == 0){
			$game_id = $this->args[0];
		}
		if($team_id == 0){
			$team_id = $this->args[1];
		}
		if($situation == 0){
			$situation = $this->args[2];
		}
		if($close = -1){
			$close = $this->args[3];
		}
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id),'recursive' => 2));
		
		$cf = 0;
		$ff = 0;
		$gf = 0;
		$sf = 0;
		
		$ca = 0;
		$fa = 0;
		$ga = 0;
		$sa = 0;
		
		$dnz_a2sag = 0;
		$oz_a2sag = 0;
		$a2sag = 0;
		$dnz_a2sg = 0;
		$oz_a2sg = 0;
		$a2sg = 0;
		$dnz_sag = 0;
		$oz_sag = 0;
		$sc_sag = 0;
		$sag = 0;
		$dnz_sg = 0;
		$oz_sg = 0;
		$sc_sg = 0;
		$sg = 0;
		
		$sagf = 0;
		$saga = 0;
		$sagf_p = 0;
		
		foreach($game['Shot'] as $shot){
			if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
				if($shot['Player']['team_id'] == $team_id){
					if($situation == 'All'){
						$cf++;
						if($shot['type'] != 'Blocked'){
							$ff++;
						}
						if($shot['type'] == 'On Goal'){
							$sf++;
						}
					} else if($shot['situation'] == $situation){
						$cf++;
						if($shot['type'] != 'Blocked'){
							$ff++;
						}
						if($shot['type'] == 'On Goal'){
							$sf++;
						}
					}
				} else {
					if($situation == 'All'){
						$ca++;
						if($shot['type'] != 'Blocked'){
							$fa++;
						}
						if($shot['type'] == 'On Goal'){
							$sa++;
						}	
					} else if($shot['situation'] == $situation){
						$ca++;
						if($shot['type'] != 'Blocked'){
							$fa++;
						}
						if($shot['type'] == 'On Goal'){
							$sa++;
						}
					}
				}
			}
		}
		
		foreach($game['Goal'] as $goal){
			if($close == 0 || $this->isClose($goal['Shot']['game_id'],$goal['Shot']['time'])){
				if($goal['Player']['team_id'] == $team_id){
					$gf++;
				} else {
					$ga++;
				}
			}
		}
		
		if($situation == 'All' || $situation == '5v5'){
			foreach($game['Pass'] as $pass){
				if($close == 0 || $this->isClose($pass['Shot']['game_id'],$pass['Shot']['time'])){
					if($pass['Player']['team_id'] == $team_id){
						switch($pass['location']){
							case 'D/NZ':
								if($pass['type'] == 'SG'){
									if($pass['order'] == 'A'){
										$dnz_sg++;
										$sg++;
									} else {
										$dnz_a2sg++;
										$a2sg++;
									}
								} else {
									if($pass['order'] == 'A'){
										$dnz_sag++;
										$sag++;
										$sagf++;
									} else {
										$dnz_a2sag++;
										$a2sag++;
									}
								}
								break;
							case 'OZ':
								if($pass['type'] == 'SG'){
									if($pass['order'] == 'A'){
										$oz_sg++;
										$sg++;
									} else {
										$oz_a2sg++;
										$a2sg++;
									}
								} else {
									if($pass['order'] == 'A'){
										$oz_sag++;
										$sag++;
										$sagf++;
									} else {
										$oz_a2sag++;
										$a2sag++;
									}
								}
								break;
							case 'SC':
								if($pass['type'] == 'SG'){
									if($pass['order'] == 'A'){
										$sc_sg++;
										$sg++;
									} else {
										$a2sg++;
									}
								} else {
									if($pass['order'] == 'A'){
										$sc_sag++;
										$sag++;
										$sagf++;
									} else {
										$a2sag++;
									}
								}
								break;
						}
					} else {
						switch($pass['location']){
							case 'D/NZ':
								if($pass['type'] == 'SG'){
									
								} else {
									if($pass['order'] == 'A'){
										$saga++;
									}
								}
								break;
							case 'OZ':
								if($pass['type'] == 'SG'){
									
								} else {
									if($pass['order'] == 'A'){
										$saga++;
									}
								}
								break;
							case 'SC':
								if($pass['type'] == 'SG'){
									
								} else {
									if($pass['order'] == 'A'){
										$saga++;
									}
								}
								break;
						}
					}
					
				}
			}
		}
		
		$cf_p = number_format($cf/($cf + $ca) * 100,2);
		$ff_p = number_format($ff/($ff + $fa) * 100,2);
		$gf_p = number_format($gf/($gf + $ga) * 100,2);
		
		$sv_p = number_format((($sa - $ga)/$sa) * 100,2);
		$sh_p = number_format($gf/$sf * 100,2);
		$pdo = $sv_p + $sh_p;
		
		$a2_sage = 0;
		$dnz_sage = 0;
		$oz_sage = 0;
		$sc_sage = 0;
		$sage = 0;
		
		if($situation == 'All' || $situation == '5v5'){
			$a2_sage = number_format(($a2sag/$a2sg) * 100,2);
			$dnz_sage = number_format(($dnz_sag/$dnz_sg) * 100,2);
			$oz_sage = number_format(($oz_sag/$oz_sg) * 100,2);
			$sc_sage = number_format(($sc_sag/$sc_sg) * 100,2);
			$sage = number_format(($sag/$sg) * 100,2);
			$sagf_p = number_format(($sagf/($sagf + $saga)) * 100,2);
		}
		
		$stats = array(
			'team_id' => $team_id,
			'game_id' => $game_id,
			'situation' => $situation,
			'close' => $close,
			'cf' => $cf,
			'ca' => $ca,
			'cf_p' => $cf_p,
			'ff' => $ff,
			'fa' => $fa,
			'ff_p' => $ff_p,
			'gf' => $gf,
			'ga' => $ga,
			'gf_p' => $gf_p,
			'sv_p' => $sv_p,
			'sh_p' => $sh_p,
			'pdo' => $pdo,
			'dnz_a2sag' => $dnz_a2sag,
			'oz_a2sag' => $oz_a2sg,
			'a2sag' => $a2sag,
			'dnz_a2sg' => $dnz_a2sg,
			'oz_a2sg' => $oz_a2sg,
			'a2sg' => $a2sg,
			'dnz_sag' => $dnz_sag,
			'oz_sag' => $oz_sag,
			'sc_sag' => $sc_sag,
			'sag' => $sag,
			'dnz_sg' => $dnz_sg,
			'oz_sg' => $oz_sg,
			'sc_sg' => $sc_sg,
			'sg' => $sg,
			'a2_sage' => $a2_sage,
			'dnz_sage' => $dnz_sage,
			'oz_sage' => $oz_sage,
			'sc_sage' => $sc_sage,
			'sage' => $sage,
			'sagf' => $sagf,
			'saga' => $saga,
			'sagf_p' => $sagf_p
		);
		$this->TeamGameStat->create();
		$this->TeamGameStat->save($stats);
	}
	
	public function getTeamAggregateBySituation($team_id = 0,$situation = 0,$close = -1){
		if($team_id == 0){
			$team_id = $this->args[0];
		}
		if($situation == 0){
			$situation = $this->args[1];
		}
		if($close = -1){
			$close = $this->args[2];
		}
		$team = $this->Team->find('first',array('conditions' => array('Team.id' => $team_id),'recursive' => 2));
		$stat = $this->TeamStat->find('first',array('conditions' => array('TeamStat.team_id' => $team_id,'TeamStat.situation' => $situation,'TeamStat.close' => $close),'recursive' => -1));
		$cf = 0;
		$ff = 0;
		$gf = 0;
		$sf = 0;
		
		$dnz_a2sag = 0;
		$oz_a2sag = 0;
		$a2sag = 0;
		$dnz_a2sg = 0;
		$oz_a2sg = 0;
		$a2sg = 0;
		$dnz_sag = 0;
		$oz_sag = 0;
		$sc_sag = 0;
		$sag = 0;
		$dnz_sg = 0;
		$oz_sg = 0;
		$sc_sg = 0;
		$sg = 0;
		$sagf = 0;
		$saga = 0;
		$sagf_p = 0;
		
		$player_ids = array();
		foreach($team['Player'] as $player){
			foreach($player['Shot'] as $shot){
				if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
					if($situation == 'All'){
						$cf++;
						if($shot['type'] != 'Blocked'){
							$ff++;
						}
						if($shot['type'] == 'On Goal'){
							$sf++;
						}
					} else if($shot['situation'] == $situation){
						$cf++;
						if($shot['type'] != 'Blocked'){
							$ff++;
						}
						if($shot['type'] == 'On Goal'){
							$sf++;
						}
					}
				}
			}
			foreach($player['Goal'] as $goal){
				$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $goal['shot_id'])));
				if($close == 0 || $this->isClose($shot['Shot']['game_id'],$shot['Shot']['time'])){
					if($situation == 'All'){
						$gf++;
					} else if($shot['Shot']['situation'] == $situation){
						$gf++;
					}
				}
			}
			
			if($situation == 'All' || $situation == '5v5'){
				foreach($player['Pass'] as $pass){
					$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $pass['shot_id'])));
					if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
						if($player['team_id'] == $team_id){
							switch($pass['location']){
								case 'D/NZ':
									if($pass['type'] == 'SG'){
										if($pass['order'] == 'A'){
											$dnz_sg++;
											$sg++;
										} else {
											$dnz_a2sg++;
											$a2sg++;
										}
									} else {
										if($pass['order'] == 'A'){
											$dnz_sag++;
											$sag++;
											$sagf++;
										} else {
											$dnz_a2sag++;
											$a2sag++;
										}
									}
									break;
								case 'OZ':
									if($pass['type'] == 'SG'){
										if($pass['order'] == 'A'){
											$oz_sg++;
											$sg++;
										} else {
											$oz_a2sg++;
											$a2sg++;
										}
									} else {
										if($pass['order'] == 'A'){
											$oz_sag++;
											$sag++;
											$sagf++;
										} else {
											$oz_a2sag++;
											$a2sag++;
										}
									}
									break;
								case 'SC':
									if($pass['type'] == 'SG'){
										if($pass['order'] == 'A'){
											$sc_sg++;
											$sg++;
										} else {
											$a2sg++;
										}
									} else {
										if($pass['order'] == 'A'){
											$sc_sag++;
											$sag++;
											$sagf++;
										} else {
											$a2sag++;
										}
									}
									break;
							}
						} else {
							switch($pass['location']){
								case 'D/NZ':
									if($pass['type'] == 'SG'){
									} else {
										if($pass['order'] == 'A'){
											$saga++;
										}
									}
									break;
								case 'OZ':
									if($pass['type'] == 'SG'){
										
									} else {
										if($pass['order'] == 'A'){
											$saga++;
										}
									}
									break;
								case 'SC':
									if($pass['type'] == 'SG'){
										
									} else {
										if($pass['order'] == 'A'){
											$saga++;
										}
									}
									break;
							}
						}
					}
				}
			}
			
			$player_ids[] = $player['id'];
		}
		
		$ca = 0;
		$fa = 0;
		$ga = 0;
		$sa = 0;
		foreach($team['Game'] as $game){
			foreach($game['Shot'] as $shot){
				if(!in_array($shot['player_id'], $player_ids)){
					if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
						if($situation == 'All'){
							$ca++;
							if($shot['type'] != 'Blocked'){
								$fa++;
							}
							if($shot['type'] == 'On Goal'){
								$sa++;
							}	
						} else if($shot['situation'] == $situation){
							$ca++;
							if($shot['type'] != 'Blocked'){
								$fa++;
							}
							if($shot['type'] == 'On Goal'){
								$sa++;
							}
						}
					}
				}
			}
			
			foreach($game['Goal'] as $goal){
				$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $goal['shot_id'])));
				if(!in_array($shot['Shot']['player_id'], $player_ids)){
					
					if($close == 0 || $this->isClose($shot['Shot']['game_id'],$shot['Shot']['time'])){
						if($situation == 'All'){
							$ga++;
						} else if($shot['Shot']['situation'] == $situation){
							$ga++;
						}
					}
				}
			}
		}
		
		$cf_p = number_format($cf/($cf + $ca) * 100,2);
		$ff_p = number_format($ff/($ff + $fa) * 100,2);
		$gf_p = number_format($gf/($gf + $ga) * 100,2);
		
		$sv_p = number_format((($sa - $ga)/$sa) * 100,2);
		$sh_p = number_format($gf/$sf * 100,2);
		$pdo = $sv_p + $sh_p;
		
		$a2_sage = 0;
		$dnz_sage = 0;
		$oz_sage = 0;
		$sc_sage = 0;
		$sage = 0;
		
		if($situation == 'All' || $situation == '5v5'){
			$a2_sage = number_format(($a2sag/$a2sg) * 100,2);
			$dnz_sage = number_format(($dnz_sag/$dnz_sg) * 100,2);
			$oz_sage = number_format(($oz_sag/$oz_sg) * 100,2);
			$sc_sage = number_format(($sc_sag/$sc_sg) * 100,2);
			$sage = number_format(($sag/$sg) * 100,2);
			$sagf_p = number_format(($sagf/($sagf + $saga)) * 100,2);
		}
		
		$this->TeamStat->read(null,$stat['TeamStat']['id']);
		$stats = array(
				'cf' => $cf,
				'ca' => $ca,
				'cf_p' => $cf_p,
				'ff' => $ff,
				'fa' => $fa,
				'ff_p' => $ff_p,
				'gf' => $gf,
				'ga' => $ga,
				'gf_p' => $gf_p,
				'sv_p' => $sv_p,
				'sh_p' => $sh_p,
				'pdo' => $pdo,
				'dnz_a2sag' => $dnz_a2sag,
				'oz_a2sag' => $oz_a2sg,
				'a2sag' => $a2sag,
				'dnz_a2sg' => $dnz_a2sg,
				'oz_a2sg' => $oz_a2sg,
				'a2sg' => $a2sg,
				'dnz_sag' => $dnz_sag,
				'oz_sag' => $oz_sag,
				'sc_sag' => $sc_sag,
				'sag' => $sag,
				'dnz_sg' => $dnz_sg,
				'oz_sg' => $oz_sg,
				'sc_sg' => $sc_sg,
				'sg' => $sg,
				'a2_sage' => $a2_sage,
				'dnz_sage' => $dnz_sage,
				'oz_sage' => $oz_sage,
				'sc_sage' => $sc_sage,
				'sage' => $sage,
				'sagf' => $sagf,
				'saga' => $saga,
				'sagf_p' => $sagf_p
			);
		$this->TeamStat->set($stats);
		$this->TeamStat->save();
	}
	
	public function getPlayerAggregateBySituation($player_id = 0,$situation = 0,$close = -1){
		if($player_id == 0){
			$player_id = $this->args[0];
		}
		if($situation == 0){
			$situation = $this->args[1];
		}
		if($close = -1){
			$close = $this->args[2];
		}
		$player = $this->Player->find('first',array('conditions' => array('Player.id' => $player_id),'recursive' => -1));
		$team = $this->Team->find('first',array('conditions' => array('Team.id' => $player['Player']['team_id']),'recursive' => 2));
		$stat = $this->PlayerStat->find('first',array('conditions' => array('PlayerStat.player_id' => $player_id,'PlayerStat.situation' => $situation,'PlayerStat.close' => $close)));
		$cf = 0;
		$ff = 0;
		$gf = 0;
		$sf = 0;
		$shots = 0;
		$icorsi = 0;
		$toi = 0;
		
		$dnz_a2sag = 0;
		$oz_a2sag = 0;
		$a2sag = 0;
		$dnz_a2sg = 0;
		$oz_a2sg = 0;
		$a2sg = 0;
		$dnz_sag = 0;
		$oz_sag = 0;
		$sc_sag = 0;
		$sag = 0;
		$dnz_sg = 0;
		$oz_sg = 0;
		$sc_sg = 0;
		$sg = 0;
		$sagf = 0;
		$saga = 0;
		$sagf_p = 0;
		
		foreach($player['Shift'] as $shift){
			if($this->getSituation($shift['game_id'],$shift['time_start']) == $situation || $situation == 'All'){
				if($close == 0 || $this->isClose($shift['game_id'],$shift['time_start'])){
					$toi += $shift['time_end'] - $shift['time_start'];
				}
			}
		}
		
		$toi = $toi / count($player['Game']);
		
		$player_ids = array();
		foreach($team['Player'] as $player){
			foreach($player['Shot'] as $shot){
				if($player['id'] == $player_id){
					$icorsi++;
					if($shot['type'] == 'On Goal'){
						$shots++;
					}
				}
				if($this->onIce($player_id,$shot['game_id'],$shot['time'])){
					if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
						if($situation == 'All'){
							$cf++;
							if($shot['type'] != 'Blocked'){
								$ff++;
							}
							if($shot['type'] == 'On Goal'){
								$sf++;
							}
						} else if($shot['situation'] == $situation){
							$cf++;
							if($shot['type'] != 'Blocked'){
								$ff++;
							}
							if($shot['type'] == 'On Goal'){
								$sf++;
							}
						}
					}
				}
			}
			foreach($player['Goal'] as $goal){
				$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $goal['shot_id'])));
				if($this->onIce($player_id,$shot['Shot']['game_id'],$shot['Shot']['time'])){
					if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
						if($situation == 'All'){
							$gf++;
						} else if($shot['Shot']['situation'] == $situation){
							$gf++;
						}
					}
				}
			}
			
			if($situation == 'All' || $situation == '5v5'){
				foreach($player['Pass'] as $pass){
					if($pass['player_id'] == $player_id){
						$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $pass['shot_id'])));
						if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
							switch($pass['location']){
								case 'D/NZ':
									if($pass['type'] == 'SG'){
										if($pass['order'] == 'A'){
											$dnz_sg++;
											$sg++;
										} else {
											$dnz_a2sg++;
											$a2sg++;
										}
									} else {
										if($pass['order'] == 'A'){
											$dnz_sag++;
											$sag++;
											$sagf++;
										} else {
											$dnz_a2sag++;
											$a2sag++;
										}
									}
									break;
								case 'OZ':
									if($pass['type'] == 'SG'){
										if($pass['order'] == 'A'){
											$oz_sg++;
											$sg++;
										} else {
											$oz_a2sg++;
											$a2sg++;
										}
									} else {
										if($pass['order'] == 'A'){
											$oz_sag++;
											$sag++;
											$sagf++;
										} else {
											$oz_a2sag++;
											$a2sag++;
										}
									}
									break;
								case 'SC':
									if($pass['type'] == 'SG'){
										if($pass['order'] == 'A'){
											$sc_sg++;
											$sg++;
										} else {
											$a2sg++;
										}
									} else {
										if($pass['order'] == 'A'){
											$sc_sag++;
											$sag++;
											$sagf++;
										} else {
											$a2sag++;
										}
									}
									break;
							}
						}
					}
				}
				foreach($player['Game'] as $game){
					foreach($game['Pass'] as $pass){
						if($pass['player_id'] != $player_id && $this->onIce($player_id,$game['id'],$pass['Shot']['time'])){
							if($pass['type'] == 'SG' && $pass['order'] == 'A'){
								$saga++;
							}
						}
					}
				}
			}
			
			$player_ids[] = $player['id'];
		}
		
		$ca = 0;
		$fa = 0;
		$ga = 0;
		$sa = 0;
		foreach($team['Game'] as $game){
			foreach($game['Shot'] as $shot){
				if($this->onIce($player_id,$shot['game_id'],$shot['time'])){
					if(!in_array($shot['player_id'], $player_ids)){
						if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
							if($situation == 'All'){
								$ca++;
								if($shot['type'] != 'Blocked'){
									$fa++;
								}
								if($shot['type'] == 'On Goal'){
									$sa++;
								}	
							} else if($shot['situation'] == $situation){
								$ca++;
								if($shot['type'] != 'Blocked'){
									$fa++;
								}
								if($shot['type'] == 'On Goal'){
									$sa++;
								}
							}
						}
					}
				}
			}
			
			foreach($game['Goal'] as $goal){
				$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $goal['shot_id'])));
				if(!in_array($shot['Shot']['player_id'], $player_ids)){
					
					if($this->onIce($player_id,$shot['Shot']['game_id'],$shot['Shot']['time'])){
						if($close == 0 || $this->isClose($shot['Shot']['game_id'],$shot['Shot']['time'])){
							if($situation == 'All'){
								$ga++;
							} else if($shot['Shot']['situation'] == $situation){
								$ga++;
							}
						}
					}
				}
			}
		}
		
		$cf_p = number_format($cf/($cf + $ca) * 100,2);
		$ff_p = number_format($ff/($ff + $fa) * 100,2);
		$gf_p = number_format($gf/($gf + $ga) * 100,2);
		
		$sv_p = number_format((($sa - $ga)/$sa) * 100,2);
		$sh_p = number_format($gf/$sf * 100,2);
		$pdo = $sv_p + $sh_p;
		
		$a2_sage = 0;
		$dnz_sage = 0;
		$oz_sage = 0;
		$sc_sage = 0;
		$sage = 0;
		
		if($situation == 'All' || $situation == '5v5'){
			$a2_sage = number_format(($a2sag/$a2sg) * 100,2);
			$dnz_sage = number_format(($dnz_sag/$dnz_sg) * 100,2);
			$oz_sage = number_format(($oz_sag/$oz_sg) * 100,2);
			$sc_sage = number_format(($sc_sag/$sc_sg) * 100,2);
			$sage = number_format(($sag/$sg) * 100,2);
			$sagf_p = number_format(($sagf/($sagf + $saga)) * 100,2);
		}
		
		$this->PlayerStat->read(null,$stat['PlayerStat']['id']);
		$stats = array(
				'cf' => $cf,
				'ca' => $ca,
				'cf_p' => $cf_p,
				'ff' => $ff,
				'fa' => $fa,
				'ff_p' => $ff_p,
				'gf' => $gf,
				'ga' => $ga,
				'gf_p' => $gf_p,
				'sv_p' => $sv_p,
				'sh_p' => $sh_p,
				'pdo' => $pdo,
				'dnz_a2sag' => $dnz_a2sag,
				'oz_a2sag' => $oz_a2sg,
				'a2sag' => $a2sag,
				'dnz_a2sg' => $dnz_a2sg,
				'oz_a2sg' => $oz_a2sg,
				'a2sg' => $a2sg,
				'dnz_sag' => $dnz_sag,
				'oz_sag' => $oz_sag,
				'sc_sag' => $sc_sag,
				'sag' => $sag,
				'dnz_sg' => $dnz_sg,
				'oz_sg' => $oz_sg,
				'sc_sg' => $sc_sg,
				'sg' => $sg,
				'a2_sage' => $a2_sage,
				'dnz_sage' => $dnz_sage,
				'oz_sage' => $oz_sage,
				'sc_sage' => $sc_sage,
				'sage' => $sage,
				'shots' => $shots,
				'icorsi' => $icorsi,
				'toi' => $toi,
				'sagf' => $sagf,
				'saga' => $saga,
				'sagf_p' => $sagf_p
			);
		$this->PlayerStat->set($stats);
		$this->PlayerStat->save();
	}
	
	public function getPlayerGameBySituation($game_id = 0,$player_id = 0,$situation = 0,$close = 0){
		if($game_id == 0){
			$game_id = $this->args[0];
		}
		if($player_id == 0){
			$player_id = $this->args[1];
		}
		if($situation == 0){
			$situation = $this->args[2];
		}
		if($close = -1){
			$close = $this->args[3];
		}
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id),'recursive' => 2));
		$player = $this->Player->find('first',array('conditions' => array('Player.id' => $player_id),'recursive' => -1));
		$team_id = $player['Player']['team_id'];
		
		$cf = 0;
		$ff = 0;
		$gf = 0;
		$sf = 0;
		
		$ca = 0;
		$fa = 0;
		$ga = 0;
		$sa = 0;
		
		$icorsi = 0;
		$shots = 0;
		$toi = 0;
		
		$dnz_a2sag = 0;
		$oz_a2sag = 0;
		$a2sag = 0;
		$dnz_a2sg = 0;
		$oz_a2sg = 0;
		$a2sg = 0;
		$dnz_sag = 0;
		$oz_sag = 0;
		$sc_sag = 0;
		$sag = 0;
		$dnz_sg = 0;
		$oz_sg = 0;
		$sc_sg = 0;
		$sg = 0;
		$sagf = 0;
		$saga = 0;
		$sagf_p = 0;
		
		foreach($game['Shift'] as $shift){
			if($shift['player_id'] == $player_id){
				if($this->getSituation($shift['game_id'],$shift['time_start']) == $situation || $situation == 'All'){
					if($close == 0 || $this->isClose($shift['game_id'],$shift['time_start'])){
						$toi += $shift['time_end'] - $shift['time_start'];
					}
				}
			}
		}
		
		foreach($game['Shot'] as $shot){
			if($shot['player_id'] == $player_id){
				$icorsi++;
				if($shot['type'] == 'On Goal'){
					$shots++;
				}
			}
			if($this->onIce($player_id,$game_id,$shot['time'])){
				if($close == 0 || $this->isClose($shot['game_id'],$shot['time'])){
					if($shot['Player']['team_id'] == $team_id){
						if($situation == 'All'){
							$cf++;
							if($shot['type'] != 'Blocked'){
								$ff++;
							}
							if($shot['type'] == 'On Goal'){
								$sf++;
							}
						} else if($shot['situation'] == $situation){
							$cf++;
							if($shot['type'] != 'Blocked'){
								$ff++;
							}
							if($shot['type'] == 'On Goal'){
								$sf++;
							}
						}
					} else {
						if($situation == 'All'){
							$ca++;
							if($shot['type'] != 'Blocked'){
								$fa++;
							}
							if($shot['type'] == 'On Goal'){
								$sa++;
							}	
						} else if($shot['situation'] == $situation){
							$ca++;
							if($shot['type'] != 'Blocked'){
								$fa++;
							}
							if($shot['type'] == 'On Goal'){
								$sa++;
							}
						}
					}
				}
			}
		}
		
		foreach($game['Goal'] as $goal){
			if($this->onIce($player_id,$game_id,$goal['Shot']['time'])){
				if($close == 0 || $this->isClose($goal['Shot']['game_id'],$goal['Shot']['time'])){
					if($goal['Player']['team_id'] == $team_id){
						$gf++;
					} else {
						$ga++;
					}
				}
			}
		}
		
		if($situation == 'All' || $situation == '5v5'){
			foreach($game['Pass'] as $pass){
				if($pass['player_id'] == $player_id){
					if($close == 0 || $this->isClose($pass['Shot']['game_id'],$pass['Shot']['time'])){
						switch($pass['location']){
							case 'D/NZ':
								if($pass['type'] == 'SG'){
									if($pass['order'] == 'A'){
										$dnz_sg++;
										$sg++;
									} else {
										$dnz_a2sg++;
										$a2sg++;
									}
								} else {
									if($pass['order'] == 'A'){
										$dnz_sag++;
										$sag++;
										$sagf++;
									} else {
										$dnz_a2sag++;
										$a2sag++;
									}
								}
								break;
							case 'OZ':
								if($pass['type'] == 'SG'){
									if($pass['order'] == 'A'){
										$oz_sg++;
										$sg++;
									} else {
										$oz_a2sg++;
										$a2sg++;
									}
								} else {
									if($pass['order'] == 'A'){
										$oz_sag++;
										$sag++;
										$saga++;
									} else {
										$oz_a2sag++;
										$a2sag++;
									}
								}
								break;
							case 'SC':
								if($pass['type'] == 'SG'){
									if($pass['order'] == 'A'){
										$sc_sg++;
										$sg++;
									} else {
										$a2sg++;
									}
								} else {
									if($pass['order'] == 'A'){
										$sc_sag++;
										$sag++;
										$saga++;
									} else {
										$a2sag++;
									}
								}
								break;
						}
					}
				} else {
					if($close == 0 || $this->isClose($pass['Shot']['game_id'],$pass['Shot']['time'])){
						if($this->onIce($player_id,$game['id'],$pass['Shot']['time'])){
							if($pass['type'] == 'SG' && $pass['order'] == 'A'){
								$saga++;
							}
						}
					}
				}
			}
		}
		
		$cf_p = number_format($cf/($cf + $ca) * 100,2);
		$ff_p = number_format($ff/($ff + $fa) * 100,2);
		$gf_p = number_format($gf/($gf + $ga) * 100,2);
		
		$sv_p = number_format((($sa - $ga)/$sa) * 100,2);
		$sh_p = number_format($gf/$sf * 100,2);
		$pdo = $sv_p + $sh_p;
		
		$a2_sage = 0;
		$dnz_sage = 0;
		$oz_sage = 0;
		$sc_sage = 0;
		$sage = 0;
		
		if($situation == 'All' || $situation == '5v5'){
			$a2_sage = number_format(($a2sag/$a2sg) * 100,2);
			$dnz_sage = number_format(($dnz_sag/$dnz_sg) * 100,2);
			$oz_sage = number_format(($oz_sag/$oz_sg) * 100,2);
			$sc_sage = number_format(($sc_sag/$sc_sg) * 100,2);
			$sage = number_format(($sag/$sg) * 100,2);
			$sagf_p = number_format(($sagf/($sagf + $saga)) * 100,2);
		}
		
		$stats = array(
			'player_id' => $player_id,
			'game_id' => $game_id,
			'situation' => $situation,
			'close' => $close,
			'cf' => $cf,
			'ca' => $ca,
			'cf_p' => $cf_p,
			'ff' => $ff,
			'fa' => $fa,
			'ff_p' => $ff_p,
			'gf' => $gf,
			'ga' => $ga,
			'gf_p' => $gf_p,
			'sv_p' => $sv_p,
			'sh_p' => $sh_p,
			'pdo' => $pdo,
			'dnz_a2sag' => $dnz_a2sag,
			'oz_a2sag' => $oz_a2sg,
			'a2sag' => $a2sag,
			'dnz_a2sg' => $dnz_a2sg,
			'oz_a2sg' => $oz_a2sg,
			'a2sg' => $a2sg,
			'dnz_sag' => $dnz_sag,
			'oz_sag' => $oz_sag,
			'sc_sag' => $sc_sag,
			'sag' => $sag,
			'dnz_sg' => $dnz_sg,
			'oz_sg' => $oz_sg,
			'sc_sg' => $sc_sg,
			'sg' => $sg,
			'a2_sage' => $a2_sage,
			'dnz_sage' => $dnz_sage,
			'oz_sage' => $oz_sage,
			'sc_sage' => $sc_sage,
			'sage' => $sage,
			'icorsi' => $icorsi,
			'shots' => $shots,
			'toi' => $toi,
			'sagf' => $sagf,
			'saga' => $saga,
			'sagf_p' => $sagf_p
		);
		$this->PlayerGameStat->create();
		$this->PlayerGameStat->save($stats);
	}
	
	public function getFullGameStats(){
		$game_id = $this->args[0];
		$situations = array('All','5v5','5v4','4v4','5v3','4v3','3v3');
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($situations as $situation){
			foreach($game['Team'] as $team){
				for($i = 0;$i<2;$i++){
					$this->getTeamGameBySituation($game_id,$team['id'],$situation,$i);
				}
			}
			foreach($game['Player'] as $player){
				for($i=0;$i<2;$i++){
					$this->getPlayerGameBySituation($game_id,$player['id'],$situation,$i);
				}
			}
		}
	}
	
	public function getSeasonStats(){
		$situations = array('All','5v5','5v4','4v4','5v3','4v3','3v3');
		$teams = $this->Team->find('all');
		foreach($situations as $situation){
			foreach($teams as $team){
				for($i=0;$i<2;$i++){
					$this->getTeamAggregateBySituation($team['Team']['id'],$situation,$i);
				}
			}
		}
	}
	
	public function main(){
		$games = $this->Game->find('all',array('conditions' => array('Game.nhl_id >' => 0)));
		foreach($games as $game){
			$this->getFullGameStats($game['Game']['id']);
		}
	}
	
	public function getFullGameStatsBySituation($game_id,$situation){
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Team'] as $team){
			for($i = 0;$i<2;$i++){
				$this->getTeamGameBySituation($game_id,$team['id'],$situation,$i);
			}
		}
		foreach($game['Player'] as $player){
			for($i=0;$i<2;$i++){
				$this->getPlayerGameBySituation($game_id,$player['id'],$situation,$i);
			}
		}
	}
	
	public function recheckShotSituations(){
		$game_id = $this->args[0];	
		$shots = $this->Shot->find('all',array('conditions' => array('Shot.game_id' => $game_id)));
		foreach($shots as $shot){
			$situation = $this->getTeamSituation($shot['Shot']['game_id'],$shot['Shot']['time'],$shot['Player']['team_id']);
			$this->Shot->read(null,$shot['Shot']['id']);
			$this->Shot->set('situation',$situation);
			$this->Shot->save();
		}
	}
	
	public function getOnIcePlayers($game_id,$time){
		$shifts = $this->Shift->find('all',array('conditions' => array('Shift.game_id' => $game_id,'Shift.time_start <' => $time,'Shift.time_end >=' => $time)));
		$player_ids = array();
		foreach($shifts as $shift){
			$player_ids[] = $shift['Player']['id'];
		}
		return $player_ids;
	}
	
	public function getRawPlayerShotStatsByGame(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Shot'] as $shot){
			$shot_player = $this->Player->find('first',array('conditions' => array('Player.id' => $shot['player_id']),'recursive' => -1));
			$shot_team_id = $shot_player['Player']['team_id'];
			if($shot['type'] == 'Blocked'){
				$kind = 'c';
			} else {
				$kind = 'f';
			}
			if($this->isClose($game_id,$shot['time'])){
				$close = 1;
			} else {
				$close = 0;
			}
			$players = $this->getOnIcePlayers($game_id,$shot['time']);
			foreach($players as $player_id){
				$player = $this->Player->find('first',array('conditions' => array('Player.id' => $player_id),'recursive' => -1));
				$situation = $this->getTeamSituation($game_id,$shot['time'],$player['Player']['team_id']);
				$player_game_stat = $this->PlayerGameStat->find('first',array('conditions' => array('PlayerGameStat.player_id' => $player_id,'PlayerGameStat.game_id' => $game_id,'PlayerGameStat.situation' => $situation,'PlayerGameStat.close' => $close),'recursive' => -1));
				if(!$player_game_stat){
					$this->PlayerGameStat->create();
					$data = array(
						'player_id' => $player_id,
						'game_id' => $game_id,
						'situation' => $situation,
						'close' => $close
					);
					if($player['Player']['team_id'] == $shot_team_id){
						$data['cf'] = 1;
						if($kind == 'f'){
							$data['ff'] = 1;
						}
						if($shot['player_id'] == $player_id){
							$data['icorsi'] = 1;
							if($shot['type'] == 'On Goal'){
								$data['shots'] = 1;
							}
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['gf'] = 1;
						}
					} else {
						$data['ca'] = 1;
						if($kind == 'f'){
							$data['fa'] = 1;
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['ga'] = 1;
						}
					}
					$this->PlayerGameStat->set($data);
				} else {
					$this->PlayerGameStat->read(null,$player_game_stat['PlayerGameStat']['id']);
					if($player['Player']['team_id'] == $shot_team_id){
						$data['cf'] = $player_game_stat['PlayerGameStat']['cf']+1;
						if($kind == 'f'){
							$data['ff'] = $player_game_stat['PlayerGameStat']['ff'] + 1;
						}
						if($shot['player_id'] == $player_id){
							$data['icorsi'] = $player_game_stat['PlayerGameStat']['icorsi'] + 1;
							if($shot['type'] == 'On Goal'){
								$data['shots'] = $player_game_stat['PlayerGameStat']['shots'] + 1;
							}
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['gf'] = $player_game_stat['PlayerGameStat']['gf'] + 1;
						}
					} else {
						$data['ca'] = $player_game_stat['PlayerGameStat']['ca'] + 1;
						if($kind == 'f'){
							$data['fa'] = $player_game_stat['PlayerGameStat']['fa'] + 1;
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['ga'] = $player_game_stat['PlayerGameStat']['ga'] + 1;
						}
					}
					$this->PlayerGameStat->set($data);
				}
				$this->PlayerGameStat->save();
			}
		}
	}
	
	public function getRawTeamShotStatsByGame(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Shot'] as $shot){
			$this->TeamGameStat->clear();
			$shot_player = $this->Player->find('first',array('conditions' => array('Player.id' => $shot['player_id']),'recursive' => -1));
			$shot_team_id = $shot_player['Player']['team_id'];
			if($shot['type'] == 'Blocked'){
				$kind = 'c';
			} else {
				$kind = 'f';
			}
			if($this->isClose($game_id,$shot['time'])){
				$close = 1;
			} else {
				$close = 0;
			}
			
			foreach($game['Team'] as $team){
				$data = array();
				$situation = $this->getTeamSituation($game_id,$shot['time'],$team['id']);
				$this->TeamGameStat->clear();
				$team_game_stat = $this->TeamGameStat->find('first',array('conditions' => array('TeamGameStat.team_id' => $team['id'],'TeamGameStat.game_id' => $game_id,'TeamGameStat.situation' => $situation,'TeamGameStat.close' => $close),'recursive' => -1));
				
				if(!$team_game_stat){
					$this->TeamGameStat->create();
					$data = array(
						'team_id' => $team['id'],
						'game_id' => $game_id,
						'situation' => $situation,
						'close' => $close
					);
					if($team['id'] == $shot_team_id){
						$data['cf'] = 1;
						if($kind == 'f'){
							$data['ff'] = 1;
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['gf'] = 1;
						}
					} else {
						$data['ca'] = 1;
						if($kind == 'f'){
							$data['fa'] = 1;
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['ga'] = 1;
						}
					}
					//$this->TeamGameStat->set($data);
				} else {
					$this->TeamGameStat->read(null,$team_game_stat['TeamGameStat']['id']);
					$data['id'] = $team_game_stat['TeamGameStat']['id'];
					if($team['id'] == $shot_team_id){
						$data['cf'] = $team_game_stat['TeamGameStat']['cf']+1;
						if($kind == 'f'){
							$data['ff'] = $team_game_stat['TeamGameStat']['ff'] + 1;
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['gf'] = $team_game_stat['TeamGameStat']['gf'] + 1;
						}
					} else {
						$data['ca'] = $team_game_stat['TeamGameStat']['ca'] + 1;
						if($kind == 'f'){
							$data['fa'] = $team_game_stat['TeamGameStat']['fa'] + 1;
						}
						if($this->Goal->find('first',array('conditions' => array('Goal.shot_id' => $shot['id'])))){
							$data['ga'] = $team_game_stat['TeamGameStat']['ga'] + 1;
						}
					}
					//$this->TeamGameStat->set($data);
				}
				$this->TeamGameStat->save($data);
			}
		}
	}
	
	public function getTOIByGame(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Shift'] as $shift){
			$shift_player = $this->Player->find('first',array('conditions' => array('Player.id' => $shift['player_id']),'recursive' => -1));
			$situation = $this->getTeamSituation($game_id,$shift['time_start'],$shift_player['Player']['team_id']);
			if($this->isClose($game_id,$shift['time_start'])){
				$close = 1;
			} else {
				$close = 0;
			}
			$conditions_array = array(
				'PlayerGameStat.player_id' => $shift_player['Player']['id'],
				'PlayerGameStat.game_id' => $game_id,
				'PlayerGameStat.situation' => $situation,
				'PlayerGameStat.close' => $close
			);
			$player_game_stat = $this->PlayerGameStat->find('first',array('conditions' => $conditions_array,'recursive' => -1));
			if(!$player_game_stat){
				$situation = $this->getTeamSituation($game_id,$shift['time_end'],$shift_player['Player']['team_id']);
				if($this->isClose($game_id,$shift['time_end'])){
					$close = 1;
				} else {
					$close = 0;
				}
				$conditions_array = array(
					'PlayerGameStat.player_id' => $shift_player['Player']['id'],
					'PlayerGameStat.game_id' => $game_id,
					'PlayerGameStat.situation' => $situation,
					'PlayerGameStat.close' => $close
				);
				$player_game_stat = $this->PlayerGameStat->find('first',array('conditions' => $conditions_array,'recursive' => -1));
			}
			if(!$player_game_stat){
				$this->PlayerGameStat->create();
				$data = array(
					'player_id' => $shift_player['Player']['id'],
					'game_id' => $game_id,
					'situation' => $situation,
					'close' => $close,
					'toi' => $shift['time_end'] - $shift['time_start']
				);
				$this->PlayerGameStat->set($data);
			} else {
				$this->PlayerGameStat->read(null,$player_game_stat['PlayerGameStat']['id']);
				$this->PlayerGameStat->set('toi',$player_game_stat['PlayerGameStat']['toi'] + ($shift['time_end'] - $shift['time_start']));
			}
			$this->PlayerGameStat->save();
		}
	}
	
	public function getPassDataByGame(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Pass'] as $pass){
			$pass_player = $this->Player->find('first',array('conditions' => array('Player.id' => $pass['player_id']),'recursive' => -1));
			$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $pass['shot_id']),'recursive' => -1));
			$situation = $this->getTeamSituation($game_id,$shot['Shot']['time'],$pass_player['Player']['team_id']);
			if($this->isClose($game_id,$shot['Shot']['time'])){
				$close = 1;
			} else {
				$close = 0;
			}
			
			if($pass['order'] == 'A' && $pass['type'] == 'SAG'){
				foreach($this->getOnIcePlayers($game_id,$shot['Shot']['time']) as $player_id){
					if($player_id != $pass['player_id']){
						$p = $this->Player->find('first',array('conditions' => array('Player.id' => $player_id),'recursive' => -1));
						$stat = $this->PlayerGameStat->find('first',array('conditions' => array(
							'PlayerGameStat.player_id' => $player_id,
							'PlayerGameStat.game_id' => $game_id,
							'PlayerGameStat.situation' => $situation,
							'PlayerGameStat.close' => $close
						),'recursive' => -1));
						
						if(!$stat){
							$data = array(
								'player_id' => $pass['player_id'],
								'game_id' => $game_id,
								'situation' => $situation,
								'close' => $close
							);
							if($p['Player']['team_id'] == $pass_player['Player']['team_id']){
								$data['sagf'] = 1;
							} else {
								$data['saga'] = 1;
							}
							$this->PlayerGameStat->create();
							$this->PlayerGameStat->set($data);
							$this->PlayerGameStat->save();
						} else {
							$this->PlayerGameStat->read(null,$stat['PlayerGameStat']['id']);
							if($p['Player']['team_id'] == $pass_player['Player']['team_id']){
								$this->PlayerGameStat->set('sagf',$stat['PlayerGameStat']['sagf']+1);
							} else {
								$this->PlayerGameStat->set('saga',$stat['PlayerGameStat']['saga']+1);
							}
							$this->PlayerGameStat->save();
						}
					}
				}
			}
			
			$stat = $this->PlayerGameStat->find('first',array('conditions' => array(
				'PlayerGameStat.player_id' => $pass['player_id'],
				'PlayerGameStat.game_id' => $game_id,
				'PlayerGameStat.situation' => $situation,
				'PlayerGameStat.close' => $close
			),'recursive' => -1));
			
			if(!$stat){
				$data = array(
					'player_id' => $pass['player_id'],
					'game_id' => $game_id,
					'situation' => $situation,
					'close' => $close
				);
				$this->PlayerGameStat->create();
				switch($pass['location']){
					case 'D/NZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['dnz_sg'] = 1;
								$data['sg'] = 1;
							} else {
								$data['dnz_a2sg'] = 1;
								$data['a2sg'] = 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['dnz_sag'] = 1;
								$data['sag'] = 1;
								$data['sagf'] = 1;
							} else {
								$data['dnz_a2sag'] = 1;
								$data['a2sag'] = 1;
							}
						}
						break;
					case 'OZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['oz_sg'] = 1;
								$data['sg'] = 1;
							} else {
								$data['oz_a2sg'] = 1;
								$data['a2sg'] = 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['oz_sag'] = 1;
								$data['sag'] = 1;
								$data['sagf'] = 1;
							} else {
								$data['oz_a2sag'] = 1;
								$data['a2sag'] = 1;
							}
						}
						break;
					case 'SC':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['sc_sg'] = 1;
								$data['sg'] = 1;
							} else {
								$data['a2sg'] = 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['sc_sag'] = 1;
								$data['sag'] = 1;
								$data['sagf'] = 1;
							} else {
								$data['a2sag'] = 1;
							}
						}
						break;
				}
				$this->PlayerGameStat->set($data);
				$this->PlayerGameStat->save();
			} else {
				$this->PlayerGameStat->read(null,$stat['PlayerGameStat']['id']);
				switch($pass['location']){
					case 'D/NZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['dnz_sg'] = $stat['PlayerGameStat']['dnz_sg'] + 1;
								$data['sg'] = $stat['PlayerGameStat']['sg'] + 1;
							} else {
								$data['dnz_a2sg'] = $stat['PlayerGameStat']['dnz_a2sg'] + 1;
								$data['a2sg'] = $stat['PlayerGameStat']['a2sg'] + 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['dnz_sag'] = $stat['PlayerGameStat']['dnz_sag'] + 1;
								$data['sag'] = $stat['PlayerGameStat']['sag'] + 1;
								$data['sagf'] = $stat['PlayerGameStat']['sagf'] + 1;
							} else {
								$data['dnz_a2sag'] = $stat['PlayerGameStat']['dnz_a2sag'] + 1;
								$data['a2sag'] = $stat['PlayerGameStat']['a2sag'] + 1;
							}
						}
						break;
					case 'OZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['oz_sg'] = $stat['PlayerGameStat']['oz_sg'] + 1;
								$data['sg'] = $stat['PlayerGameStat']['sg'] + 1;
							} else {
								$data['oz_a2sg'] = $stat['PlayerGameStat']['oz_a2sg'] + 1;
								$data['a2sg'] = $stat['PlayerGameStat']['a2sg'] + 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['oz_sag'] = $stat['PlayerGameStat']['oz_sag'] + 1;
								$data['sag'] = $stat['PlayerGameStat']['sag'] + 1;
								$data['sagf'] =$stat['PlayerGameStat']['sagf'] + 1;
							} else {
								$data['oz_a2sag'] = $stat['PlayerGameStat']['oz_a2sag'] + 1;
								$data['a2sag'] = $stat['PlayerGameStat']['a2sag'] + 1;
							}
						}
						break;
					case 'SC':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['sc_sg'] = $stat['PlayerGameStat']['sc_sg'] + 1;
								$data['sg'] = $stat['PlayerGameStat']['sg'] + 1;
							} else {
								$data['a2sg'] = $stat['PlayerGameStat']['a2sg'] + 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['sc_sag'] = $stat['PlayerGameStat']['sc_sag'] + 1;
								$data['sag'] = $stat['PlayerGameStat']['sag'] + 1;
								$data['sagf'] = $stat['PlayerGameStat']['sagf'] + 1;
							} else {
								$data['a2sag'] = $stat['PlayerGameStat']['a2sag'] + 1;
							}
						}
						break;
				}
				$this->PlayerGameStat->set($data);
				$this->PlayerGameStat->save();
			}
		}
	}
	
	public function getTeamPassDataByGame(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Pass'] as $pass){
			$pass_player = $this->Player->find('first',array('conditions' => array('Player.id' => $pass['player_id']),'recursive' => -1));
			$shot = $this->Shot->find('first',array('conditions' => array('Shot.id' => $pass['shot_id']),'recursive' => -1));
			$situation = $this->getTeamSituation($game_id,$shot['Shot']['time'],$pass_player['Player']['team_id']);
			if($this->isClose($game_id,$shot['Shot']['time'])){
				$close = 1;
			} else {
				$close = 0;
			}
			
			if($pass['order'] == 'A' && $pass['type'] == 'SAG'){
				foreach($game['Team'] as $team){
					$data = array();
					$stat = $this->TeamGameStat->find('first',array('conditions' => array(
						'TeamGameStat.team_id' => $team['id'],
						'TeamGameStat.game_id' => $game_id,
						'TeamGameStat.situation' => $situation,
						'TeamGameStat.close' => $close
					),'recursive' => -1));
					
					if(!$stat){
						$data = array(
							'team_id' => $team['id'],
							'game_id' => $game_id,
							'situation' => $situation,
							'close' => $close
						);
						if($team['id'] != $pass_player['Player']['team_id']){
							$data['saga'] = 1;
						} else {
							$data['sagf'] = 1;
						}
						$this->TeamGameStat->create();
						$this->TeamGameStat->set($data);
						$this->TeamGameStat->save();
					} else {
						$this->TeamGameStat->read(null,$stat['TeamGameStat']['id']);
						if($team['id'] != $pass_player['Player']['team_id']){
							$this->TeamGameStat->set('saga',$stat['TeamGameStat']['sagf']+1);
						} else {
							$this->TeamGameStat->set('sagf',$stat['TeamGameStat']['saga']+1);
						}
						$this->TeamGameStat->save();
					}
				}
			}
			$data = array();
			$stat = $this->TeamGameStat->find('first',array('conditions' => array(
				'TeamGameStat.team_id' => $pass_player['Player']['team_id'],
				'TeamGameStat.game_id' => $game_id,
				'TeamGameStat.situation' => $situation,
				'TeamGameStat.close' => $close
			),'recursive' => -1));
			
			if(!$stat){
				$data = array(
					'team_id' => $pass_player['Player']['team_id'],
					'game_id' => $game_id,
					'situation' => $situation,
					'close' => $close
				);
				$this->TeamGameStat->create();
				switch($pass['location']){
					case 'D/NZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['dnz_sg'] = 1;
								$data['sg'] = 1;
							} else {
								$data['dnz_a2sg'] = 1;
								$data['a2sg'] = 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['dnz_sag'] = 1;
								$data['sag'] = 1;
							} else {
								$data['dnz_a2sag'] = 1;
								$data['a2sag'] = 1;
							}
						}
						break;
					case 'OZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['oz_sg'] = 1;
								$data['sg'] = 1;
							} else {
								$data['oz_a2sg'] = 1;
								$data['a2sg'] = 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['oz_sag'] = 1;
								$data['sag'] = 1;
							} else {
								$data['oz_a2sag'] = 1;
								$data['a2sag'] = 1;
							}
						}
						break;
					case 'SC':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['sc_sg'] = 1;
								$data['sg'] = 1;
							} else {
								$data['a2sg'] = 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['sc_sag'] = 1;
								$data['sag'] = 1;
							} else {
								$data['a2sag'] = 1;
							}
						}
						break;
				}
				$this->TeamGameStat->set($data);
				$this->TeamGameStat->save();
			} else {
				$this->TeamGameStat->read(null,$stat['TeamGameStat']['id']);
				switch($pass['location']){
					case 'D/NZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['dnz_sg'] = $stat['TeamGameStat']['dnz_sg'] + 1;
								$data['sg'] = $stat['TeamGameStat']['sg'] + 1;
							} else {
								$data['dnz_a2sg'] = $stat['TeamGameStat']['dnz_a2sg'] + 1;
								$data['a2sg'] = $stat['TeamGameStat']['a2sg'] + 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['dnz_sag'] = $stat['TeamGameStat']['dnz_sag'] + 1;
								$data['sag'] = $stat['TeamGameStat']['sag'] + 1;
							} else {
								$data['dnz_a2sag'] = $stat['TeamGameStat']['dnz_a2sag'] + 1;
								$data['a2sag'] = $stat['TeamGameStat']['a2sag'] + 1;
							}
						}
						break;
					case 'OZ':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['oz_sg'] = $stat['TeamGameStat']['oz_sg'] + 1;
								$data['sg'] = $stat['TeamGameStat']['sg'] + 1;
							} else {
								$data['oz_a2sg'] = $stat['TeamGameStat']['oz_a2sg'] + 1;
								$data['a2sg'] = $stat['TeamGameStat']['a2sg'] + 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['oz_sag'] = $stat['TeamGameStat']['oz_sag'] + 1;
								$data['sag'] = $stat['TeamGameStat']['sag'] + 1;
							} else {
								$data['oz_a2sag'] = $stat['TeamGameStat']['oz_a2sag'] + 1;
								$data['a2sag'] = $stat['TeamGameStat']['a2sag'] + 1;
							}
						}
						break;
					case 'SC':
						if($pass['type'] == 'SG'){
							if($pass['order'] == 'A'){
								$data['sc_sg'] = $stat['TeamGameStat']['sc_sg'] + 1;
								$data['sg'] = $stat['TeamGameStat']['sg'] + 1;
							} else {
								$data['a2sg'] = $stat['TeamGameStat']['a2sg'] + 1;
							}
						} else {
							if($pass['order'] == 'A'){
								$data['sc_sag'] = $stat['TeamGameStat']['sc_sag'] + 1;
								$data['sag'] = $stat['TeamGameStat']['sag'] + 1;
								$data['sagf'] = $stat['TeamGameStat']['sagf'] + 1;
							} else {
								$data['a2sag'] = $stat['TeamGameStat']['a2sag'] + 1;
							}
						}
						break;
				}
				$this->TeamGameStat->set($data);
				$this->TeamGameStat->save();
			}
		}
	}
	
	public function createPlayerAllRecords(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Player'] as $player){
			$stats = $this->PlayerGameStat->find('all',array('conditions' => array('PlayerGameStat.game_id' => $game_id,'PlayerGameStat.player_id' => $player['id'])));
			$close_data = array(
				'player_id' => $player['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 1,
				'toi' => 0,
				'ca' => 0,
				'cf' => 0,
				'fa' => 0,
				'ff' => 0,
				'ga' => 0,
				'gf' => 0,
				'dnz_a2sag' => 0,
				'oz_a2sag' => 0,
				'a2sag' => 0,
				'dnz_a2sg' => 0,
				'oz_a2sg' => 0,
				'a2sg' => 0,
				'dnz_sag' => 0,
				'oz_sag' => 0,
				'sc_sag' => 0,
				'sag' => 0,
				'dnz_sg' => 0,
				'oz_sg' => 0,
				'sc_sg' => 0,
				'sg' => 0,
				'shots' => 0,
				'icorsi' => 0,
				'toi' => 0,
				'sagf' => 0,
				'saga' => 0
			);
			$all_data = array(
				'player_id' => $player['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 0,
				'toi' => 0,
				'ca' => 0,
				'cf' => 0,
				'fa' => 0,
				'ff' => 0,
				'ga' => 0,
				'gf' => 0,
				'dnz_a2sag' => 0,
				'oz_a2sag' => 0,
				'a2sag' => 0,
				'dnz_a2sg' => 0,
				'oz_a2sg' => 0,
				'a2sg' => 0,
				'dnz_sag' => 0,
				'oz_sag' => 0,
				'sc_sag' => 0,
				'sag' => 0,
				'dnz_sg' => 0,
				'oz_sg' => 0,
				'sc_sg' => 0,
				'sg' => 0,
				'shots' => 0,
				'icorsi' => 0,
				'toi' => 0,
				'sagf' => 0,
				'saga' => 0
			);
			foreach($stats as $stat){
				foreach($all_data as $id=>$data){
					if($id != 'player_id' && $id != 'game_id' && $id != 'situation' && $id != 'close'){
						$all_data[$id] += $stat['PlayerGameStat'][$id];
						if($stat['PlayerGameStat']['close'] == 1){
							$close_data[$id] += $stat['PlayerGameStat'][$id];
						}
					}
				}
			}
			
			$stat = $this->PlayerGameStat->find('first',array('conditions' => array(
				'player_id' => $player['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 1
			),'recursive' => -1));
			if(!$stat){
				$this->PlayerGameStat->create();
			} else {
				$this->PlayerGameStat->read(null,$stat['PlayerGameStat']['id']);
			}
			$this->PlayerGameStat->set($close_data);
			$this->PlayerGameStat->save();
			
			$stat = $this->PlayerGameStat->find('first',array('conditions' => array(
				'player_id' => $player['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 0
			),'recursive' => -1));
			if(!$stat){
				$this->PlayerGameStat->create();
			} else {
				$this->PlayerGameStat->read(null,$stat['PlayerGameStat']['id']);
			}
			$this->PlayerGameStat->set($all_data);
			$this->PlayerGameStat->save();
		}
	}
	
	public function createTeamAllRecords(){
		$game_id = $this->args[0];
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		foreach($game['Team'] as $team){
			$stats = $this->TeamGameStat->find('all',array('conditions' => array('TeamGameStat.game_id' => $game_id,'TeamGameStat.team_id' => $team['id'])));
			$close_data = array(
				'team_id' => $team['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 1,
				'ca' => 0,
				'cf' => 0,
				'fa' => 0,
				'ff' => 0,
				'ga' => 0,
				'gf' => 0,
				'dnz_a2sag' => 0,
				'oz_a2sag' => 0,
				'a2sag' => 0,
				'dnz_a2sg' => 0,
				'oz_a2sg' => 0,
				'a2sg' => 0,
				'dnz_sag' => 0,
				'oz_sag' => 0,
				'sc_sag' => 0,
				'sag' => 0,
				'dnz_sg' => 0,
				'oz_sg' => 0,
				'sc_sg' => 0,
				'sg' => 0,
				'sagf' => 0,
				'saga' => 0
			);
			$all_data = array(
				'team_id' => $team['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 0,
				'ca' => 0,
				'cf' => 0,
				'fa' => 0,
				'ff' => 0,
				'ga' => 0,
				'gf' => 0,
				'dnz_a2sag' => 0,
				'oz_a2sag' => 0,
				'a2sag' => 0,
				'dnz_a2sg' => 0,
				'oz_a2sg' => 0,
				'a2sg' => 0,
				'dnz_sag' => 0,
				'oz_sag' => 0,
				'sc_sag' => 0,
				'sag' => 0,
				'dnz_sg' => 0,
				'oz_sg' => 0,
				'sc_sg' => 0,
				'sg' => 0,
				'sagf' => 0,
				'saga' => 0
			);
			foreach($stats as $stat){
				foreach($all_data as $id=>$data){
					if($id != 'player_id' && $id != 'game_id' && $id != 'situation' && $id != 'close'){
						$all_data[$id] += $stat['TeamGameStat'][$id];
						if($stat['TeamGameStat']['close'] == 1){
							$close_data[$id] += $stat['TeamGameStat'][$id];
						}
					}
				}
			}
			
			$stat = $this->TeamGameStat->find('first',array('conditions' => array(
				'team_id' => $team['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 1
			),'recursive' => -1));
			if(!$stat){
				$this->TeamGameStat->create();
			} else {
				$this->TeamGameStat->read(null,$stat['TeamGameStat']['id']);
			}
			$this->TeamGameStat->set($close_data);
			$this->TeamGameStat->save();
			
			$stat = $this->TeamGameStat->find('first',array('conditions' => array(
				'team_id' => $team['id'],
				'game_id' => $game_id,
				'situation' => 'All',
				'close' => 0
			),'recursive' => -1));
			if(!$stat){
				$this->TeamGameStat->create();
			} else {
				$this->TeamGameStat->read(null,$stat['TeamGameStat']['id']);
			}
			$this->TeamGameStat->set($all_data);
			$this->TeamGameStat->save();
		}
	}
	
	public function doPlayerStatMath(){
		$player_id = $this->args[0];
		$stats = $this->PlayerGameStat->find('all',array('conditions' => array('PlayerGameStat.player_id' => $player_id)));
		foreach($stats as $stat){
			$this->PlayerGameStat->read(null,$stat['PlayerGameStat']['id']);
			$cf_p = $stat['PlayerGameStat']['cf'] / ($stat['PlayerGameStat']['cf'] + $stat['PlayerGameStat']['ca']);
			$cf_p *= 100;
			$ff_p = $stat['PlayerGameStat']['ff'] / ($stat['PlayerGameStat']['ff'] + $stat['PlayerGameStat']['fa']);
			$ff_p *= 100;
			$gf_p = $stat['PlayerGameStat']['gf'] / ($stat['PlayerGameStat']['gf'] + $stat['PlayerGameStat']['ga']);
			$gf_p *= 100;
			$a2_sage = $stat['PlayerGameStat']['a2sag'] / ($stat['PlayerGameStat']['a2sag'] + $stat['PlayerGameStat']['a2sg']);
			$a2_sage *= 100;
			$dnz_sage = $stat['PlayerGameStat']['dnz_sag'] / ($stat['PlayerGameStat']['dnz_sag'] + $stat['PlayerGameStat']['dnz_sg']);
			$dnz_sage *= 100;
			$oz_sage = $stat['PlayerGameStat']['oz_sag'] / ($stat['PlayerGameStat']['oz_sag'] + $stat['PlayerGameStat']['oz_sg']);
			$oz_sage *= 100;
			$sc_sage = $stat['PlayerGameStat']['sc_sag'] / ($stat['PlayerGameStat']['sc_sag'] + $stat['PlayerGameStat']['sc_sg']);
			$sc_sage *= 100;
			$sage = $stat['PlayerGameStat']['sag'] / ($stat['PlayerGameStat']['sag'] + $stat['PlayerGameStat']['sg']);
			$sage *= 100;
			$sagf_p = $stat['PlayerGameStat']['sagf'] / ($stat['PlayerGameStat']['sagf'] + $stat['PlayerGameStat']['saga']);
			$sagf_p *= 100;
			
			$data = array(
				'cf_p' => $cf_p,
				'ff_p' => $ff_p,
				'gf_p' => $gf_p,
				'a2_sage' => $a2_sage,
				'dnz_sage' => $dnz_sage,
				'oz_sage' => $oz_sage,
				'sc_sage' => $sc_sage,
				'sage' => $sage,
				'sagf_p' => $sagf_p
			);
			$this->PlayerGameStat->set($data);
			$this->PlayerGameStat->save();
		}
	}
	
	public function doTeamStatMath(){
		$team_id = $this->args[0];
		$stats = $this->TeamGameStat->find('all',array('conditions' => array('TeamGameStat.player_id' => $team_id)));
		foreach($stats as $stat){
			$this->TeamGameStat->read(null,$stat['TeamGameStat']['id']);
			$cf_p = $stat['TeamGameStat']['cf'] / ($stat['TeamGameStat']['cf'] + $stat['TeamGameStat']['ca']);
			$cf_p *= 100;
			$ff_p = $stat['TeamGameStat']['ff'] / ($stat['TeamGameStat']['ff'] + $stat['TeamGameStat']['fa']);
			$ff_p *= 100;
			$gf_p = $stat['TeamGameStat']['gf'] / ($stat['TeamGameStat']['gf'] + $stat['TeamGameStat']['ga']);
			$gf_p *= 100;
			$a2_sage = $stat['TeamGameStat']['a2_sag'] / ($stat['TeamGameStat']['a2_sag'] + $stat['TeamGameStat']['a2_sg']);
			$a2_sage *= 100;
			$dnz_sage = $stat['TeamGameStat']['dnz_sag'] / ($stat['TeamGameStat']['dnz_sag'] + $stat['TeamGameStat']['dnz_sg']);
			$dnz_sage *= 100;
			$oz_sage = $stat['TeamGameStat']['oz_sag'] / ($stat['TeamGameStat']['oz_sag'] + $stat['TeamGameStat']['oz_sg']);
			$oz_sage *= 100;
			$sc_sage = $stat['TeamGameStat']['sc_sag'] / ($stat['TeamGameStat']['sc_sag'] + $stat['TeamGameStat']['sc_sg']);
			$sc_sage *= 100;
			$sage = $stat['TeamGameStat']['sag'] / ($stat['TeamGameStat']['sag'] + $stat['TeamGameStat']['sg']);
			$sage *= 100;
			$sagf_p = $stat['TeamGameStat']['sagf'] / ($stat['TeamGameStat']['sagf'] + $stat['TeamGameStat']['saga']);
			$sagf_p *= 100;
			
			$data = array(
				'cf_p' => $cf_p,
				'ff_p' => $ff_p,
				'gf_p' => $gf_p,
				'a2_sage' => $a2_sage,
				'dnz_sage' => $dnz_sage,
				'oz_sage' => $oz_sage,
				'sc_sage' => $sc_sage,
				'sage' => $sage,
				'sagf_p' => $sagf_p
			);
			$this->TeamGameStat->set($data);
			$this->TeamGameStat->save();
		}
	}
	
	public function createPlayerAggregateStats(){
		$player_id = $this->args[0];
		$stats = $this->PlayerGameStat->find('all',array('conditions' => array('PlayerGameStat.player_id' => $player_id,'NOT' => array('PlayerGameStat.situation' => '')),'recursive' => -1));
		
		foreach($stats as $stat){
			if($stat['PlayerGameStat']['situation'] != null){
				
				$this->PlayerStat->clear();
				$agg_stat = $this->PlayerStat->find('first',array('conditions' => array(
					'PlayerStat.player_id' => $player_id,
					'PlayerStat.situation' => $stat['PlayerGameStat']['situation'],
					'PlayerStat.close' => $stat['PlayerGameStat']['close']
				),'recursive' => -1));
				
				$data = array(
					'ca' => 0,
					'cf' => 0,
					'fa' => 0,
					'ff' => 0,
					'ga' => 0,
					'gf' => 0,
					'dnz_a2sag' => 0,
					'oz_a2sag' => 0,
					'a2sag' => 0,
					'dnz_a2sg' => 0,
					'oz_a2sg' => 0,
					'a2sg' => 0,
					'dnz_sag' => 0,
					'oz_sag' => 0,
					'sc_sag' => 0,
					'sag' => 0,
					'dnz_sg' => 0,
					'oz_sg' => 0,
					'sc_sg' => 0,
					'sg' => 0,
					'shots' => 0,
					'icorsi' => 0,
					'toi' => 0,
					'sagf' => 0,
					'saga' => 0,
					'player_id' => $player_id,
					'situation' => $stat['PlayerGameStat']['situation'],
					'close' => $stat['PlayerGameStat']['close']
				);
				if(!$agg_stat){
					$this->PlayerStat->create();
					foreach($data as $id=>$r){
						if($id != 'player_id' && $id != 'situation' && $id != 'close' && $id != 'id'){
							$data[$id] += $stat['PlayerGameStat'][$id];
						}
					}
					
					$this->PlayerStat->save($data);
				} else {
					$this->PlayerStat->read(null,$agg_stat['PlayerStat']['id']);
					foreach($data as $id=>$r){
						$data[$id] = $agg_stat['PlayerStat'][$id];
					}
					$data['id'] = $agg_stat['PlayerStat']['id'];
					foreach($data as $id=>$r){
						if($id != 'player_id' && $id != 'situation' && $id != 'close' && $id != 'id'){
							$data[$id] += $stat['PlayerGameStat'][$id];
						}
					}
					
					$this->PlayerStat->save($data);
				}
			}
			
		}
	}
	
	public function createTeamAggregateStats(){
		$team_id = $this->args[0];
		$stats = $this->TeamGameStat->find('all',array('conditions' => array('TeamGameStat.team_id' => $team_id),'recursive' => -1));
		
		foreach($stats as $stat){
			$agg_stat = $this->TeamStat->find('first',array('conditions' => array(
				'TeamStat.player_id' => $team_id,
				'TeamStat.situation' => $stat['TeamGameStat']['situation'],
				'TeamStat.close' => $stat['TeamGameStat']['close']
			),'recursive' => -1));
			
			$data = array(
				'ca' => 0,
				'cf' => 0,
				'fa' => 0,
				'ff' => 0,
				'ga' => 0,
				'gf' => 0,
				'dnz_a2sag' => 0,
				'oz_a2sag' => 0,
				'a2sag' => 0,
				'dnz_a2sg' => 0,
				'oz_a2sg' => 0,
				'a2sg' => 0,
				'dnz_sag' => 0,
				'oz_sag' => 0,
				'sc_sag' => 0,
				'sag' => 0,
				'dnz_sg' => 0,
				'oz_sg' => 0,
				'sc_sg' => 0,
				'sg' => 0,
				'shots' => 0,
				'icorsi' => 0,
				'toi' => 0,
				'sagf' => 0,
				'saga' => 0
			);
			if(!$agg_stat){
				$this->TeamStat->create();
				$data['team_id'] = $team_id;
				$data['situation'] = $stat['TeamGameStat']['situation'];
				$data['close'] = $stat['TeamGameStat']['close'];
			} else {
				$this->TeamStat->read(null,$agg_stat['TeamStat']['id']);
				foreach($data as $id=>$r){
					$data[$id] = $agg_stat['TeamStat'][$id];
				}
			}
			
			foreach($data as $id=>$r){
				$data[$id] += $stat['TeamGameStat'][$id];
			}
			$this->TeamStat->set($data);
			$this->TeamStat->save();
		}
	}
	
	public function doPlayerAggregateStatMath(){
		$player_id = $this->args[0];
		$stats = $this->PlayerStat->find('all',array('conditions' => array('PlayerStat.player_id' => $player_id)));
		foreach($stats as $stat){
			$this->PlayerStat->read(null,$stat['PlayerStat']['id']);
			$cf_p = $stat['PlayerStat']['cf'] / ($stat['PlayerStat']['cf'] + $stat['PlayerStat']['ca']);
			$cf_p *= 100;
			$ff_p = $stat['PlayerStat']['ff'] / ($stat['PlayerStat']['ff'] + $stat['PlayerStat']['fa']);
			$ff_p *= 100;
			$gf_p = $stat['PlayerStat']['gf'] / ($stat['PlayerStat']['gf'] + $stat['PlayerStat']['ga']);
			$gf_p *= 100;
			$a2_sage = $stat['PlayerStat']['a2sag'] / ($stat['PlayerStat']['a2sag'] + $stat['PlayerStat']['a2sg']);
			$a2_sage *= 100;
			$dnz_sage = $stat['PlayerStat']['dnz_sag'] / ($stat['PlayerStat']['dnz_sag'] + $stat['PlayerStat']['dnz_sg']);
			$dnz_sage *= 100;
			$oz_sage = $stat['PlayerStat']['oz_sag'] / ($stat['PlayerStat']['oz_sag'] + $stat['PlayerStat']['oz_sg']);
			$oz_sage *= 100;
			$sc_sage = $stat['PlayerStat']['sc_sag'] / ($stat['PlayerStat']['sc_sag'] + $stat['PlayerStat']['sc_sg']);
			$sc_sage *= 100;
			$sage = $stat['PlayerStat']['sag'] / ($stat['PlayerStat']['sag'] + $stat['PlayerStat']['sg']);
			$sage *= 100;
			$sagf_p = $stat['PlayerStat']['sagf'] / ($stat['PlayerStat']['sagf'] + $stat['PlayerStat']['saga']);
			$sagf_p *= 100;
			
			$data = array(
				'cf_p' => $cf_p,
				'ff_p' => $ff_p,
				'gf_p' => $gf_p,
				'a2_sage' => $a2_sage,
				'dnz_sage' => $dnz_sage,
				'oz_sage' => $oz_sage,
				'sc_sage' => $sc_sage,
				'sage' => $sage,
				'sagf_p' => $sagf_p
			);
			$this->PlayerStat->set($data);
			$this->PlayerStat->save();
		}
	}
	
	public function doTeamAggregateStatMath(){
		$team_id = $this->args[0];
		$stats = $this->TeamStat->find('all',array('conditions' => array('TeamStat.player_id' => $player_id)));
		foreach($stats as $stat){
			$this->TeamStat->read(null,$stat['TeamStat']['id']);
			$cf_p = $stat['TeamStat']['cf'] / ($stat['TeamStat']['cf'] + $stat['TeamStat']['ca']);
			$cf_p *= 100;
			$ff_p = $stat['TeamStat']['ff'] / ($stat['TeamStat']['ff'] + $stat['TeamStat']['fa']);
			$ff_p *= 100;
			$gf_p = $stat['TeamStat']['gf'] / ($stat['TeamStat']['gf'] + $stat['TeamStat']['ga']);
			$gf_p *= 100;
			$a2_sage = $stat['TeamStat']['a2_sag'] / ($stat['TeamStat']['a2_sag'] + $stat['TeamStat']['a2_sg']);
			$a2_sage *= 100;
			$dnz_sage = $stat['TeamStat']['dnz_sag'] / ($stat['TeamStat']['dnz_sag'] + $stat['TeamStat']['dnz_sg']);
			$dnz_sage *= 100;
			$oz_sage = $stat['TeamStat']['oz_sag'] / ($stat['TeamStat']['oz_sag'] + $stat['TeamStat']['oz_sg']);
			$oz_sage *= 100;
			$sc_sage = $stat['TeamStat']['sc_sag'] / ($stat['TeamStat']['sc_sag'] + $stat['TeamStat']['sc_sg']);
			$sc_sage *= 100;
			$sage = $stat['TeamStat']['sag'] / ($stat['TeamStat']['sag'] + $stat['TeamStat']['sg']);
			$sage *= 100;
			$sagf_p = $stat['TeamStat']['sagf'] / ($stat['TeamStat']['sagf'] + $stat['TeamStat']['saga']);
			$sagf_p *= 100;
			
			$data = array(
				'cf_p' => $cf_p,
				'ff_p' => $ff_p,
				'gf_p' => $gf_p,
				'a2_sage' => $a2_sage,
				'dnz_sage' => $dnz_sage,
				'oz_sage' => $oz_sage,
				'sc_sage' => $sc_sage,
				'sage' => $sage,
				'sagf_p' => $sagf_p
			);
			$this->TeamStat->set($data);
			$this->TeamStat->save();
		}
	}
}	
?>