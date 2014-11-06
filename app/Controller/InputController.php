<?php
class InputController extends AppController{
  public $uses = array('Game','Season','Team','Player','Pass');

  public function index(){
    $seasons = $this->Season->find('all');
    $this->set('seasons',$seasons);
  }

  public function season($season_id){
    $season = $this->Season->find('first',array('conditions' => array('id' => $season_id),'recursive' => 2));
    $this->set('season',$season);
  }

  public function game($game_id){
    $game = $this->Game->find('first',array('conditions' => array('Game.id' => $game_id),'recursive' => 2));
    $this->set('game',$game);
  }

  public function addPass(){
    switch($this->request->data['pass_type']){
	    case 1:
	    	$type = 'SAG';
	    	$location = 'D/NZ';
	    	$order = 'A2';
	    	$situation = 'Normal';
	    	break;
	    case 2:
	    	$type = 'SAG';
	    	$location = 'D/NZ';
	    	$order = 'A2';
	    	$situation = 'Score Close';
	    	break;
	    case 3:
	    	$type = 'SAG';
	    	$location = 'OZ';
	    	$order = 'A2';
	    	$situation = 'Normal';
	    	break;
	    case 4:
	    	$type = 'SAG';
	    	$location = 'OZ';
	    	$order = 'A2';
	    	$situation = 'Score Close';
	    	break;
	    case 5:
	    	$type = 'SG';
	    	$location = 'D/NZ';
	    	$order = 'A2';
	    	$situation = 'Normal';
	    	break;
	    case 6:
	    	$type = 'SG';
	    	$location = 'D/NZ';
	    	$order = 'A2';
	    	$situation = 'Score Close';
	    	break;
	    case 7:
	    	$type = 'SG';
	    	$location = 'OZ';
	    	$order = 'A2';
	    	$situation = 'Normal';
	    	break;
	    case 8:
	    	$type = 'SG';
	    	$location = 'OZ';
	    	$order = 'A2';
	    	$situation = 'Score Close';
	    	break;
	    case 9:
	    	$type = 'SAG';
	    	$location = 'D/NZ';
	    	$order = 'A';
	    	$situation = 'Normal';
	    	break;
	    case 10:
	    	$type = 'SAG';
	    	$location = 'D/NZ';
	    	$order = 'A';
	    	$situation = 'Score Close';
	    	break;
	    case 11:
	    	$type = 'SAG';
	    	$location = 'OZ';
	    	$order = 'A';
	    	$situation = 'Normal';
	    	break;
	    case 12:
	    	$type = 'SAG';
	    	$location = 'OZ';
	    	$order = 'A';
	    	$situation = 'Score Close';
	    	break;
	    case 13:
	    	$type = 'SAG';
	    	$location = 'SC';
	    	$order = 'A';
	    	$situation = 'Normal';
	    	break;
	    case 14:
	    	$type = 'SAG';
	    	$location = 'SC';
	    	$order = 'A';
	    	$situation = 'Score Close';
	    	break;
	    case 15:
	    	$type = 'SG';
	    	$location = 'D/NZ';
	    	$order = 'A';
	    	$situation = 'Normal';
	    	break;
	    case 16:
	    	$type = 'SG';
	    	$location = 'D/NZ';
	    	$order = 'A';
	    	$situation = 'Score Close';
	    	break;
	    case 17:
	    	$type = 'SG';
	    	$location = 'OZ';
	    	$order = 'A';
	    	$situation = 'Normal';
	    	break;
	    case 18:
	    	$type = 'SG';
	    	$location = 'OZ';
	    	$order = 'A';
	    	$situation = 'Score Close';
	    	break;
	    case 19:
	    	$type = 'SG';
	    	$location = 'SC';
	    	$order = 'A';
	    	$situation = 'Normal';
	    	break;
	    case 20:
	    	$type = 'SG';
	    	$location = 'SC';
	    	$order = 'A';
	    	$situation = 'Score Close';
	    	break;
    }
    
    if($this->request->data['type'] == 'add'){
	    $pass = array();
	    $pass['Player'] = array();
	    $pass['Game'] = array();
	    $pass['Player']['id'] = $this->request->data['player'];
	    $pass['Game']['id'] = $this->request->data['game'];
	    $pass['Pass'] = array(
	    	'type' => $type,
	    	'location' => $location,
	    	'order' => $order,
	    	'situation' => $situation,
	    	'created_at' => date('Y-m-d H:i:s')
	    );
	    $this->Pass->saveAssociated($pass);
    } else {
	    $pass = $this->Pass->find('first',array(
	    	'order' => 'Pass.created_at DESC',
	    	'conditions' => array(
	    		'Pass.type' => $type,
	    		'Pass.location' => $location,
	    		'Pass.order' => $order,
	    		'Pass.situation' => $situation,
	    		'Pass.player_id' => $this->request->data['player'],
	    		'Pass.game_id' => $this->request->data['game']
	    	)
	    ));
	    if($pass){
		   $this->Pass->delete($pass['Pass']['id'],false);
	    }
    }
  }
  
  public function complete(){
  	require '../Vendor/PHPMailerAutoload.php';
	  $this->Game->read(null,$this->request->data['game_id']);
	  $this->Game->set(array(
	  	'complete' => 1,
	  	'completed_at' => date('Y-m-d H:i:s')
	  ));
	  $this->Game->save();
	  
	  $mail = new PHPMailer;
	  $mail->From = 'tracking@passstats.nfshost.net';
	  $mail->FromName = 'Game Tracking';
	  $mail->AddAddress('hobgadling@gmail.com');
	  $mail->Subject = 'A Game has been completed';
	  $mail->Body = $this->request->data['name'] . " has finished tracking a game.<br /><br /><a href='http://" . $_SERVER['HTTP_HOST'] . "/output/csv/" . $this->Game->id . "'>Click here</a> to get the spreadsheet for it";
	  $mail->isHTML(true);
	  $mail->send();
	  

  }
}
?>