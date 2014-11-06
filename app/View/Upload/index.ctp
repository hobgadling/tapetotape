<input type="text" class="datepicker" />

<?php foreach($games as $date=>$day_games){?>
	<div id="<?php echo $date?>" class="hidden game_list">
		<?php foreach($day_games as $game){?>
			<?php print_r($game);?>
			<?php foreach($game['Team'] as $team){?>
				<?php echo $team['team_name']?>/
			<?php }?><br />
		<? }?>
	</div>
<?php }?>