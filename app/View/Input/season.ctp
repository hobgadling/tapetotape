Please select a game to record:
<br />
<?php foreach($season['Game'] as $game){?>
<a href="/input/game/<?php echo $game['id'];?>"><?php echo date('l F j, Y',strtotime($game['event_date']))?>:
   <?php foreach($game['Team'] as $id=>$team){?>
 <?php echo $team['team_name']?> at
 <?php }?>
</a><br />
<?php }?>