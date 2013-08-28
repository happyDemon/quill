<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'user_id' => array(
		'not_empty' => 'You need to be logged in to reply to a topic.'
	),
	'topic_id' => array(
		'not_empty' => 'I don\'t know where you\'re posting this topic.'
	),
	'content' => array(
		'not_empty' => 'Please provide some content for your topic.'
	)
);
