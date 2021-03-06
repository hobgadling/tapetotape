<!DOCTYPE html>
<html>
<head>
	<title>Hockey Passing Stats</title>
	<link rel="stylesheet" type="text/css" href="/css/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="/css/bootstrap/datepicker/css/datepicker.css" />
	<link rel="stylesheet" type="text/css" href="/js/jquery.tablesorter/themes/blue/style.css" />
	<link rel="stylesheet" type="text/css" href="/css/colors.css" />
	<link rel="stylesheet" type="text/css" href="/css/default.css" />
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="/css/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/css/bootstrap/datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="/js/jquery.tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="/js/default.js"></script>	
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<a class="navbar-brand" href="/">HPS.com</a>
		</div>
	</nav>
	<div id="container">
	     <?php echo $this->fetch('content'); ?>
	</div>
	<nav>
		&copy;2015 HockeyPassingStats.com
	</footer>
</body>
</html>
