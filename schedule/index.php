<?php
error_reporting(~0);
ini_set('display_errors', 1);

define('SCHED_LANG', 'bg');
?>
<html>
	<head>
		<title>Test schedule</title>
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="http://www.openfest.org/2014/wp-content/themes/initfest/style.css" />
	</head>
	<body>
		<pre>
<?php
$content = require __DIR__ . DIRECTORY_SEPARATOR . 'parse.php';
?>
		</pre>
		<table border="1" style="text-align: center;">
			<thead>
				<tr>
					<td>&nbsp;</td>
<?php
foreach ($content['halls'] as $hall_name) {
?>
					<td><?php echo htmlspecialchars($hall_name[SCHED_LANG]); ?></td>
<?php
}
?>
				</tr>
			</thead>
			<tbody>
<?php
foreach ($content['lines'] as $line) {
	echo $line, PHP_EOL;
}
?>
			</tbody>
		</table>
		<div class="separator"></div>
		<table border="1">
			<tbody>
<?php
foreach ($content['legend'] as $line) {
	echo $line, PHP_EOL;
}
?>
			</tbody>
		</table>
<?php
foreach ($content['fulltalks'] as $line) {
	echo $line, PHP_EOL;
}

foreach ($content['gspk'] as $line) {
	echo $line, PHP_EOL;
}

foreach ($content['fspk'] as $line) {
	echo $line, PHP_EOL;
}
?>
	</body>
</html>
