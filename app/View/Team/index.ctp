Recently Completed Games:<br />
<?php foreach($games as $game){?>
<a href="/game/detail/<?php echo $game['Game']['id']?>">
	<?php echo date('n/j/Y h:i',strtotime($game['Game']['event_date']));?> - <?php echo $game['Team'][0]['team_name']?> vs <?php echo $game['Team'][1]['team_name']?><br />
</a>
<?php }?>
<table width="100%" id="teams_table" class="table table-striped tablesorter" border="0" cellpadding="0" cellspacing="1">
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
	<?php foreach($teams as $team){?>
		<tr>
			<td><a href="/team/detail/<?php echo $team['Team']['short_name']?>"><?php echo $team['Team']['team_name']?></a></td>
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
	<?php }?>
	</tbody>
</table>