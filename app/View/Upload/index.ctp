<input type="text" class="datepicker" />

<?php foreach($games as $date=>$day_games){?>
	<div id="<?php echo $date?>" class="hidden game_list">
		<?php foreach($day_games as $game){?>
			<div class="game_selector">
				<?php foreach($game['Team'] as $id=>$team){?>
					<span class="team_<?php echo $team['id']?>"><?php echo $team['team_name']?></span>
					<?php if($id == 0){?>
						 at 
					<? }?>
				<?php }?>
				 - <?php echo date('h:i A',strtotime($game['Game']['event_date']));?>
				 <button class="uploadform" id="<?php echo $game['Game']['id']?>">Upload Stats</button>
				 <br />
			</div>
		<? }?>
	</div>
<?php }?>

<form method="post" id="uploadform" class="hidden" enctype="multipart/form-data">
	Upload spreadsheet(s):<br />
	<span class="teamname"></span><br />
	<input type="file" name="" class="fileupload" /><br />
	<span class="teamname"></span><br />
	<input type="file" name="" class="fileupload" /><br />
	<input type="text" name="completed_by" id="completed_by" placeholder="Name" />
	<input type="hidden" name="game_id" id="game_id" value="" />
	<input type="submit" name="submit" value="Submit" />
</form>