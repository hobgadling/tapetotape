<h1><?php echo ucwords($player['Player']['name'])?></h1>
<table width="100%" id="player_table" class="table table-striped tablesorter" border="0" cellpadding="0" cellspacing="1">
	<thead>
	<tr>
		<th>CF</th>
		<th>CA</th>
		<th>CF%</th>
		<th>FF</th>
		<th>FA</th>
		<th>FF%</th>
		<th>dnz_a2sag</th>
		<th>oz_a2sag</th>
		<th>a2sag</th>
		<th>dnz_a2sg</th>
		<th>oz_a2sg</th>
		<th>a2sg</th>
		<th>dnz_sag</th>
		<th>oz_sag</th>
		<th>sc_sag</th>
		<th>sag</th>
		<th>dnz_sg</th>
		<th>oz_sg</th>
		<th>sc_sg</th>
		<th>sg</th>
		<th>A2 SAGE</th>
		<th>D/NZ SAGE</th>
		<th>OZ SAGE</th>
		<th>SC SAGE</th>
		<th>SAGE</th>
	</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo $player['PlayerStat'][0]['cf']?></td>
			<td><?php echo $player['PlayerStat'][0]['ca']?></td>
			<td><?php echo $player['PlayerStat'][0]['cf_p']?></td>
			<td><?php echo $player['PlayerStat'][0]['ff']?></td>
			<td><?php echo $player['PlayerStat'][0]['fa']?></td>
			<td><?php echo $player['PlayerStat'][0]['ff_p']?></td>
			<td><?php echo $player['PlayerStat'][0]['dnz_a2sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['oz_a2sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['a2sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['dnz_a2sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['oz_a2sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['a2sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['dnz_sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['oz_sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['sc_sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['sag']?></td>
			<td><?php echo $player['PlayerStat'][0]['dnz_sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['oz_sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['sc_sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['sg']?></td>
			<td><?php echo $player['PlayerStat'][0]['a2_sage']?></td>
			<td><?php echo $player['PlayerStat'][0]['dnz_sage']?></td>
			<td><?php echo $player['PlayerStat'][0]['oz_sage']?></td>
			<td><?php echo $player['PlayerStat'][0]['sc_sage']?></td>
			<td><?php echo $player['PlayerStat'][0]['sage']?></td>
		</tr>
	</tbody>
</table>