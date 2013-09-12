<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'user_id' => array(
		'not_empty' => 'We don\'t know which user created this topic.'
	),
	'category_id' => array(
		'not_empty' => 'I don\'t know which category you\'re posting this topic to.'
	),
	'content' => array(
		'not_empty' => 'Please provide a message for your topic.'
	)
);
