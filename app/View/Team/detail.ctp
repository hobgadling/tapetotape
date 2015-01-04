<h1><?php echo $team['Team']['team_name']?></h1>

Recently Completed Games:<br />
<?php foreach($games as $game){?>
<a href="/game/detail/<?php echo $game['Game']['id']?>">
	<?php echo date('n/j/Y h:i',strtotime($game['Game']['event_date']));?> - <?php echo $game['Team'][0]['team_name']?> vs <?php echo $game['Team'][1]['team_name']?><br />
</a>
<?php }?>

Situation:

<div class="dropdown">
	<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
		<?php echo $situation?>
		<span class="caret"></span>
	</button>
	<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
		<li role="presentation"><a role="menuitem" class="situation" tabindex="-1" href="/team/detail/<?php echo $team['Team']['short_name']?>/All">All</a>
		<li role="presentation"><a role="menuitem" class="situation" tabindex="-1" href="/team/detail/<?php echo $team['Team']['short_name']?>/5v5">5v5</a>
		<li role="presentation"><a role="menuitem" class="situation" tabindex="-1" href="/team/detail/<?php echo $team['Team']['short_name']?>/5v4">5v4</a>
	</ul>
</div>

Score Close: <input type="checkbox" id="sc"<?php if($close == '1'){?>checked="checked"<?php }?> />

<table width="100%" id="team_table" class="table table-striped tablesorter" border="0" cellpadding="0" cellspacing="1">
	<thead>
	<tr>
		<th>CF</th>
		<th>CA</th>
		<th>CF%</th>
		<th>FF</th>
		<th>FA</th>
		<th>FF%</th>
		<th>GF</th>
		<th>GA</th>
		<th>GF%</th>
		<th>PDO</th>
		<th>D/NZ A2SAG</th>
		<th>OZ A2SAG</th>
		<th>A2SAG</th>
		<th>D/NZ A2SG</th>
		<th>OZ A2SG</th>
		<th>A2SG</th>
		<th>D/NZ SAG</th>
		<th>OZ SAG</th>
		<th>SC SAG</th>
		<th>SAG</th>
		<th>D/NZ SG</th>
		<th>OZ SG</th>
		<th>SC SG</th>
		<th>SG</th>
		<th>A2 SAGE</th>
		<th>D/NZ SAGE</th>
		<th>OZ SAGE</th>
		<th>SC SAGE</th>
		<th>SAGE</th>
	</tr>
	</thead>
	<tbody>
		<?php foreach($team['TeamStat'] as $stat){?>
			<?php if($stat['situation'] == $situation && $stat['close'] == $close){?>
				<tr>
					<td><?php echo $stat['cf']?></td>
					<td><?php echo $stat['ca']?></td>
					<td><?php echo $stat['cf_p']?></td>
					<td><?php echo $stat['ff']?></td>
					<td><?php echo $stat['fa']?></td>
					<td><?php echo $stat['ff_p']?></td>
					<td><?php echo $stat['pdo']?></td>
					<td><?php echo $stat['gf']?></td>
					<td><?php echo $stat['ga']?></td>
					<td><?php echo $stat['gf_p']?></td>
					<td><?php echo $stat['dnz_a2sag']?></td>
					<td><?php echo $stat['oz_a2sag']?></td>
					<td><?php echo $stat['a2sag']?></td>
					<td><?php echo $stat['dnz_a2sg']?></td>
					<td><?php echo $stat['oz_a2sg']?></td>
					<td><?php echo $stat['a2sg']?></td>
					<td><?php echo $stat['dnz_sag']?></td>
					<td><?php echo $stat['oz_sag']?></td>
					<td><?php echo $stat['sc_sag']?></td>
					<td><?php echo $stat['sag']?></td>
					<td><?php echo $stat['dnz_sg']?></td>
					<td><?php echo $stat['oz_sg']?></td>
					<td><?php echo $stat['sc_sg']?></td>
					<td><?php echo $stat['sg']?></td>
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
		<th>D/NZ A2SAG</th>
		<th>OZ A2SAG</th>
		<th>A2SAG</th>
		<th>D/NZ A2SG</th>
		<th>OZ A2SG</th>
		<th>A2SG</th>
		<th>D/NZ SAG</th>
		<th>OZ SAG</th>
		<th>SC SAG</th>
		<th>SAG</th>
		<th>D/NZ SG</th>
		<th>OZ SG</th>
		<th>SC SG</th>
		<th>SG</th>
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
			<?php foreach($player['PlayerStat'] as $stat){?>
				<?php if($stat['situation'] == $situation && $stat['close'] == $close){?>
					<tr>
						<td><a href="/player/detail/<?php echo $player['id']?>"><?php echo ucwords($player['name'])?></a></td>
						<td><?php echo $stat['cf']?></td>
						<td><?php echo $stat['ca']?></td>
						<td><?php echo $stat['cf_p']?></td>
						<td><?php echo $stat['ff']?></td>
						<td><?php echo $stat['fa']?></td>
						<td><?php echo $stat['ff_p']?></td>
						<td><?php echo $stat['dnz_a2sag']?></td>
						<td><?php echo $stat['oz_a2sag']?></td>
						<td><?php echo $stat['a2sag']?></td>
						<td><?php echo $stat['dnz_a2sg']?></td>
						<td><?php echo $stat['oz_a2sg']?></td>
						<td><?php echo $stat['a2sg']?></td>
						<td><?php echo $stat['dnz_sag']?></td>
						<td><?php echo $stat['oz_sag']?></td>
						<td><?php echo $stat['sc_sag']?></td>
						<td><?php echo $stat['sag']?></td>
						<td><?php echo $stat['dnz_sg']?></td>
						<td><?php echo $stat['oz_sg']?></td>
						<td><?php echo $stat['sc_sg']?></td>
						<td><?php echo $stat['sg']?></td>
						<td><?php echo $stat['a2_sage']?></td>
						<td><?php echo $stat['dnz_sage']?></td>
						<td><?php echo $stat['oz_sage']?></td>
						<td><?php echo $stat['sc_sage']?></td>
						<td><?php echo $stat['sage']?></td>
					</tr>
				<?php }?>
			<?php }?>
		<?php }?>
	<?php }?>
	</tbody>
</table>