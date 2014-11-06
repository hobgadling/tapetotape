Please choose a season:
<?php foreach($seasons as $season){?>
<br /><a href="/input/season/<?php echo $season['Season']['id']?>"><?php echo $season['Season']['start_year'] . '-' . $season['Season']['end_year']?></a>
<?php }?>