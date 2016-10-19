<?php
function parseData($config, $data) {
	$time = 0;
	$date = 0;
	$lines = [];
	$fulltalks = '';
	$prev_event_id = 0;
	$colspan = 1;

	$languages = array(
		'en' => array(
			'name' => 'English',
			'locale' => 'en_US.UTF8'
		),
		'bg' => array(
			'name' => 'Български',
			'locale' => 'bg_BG.UTF8'
		)
	);

	/* We need to set these so we actually parse properly the dates. WP fucks up both. */
	date_default_timezone_set('Europe/Sofia');
	setlocale(LC_TIME, $languages[$config['lang']]['locale']);

	foreach ($data['slots'] as $slot) {
		$slotTime = $slot['starts_at'];
		$slotDate = date('d', $slotTime);
			
		if ($slotDate !== $date) {
			$lines[] = '<tr>';
			$lines[] = '<td>' . strftime('%d %B - %A', $slotTime) . '</td>';
			$lines[] = '<td colspan="3">&nbsp;</td>';
			$lines[] = '</tr>';
			
			$date = $slotDate;
		}
		
		if ($slotTime !== $time) {
			if ($time !== 0) {
				$lines[] = '</tr>';
			}
			
			$lines[] = '<tr>';
			$lines[] = '<td>' . date('H:i', $slot['starts_at']) . ' - ' . date('H:i', $slot['ends_at']) . '</td>';
			
			$time = $slotTime;
		}
		
		$eid = &$slot['event_id'];
		
		if (!array_key_exists($eid, $data['events'])) {
			continue;
		}
		
		$event = &$data['events'][$eid];
		
		if (is_null($eid)) {
			$lines[] = '<td>TBA</td>';
		}
		else {
			$title = mb_substr($event['title'], 0, $config['cut_len']) . (mb_strlen($event['title']) > $config['cut_len'] ? '...' : '');
			$speakers = '';
			
			if (count($event['participant_user_ids']) > 0) {
				$speakers = json_encode($event['participant_user_ids']) . '<br>';

				$spk = array();
				$speaker_name = array();
				foreach ($event['participant_user_ids'] as $uid) {
					/* The check for uid==4 is for us not to show the "Opefest Team" as a presenter for lunches, etc. */
					if ($uid == 4 || empty ($data['speakers'][$uid])) {
						continue;
					} else {
						/* TODO: fix the URL */
						$name = $data['speakers'][$uid]['first_name'] . ' ' . $data['speakers'][$uid]['last_name'];
						$spk[$uid] = '<a class="vt-p" href="#'. $name . '">' . $name . '</a>';
					}
				}
				$speakers = implode (', ', $spk);
			}
			
			
			/* Hack, we don't want language for the misc track. This is the same for all years. */
			if ('misc' !== $data['tracks'][$event['track_id']]['name']['en']) {
				$csslang = 'schedule-' . $event['language'];
			} else {
				$csslang = '';
			}
			$cssclass = &$data['tracks'][$event['track_id']]['css_class'];
			$style = ' class="' . $cssclass . ' ' . $csslang . '"';
			$content = '<a href=#lecture-' . $eid . '>' . htmlspecialchars($title) . '</a> <br>' . $speakers;


			/* these are done by $eid, as otherwise we get some talks more than once (for example the lunch) */
			$fulltalks .= '<section id="lecture-' . $eid . '">';
			/* We don't want '()' when we don't have a speaker name */
			$fulltalk_spkr = strlen($speakers)>1 ? ' (' . $speakers . ')' : '';
			$fulltalks .= '<p><strong>' . $event['title'] . ' ' . $fulltalk_spkr . '</strong></p>';
			$fulltalks .= '<p>' . $event['abstract'] . '</p>';
			$fulltalks .= '<div class="separator"></div></section>';

			if ($slot['event_id'] === $prev_event_id) {
				array_pop($lines);
				$lines[] = '<td' . $style . ' colspan="' . ++$colspan . '">' . $content . '</td>';
			}
			else {
				$lines[] = '<td' . $style . '>' . $content . '</td>';
				$colspan = 1;
			}
		}
		
		$prev_event_id = $slot['event_id'];
	}

	$lines[] = '</tr>';

	/* create the legend */
	$legend = '';

	foreach($data['tracks'] as $track) {
		$legend .= '<tr><td class="' . $track['css_class'] . '">' . $track['name'][$config['lang']] . '</td></tr>';
	}

	foreach ($languages as $code => $lang) {
		$legend .= '<tr><td class="schedule-' . $code . '">' . $lang['name'] . '</td></tr>';
	}

	$gspk = '<div class="grid members">';
	$fspk = '';
	$types = [
		'twitter' => [
			'class' => 'twitter',
			'url' => 'https://twitter.com/',
		],
		'github' => [
			'class' => 'github',
			'url' => 'https://github.com/',
		],
		'email' => [
			'class' => 'envelope',
			'url' => 'mailto:',
		],
	];

	foreach ($data['speakers'] as $speaker) {
		$name = $speaker['first_name'] . ' ' . $speaker['last_name'];

		$gspk .= '<div class="member col4">';
		$gspk .= '<a href="#' . $name . '">';
		$gspk .= '<img width="100" height="100" src="' . $config['cfp_url'] . $speaker['picture']['schedule']['url'].'" class="attachment-100x100 wp-post-image" alt="' . $name .'" />';
		$gspk .= '</a> </div>';

		$fspk .= '<div class="speaker" id="' . $name . '">';
		$fspk .= '<img width="100" height="100" src="' . $config['cfp_url'] . $speaker['picture']['schedule']['url'].'" class="attachment-100x100 wp-post-image" alt="' . $name .'" />'; 
		$fspk .= '<h3>' . $name . '</h3>';
		$fspk .= '<div class="icons">';
		
		foreach ($types as $type => $param) {
			if (!empty($speaker[$type])) {
				$fspk .= '<a href="' . $param['url'] . $speaker[$type] . '"><i class="fa fa-' . $param['class'] . '"></i></a>';
			}
		}
		
		$fspk .= '</div>';
		$fspk .= '<p>' . $speaker['biography'] . '</p>';
		$fspk .= '</div><div class="separator"></div>';
	}

	$gspk .= '</div>';

	return compact('lines', 'fulltalks', 'gspk', 'fspk', 'legend');
}
