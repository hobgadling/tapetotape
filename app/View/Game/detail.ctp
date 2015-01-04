<h1><?php echo $game['Team'][0]['team_name']?> vs <?php echo $game['Team'][1]['team_name']?></h1>
<h2><?php echo date('n/j/Y h:i',strtotime($game['Game']['event_date']));?></h2>

<div id="shot_table">
	<?php foreach($shots as $shot){?>
		<?php if(count($shot['Pass']) == 1){?>
				<?php switch($shot['Pass'][0]['location']){
					case 'D/NZ':
						echo "<div class='one_pass long bg-color-primary-0'>";
						break;
					case 'OZ':
						echo "<div class='one_pass med bg-color-primary-0'>";
						break;
					case 'SC':
						echo "<div class='one_pass short bg-color-primary-0'>";
						break;
				}?>
				<div class="pass" style="background-color: #<?php echo $shot['Player']['Team']['hex_color']?>">
					<a href="/player/detail/<?php echo $shot['Pass'][0]['Player']['id']?>"><?php echo ucwords($shot['Pass'][0]['Player']['name'])?></a>
				</div>
				<div class="shot" style="background-color: #<?php echo $shot['Player']['Team']['hex_color']?>">
					<a href="/player/detail/<?php echo $shot['Player']['id']?>"><?php echo ucwords($shot['Player']['name'])?></a><br />
					<?php echo $shot['Shot']['type']?>
				</div>
				<div class="time" style="background-color: #<?php echo $shot['Player']['Team']['hex_color']?>">
					<?php if($shot['Shot']['time'] < (20*60)){?>
						1st - <?php echo floor($shot['Shot']['time']/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php } else if($shot['Shot']['time'] < (40*60)){?>
						2nd - <?php echo floor(($shot['Shot']['time'] - (20*60))/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php } else if($shot['Shot']['time'] < (60*60)){?>
						3rd - <?php echo floor(($shot['Shot']['time'] - (40*60))/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php } else {?>
						OT - <?php echo floor(($shot['Shot']['time'] - (60*60))/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php }?>
				</div>
			</div>
		<?php } else {?>
			<?php switch($shot['Pass'][0]['location']){
					case 'D/NZ':
						echo "<div class='two_pass long bg-color-primary-1'>";
						break;
					case 'OZ':
						echo "<div class='two_pass med bg-color-primary-1'>";
						break;
					case 'SC':
						echo "<div class='two_pass short bg-color-primary-1'>";
						break;
				}?>
				<?php foreach($shot['Pass'] as $pass){?>
					<div class="pass <?php echo strtolower(str_replace('/','',$pass['location']))?>" style="background-color: #<?php echo $shot['Player']['Team']['hex_color']?>">
						<a href="/player/detail/<?php echo $pass['Player']['id']?>"><?php echo ucwords($pass['Player']['name'])?></a>
					</div>
					
				<?php }?>
				<div class="shot" style="background-color: #<?php echo $shot['Player']['Team']['hex_color']?>">
					<a href="/player/detail/<?php echo $shot['Player']['id']?>"><?php echo ucwords($shot['Player']['name'])?></a><br />
					<?php echo $shot['Shot']['type']?>
				</div>
				<div class="time" style="background-color: #<?php echo $shot['Player']['Team']['hex_color']?>">
					<?php if($shot['Shot']['time'] < (20*60)){?>
						1st - <?php echo floor($shot['Shot']['time']/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php } else if($shot['Shot']['time'] < (40*60)){?>
						2nd - <?php echo floor(($shot['Shot']['time'] - (20*60))/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php } else if($shot['Shot']['time'] < (60*60)){?>
						3rd - <?php echo floor(($shot['Shot']['time'] - (40*60))/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php } else {?>
						OT - <?php echo floor(($shot['Shot']['time'] - (60*60))/60)?>:<?php echo ($shot['Shot']['time']%60 < 10 ? '0' : '')?><?php echo $shot['Shot']['time']%60?>
					<?php }?>
				</div>
			</div>
		<? }?>
	<?php }?>
	<div class="clear"></div>
</div>

Situation:

<div class="dropdown">
	<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
		<?php echo $situation?>
		<span class="caret"></span>
	</button>
	<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
		<li role="presentation"><a role="menuitem" class="situation" tabindex="-1" href="/game/detail/<?php echo $game['Game']['id']?>/All">All</a>
		<li role="presentation"><a role="menuitem" class="situation" tabindex="-1" href="/game/detail/<?php echo $game['Game']['id']?>/5v5">5v5</a>
		<li role="presentation"><a role="menuitem" class="situation" tabindex="-1" href="/game/detail/<?php echo $game['Game']['id']?>/5v4">5v4</a>
	</ul>
</div>

Score Close: <input type="checkbox" id="sc"<?php if($close == '1'){?>checked="checked"<?php }?> />

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
		<?php if($stat['situation'] == $situation && $stat['close'] == $close){?>
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
		<?php }?>
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
				<?php if($stat['Player']['team_id'] == $team['id'] && $stat['Player']['position'] != 'G' && $stat['situation'] == $situation && $stat['close'] == $close){?>
					<tr>
						<td><a href="/player/detail/<?php echo $stat['Player']['id']?>"><?php echo ucwords($stat['Player']['name'])?></a></td>
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