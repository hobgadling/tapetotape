<h1><?php echo $team['Team']['team_name']?></h1>

Recently Completed Games:<br />
<?php foreach($games as $game){?>
<a href="/game/detail/<?php echo $game['Game']['id']?>">
	<?php echo date('n/j/Y h:i',strtotime($game['Game']['event_date']));?> - <?php echo $game['Team'][0]['team_name']?> vs <?php echo $game['Team'][1]['team_name']?><br />
</a>
<?php }?>

<table width="100%" id="team_table" class="table table-striped tablesorter" border="0" cellpadding="0" cellspacing="1">
	<thead>
	<tr>
		<th>CF</th>
		<th>CA</th>
		<th>CF%</th>
		<th>FF</th>
		<th>FA</th>
		<th>FF%</th>
		<th>A2 SAGE</th>
		<th>D/NZ SAGE</th>
		<th>OZ SAGE</th>
		<th>SC SAGE</th>
		<th>SAGE</th>
	</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo $team['TeamStat'][0]['cf']?></td>
			<td><?php echo $team['TeamStat'][0]['ca']?></td>
			<td><?php echo $team['TeamStat'][0]['cf_p']?></td>
			<td><?php echo $team['TeamStat'][0]['ff']?></td>
			<td><?php echo $team['TeamStat'][0]['fa']?></td>
			<td><?php echo $team['TeamStat'][0]['ff_p']?></td>
			<td><?php echo $team['TeamStat'][0]['a2_sage']?></td>
			<td><?php echo $team['TeamStat'][0]['dnz_sage']?></td>
			<td><?php echo $team['TeamStat'][0]['oz_sage']?></td>
			<td><?php echo $team['TeamStat'][0]['sc_sage']?></td>
			<td><?php echo $team['TeamStat'][0]['sage']?></td>
		</tr>
	</tbody>
</table>

<table width="100%" id="players_table" class="table table-striped tablesorter" border="0" cellpadding="0" cellspacing="1">
	<thead>
	<tr>
		<th>Name</th>
		<th>CF</th>
		<th>CA</th>
		<th>CF%</th>
		<th>FF</th>
		<th>FA</th>
		<th>FF%</th>
		<th>A2 SAGE</th>
		<th>D/NZ SAGE</th>
		<th>OZ SAGE</th>
		<th>SC SAGE</th>
		<th>SAGE</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($team['Player'] as $player){?>
		<?php if($player['position'] != 'G'){?>
			<tr>
				<td><a href="/player/detail/<?php echo $player['id']?>"><?php echo ucwords($player['name'])?></a></td>
				<td><?php echo $player['PlayerStat'][0]['cf']?></td>
				<td><?php echo $player['PlayerStat'][0]['ca']?></td>
				<td><?php echo $player['PlayerStat'][0]['cf_p']?></td>
				<td><?php echo $player['PlayerStat'][0]['ff']?></td>
				<td><?php echo $player['PlayerStat'][0]['fa']?></td>
				<td><?php echo $player['PlayerStat'][0]['ff_p']?></td>
				<td><?php echo $player['PlayerStat'][0]['a2_sage']?></td>
				<td><?php echo $player['PlayerStat'][0]['dnz_sage']?></td>
				<td><?php echo $player['PlayerStat'][0]['oz_sage']?></td>
				<td><?php echo $player['PlayerStat'][0]['sc_sage']?></td>
				<td><?php echo $player['PlayerStat'][0]['sage']?></td>
			</tr>
		<?php }?>
	<?php }?>
	</tbody>
</table>