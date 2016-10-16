<?php
function compareKeys($a, $b, $key) {
	$valA = &$a[$key];
	$valB = &$b[$key];
	
	return ($valA < $valB) ? -1 : (($valA > $valB) ? 1 : 0);
}

function loadData($config) {
	$base_url = $config['cfp_url'] . '/api/conferences/2/';

	$filenames = [
		'events'			=>	'events.json',
		'speakers'			=>	'speakers.json',
		'tracks'			=>	'tracks.json',
		'event_types'		=>	'event_types.json',
		'halls'				=>	'halls.json',
		'slots'				=>	'slots.json',
	];

	$data = [];

	foreach ($filenames as $name => $filename) {
		$curl = new SmartCurl($base_url);
		$json = $curl->getUrl($filename);

		if ($json === false) {
			echo 'get failed: ', $filename, PHP_EOL;
			exit;
		}
		
		$decoded = json_decode($json, true);

		if ($decoded === false) {
			echo 'decode failed: ', $filename, PHP_EOL;
			exit;
		}
		
		$add = true;
		
		switch ($name) {
			case 'halls':
				$decoded = array_map(function($el) {
					return $el['name'];
				}, $decoded);
			break;
			case 'slots':
				$decoded = array_map(function($el) {
					foreach (['starts_at', 'ends_at'] as $key) {
						$el[$key] = strtotime($el[$key]);
					}
					
					return $el;
				}, $decoded);
			break;
		}
		
		$data[$name] = $decoded;
	}

	uasort($data['slots'], function($a, $b) {
		return compareKeys($a, $b, 'starts_at') ?: compareKeys($a, $b, 'hall_id');
	});

	$data['halls'] = array_filter($data['halls'], function($key) use ($config) {
		return in_array($key, $config['allowedHallIds']);
	}, ARRAY_FILTER_USE_KEY);

	return $data;
}
