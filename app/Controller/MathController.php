<?php
class MathController extends AppController{
	public $uses = array('Season','Game','Team','Player','Shift','Shot','Faceoff','Goal','Block','Shot','Assist','TeamStat','TeamGameStat','PlayerStat','PlayerGameStat');
	
	public function createTeamRecords(){
		$teams = $this->Team->find('all');
		$situations = array('All','5v5','5v4','4v4','5v3','4v3','3v3');
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
		$situations = array('All','5v5','5v4','4v4','5v3','4v3','3v3');
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
	
	public function isClose($game_id,$time){
		$game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id)));
		$score = array();
		foreach($game['Team'] as $team){
			$score[$team['id']] = 0;
		}
		foreach($game['Goal'] as $goal){
			if($goal['time'] <= $time){
				$player = $this->Player->find('first',array('conditions' => array('Player.id' => $goal['player_id'])));
				$score[$player['Player']['team_id']]++;
			}
		}
		list($score1) = array_slice($score, 0, 1);
		list($score2) = array_slice($score, 1, 1);
		$diff = abs($score1-$score2);
		if($time < 40*60){
			return $diff < 2;
		} else {
			return $diff == 0;
		}
	}
	
	public function onIce($player_id,$game_id,$time){
		$shift = $this->Shift->find('first',array('conditions' => array(
			'Shift.player_id' => $player_id,
			'Shift.game_id' => $game_id,
			'Shift.time_start <=' => $time,
			'Shift.time_end >=' => $time
		),'recursive' => -1));
		
		return !!$shift;
	}
	
	public function getTeamGameBySituation($game_id,$team_id,$situation,$close){
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
								} else {
									$a2sag++;
								}
							}
							break;
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
			'sage' => $sage
		);
		$this->TeamGameStat->create();
		$this->TeamGameStat->save($stats);
	}
	
	public function getTeamAggregateBySituation($team_id,$situation,$close){
		header('Content-Type: text/html');
		set_time_limit(0);
		$team = $this->Team->find('first',array('conditions' => array('Team.id' => $team_id),'recursive' => 2));
		$stat = $this->TeamStat->find('first',array('conditions' => array('TeamStat.team_id' => $team_id,'TeamStat.situation' => $situation,'TeamStat.close' => $close)));
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
									} else {
										$a2sag++;
									}
								}
								break;
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
				'sage' => $sage
			);
		$this->TeamStat->set($stats);
		$this->TeamStat->save();
	}
	
	public function getPlayerAggregateBySituation($player_id,$situation,$close){
		header('Content-Type: text/html');
		set_time_limit(0);
		$player = $this->Player->find('first',array('conditions' => array('Player.id' => $player_id),'recursive' => -1));
		$team = $this->Team->find('first',array('conditions' => array('Team.id' => $player['Player']['team_id']),'recursive' => 2));
		$stat = $this->PlayerStat->find('first',array('conditions' => array('PlayerStat.player_id' => $player_id,'PlayerStat.situation' => $situation,'PlayerStat.close' => $close)));
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
		
		$player_ids = array();
		foreach($team['Player'] as $player){
			foreach($player['Shot'] as $shot){
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
										} else {
											$a2sag++;
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
				'sage' => $sage
			);
		$this->PlayerStat->set($stats);
		$this->PlayerStat->save();
	}
	
	public function getPlayerGameBySituation($game_id,$player_id,$situation,$close){
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
		
		foreach($game['Shot'] as $shot){
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
									} else {
										$a2sag++;
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
			'sage' => $sage
		);
		$this->PlayerGameStat->create();
		$this->PlayerGameStat->save($stats);
	}
	
	public function getFullGameStats($game_id){
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
}	
?>