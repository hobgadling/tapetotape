<h1><?php echo $game['Team'][0]['team_name']?> vs <?php echo $game['Team'][1]['team_name']?></h1>
<h2><?php echo date('n/j/Y h:i',strtotime($game['Game']['event_date']));?></h2>

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
	<?php foreach($game['TeamGameStat'] as $stat){?>
		<tr>
			<td><?php echo $stat['Team']['team_name']?></td>
			<td><?php echo $stat['cf']?></td>
			<td><?php echo $stat['ca']?></td>
			<td><?php echo $stat['cf_p']?></td>
			<td><?php echo $stat['ff']?></td>
			<td><?php echo $stat['fa']?></td>
			<td><?php echo $stat['ff_p']?></td>
			<td><?php echo $stat['a2_sage']?></td>
			<td><?php echo $stat['dnz_sage']?></td>
			<td><?php echo $stat['oz_sage']?></td>
			<td><?php echo $stat['sc_sage']?></td>
			<td><?php echo $stat['sage']?></td>
		</tr>
	<? }?>
	</tbody>
</table>

<?php foreach($game['Team'] as $team){?>
	<h3><?php echo $team['team_name']?></h3>
	<table width="100%" id="team<?php echo $team['id']?>_table" class="table table-striped tablesorter" border="0" cellpadding="0" cellspacing="1">
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
			<?php foreach($game['PlayerGameStat'] as $stat){?>
				<?php if($stat['Player']['team_id'] == $team['id'] && $stat['Player']['position'] != 'G'){?>
					<tr>
						<td><?php echo ucwords($stat['Player']['name'])?></td>
						<td><?php echo $stat['cf']?></td>
						<td><?php echo $stat['ca']?></td>
						<td><?php echo $stat['cf_p']?></td>
						<td><?php echo $stat['ff']?></td>
						<td><?php echo $stat['fa']?></td>
						<td><?php echo $stat['ff_p']?></td>
						<td><?php echo $stat['a2_sage']?></td>
						<td><?php echo $stat['dnz_sage']?></td>
						<td><?php echo $stat['oz_sage']?></td>
						<td><?php echo $stat['sc_sage']?></td>
						<td><?php echo $stat['sage']?></td>
					</tr>
				<?php }?>
			<?php }?>
		</tbody>
	</table>
<?php }?>