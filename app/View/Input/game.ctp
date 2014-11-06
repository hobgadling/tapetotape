When you have completed the tracking, submit here: 
<form action="/input/complete" method="post" role="form" class="form-inline">
	<div class="form-group">
		<label class="sr-only" for="name">Name</label>
		<input type="text" class="form-control" id="name" name="name" placeholder="Name" />
	</div>
	<input type="hidden" name="game_id" value="<?php echo $game['Game']['id']?>" />
	<button class="btn btn-default" type="submit">Complete Game</button>
</form>
<br /><br />
<div class="table-responsive">
	<table class="table table-bordered">
		<thead>
			<tr>
				<td>Player</td>
				<td>D/NZ A2SAG</td>
				<td>D/NZ A2SAG Close</td>
				<td>OZ A2 SAG</td>
				<td>OZ A2 SAG Close</td>
				<td>D/NZ A2SG</td>
				<td>D/NZ A2SG Close</td>
				<td>OZ A2 SG</td>
				<td>OZ A2 SG Close</td>
				<td>D/NZ SAG</td>
				<td>D/NZ SAG Close</td>
				<td>OZ SAG</td>
				<td>OZ SAG Close</td>
				<td>SC SAG</td>
				<td>SC SAG Close</td>
				<td>D/NZ SG</td>
				<td>D/NZ SG Close</td>
				<td>OZ SG</td>
				<td>OZ SG Close</td>
				<td>SC SG</td>
				<td>SC SG Close</td>
			</tr>
		</thead>
		<?php foreach($game['Team'] as $team){?>
			<tr class="warning"><td colspan="21" class="warning"><?php echo $team['team_name']?></td></tr>
			<?php foreach($team['Player'] as $player){?>
				<tr>
					<td><b><?php echo $player['number'] . ' ' . $player['name'] . ' ' . $player['position']?></b></td>
					<?php for($i=1;$i<21;$i++){?>
						<td id="team<?php echo $team['id']?>_player<?php echo $player['id']?>_type<?php echo $i?>">
							<button type="button" class="add btn btn-primary">+</button><br />
							<span class="total">0</span><br />
							<button type="button" class="sub btn btn-primary">-</button>
						</td>
					<?php }?>
				</tr>
			<?php }?>
		<?php }?>
	</table>
</div>