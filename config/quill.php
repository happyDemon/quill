<?php defined('SYSPATH' OR die('No direct access allowed.'));
/**
 * Quill config file
 */
return array(
	'time_format' => 'h:i a m/d/Y',
	'auto_create_location' => false,
	'default_user_id' => 6,
	'location_options' => array(
		'count_replies' => false, // Should topics in this location keep track of reply count?
		'count_topics' => false,  // Should threads in this location keep track of topic count?
		'count_views' => false, // Should topics in this location keep track of view count?
		'stickies' => false, // Are we able to sticky topics in this location
		'record_last_topic' => false, // Should threads in this location keep track of the last made topic
		'record_last_post' => false // Should topics in this location keep track of the user who made the last reply?
	)
);