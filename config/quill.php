<?php defined('SYSPATH' OR die('No direct access allowed.'));
/**
 * Quill config file
 */
return array(
	'time_format' => 'h:i a m/d/Y',
	'auto_create_location' => false,
	'default_user_id' => function() //Should always be an anonymous function do your logged in user check here and return the id or null
	{
		// If a user is logged in
		if(Sentry::check())
		{
			return Sentry::getUser()->id;
		}
		/*
		 * If you're using Kohana's Auth:
		 *
		 * $auth = Auth::instance();
		 * if ($auth->check())
		 * {
		 *      return $auth->get_user()->id;
		 * }
		 */

		//nope none is logged in
		return null;
	},
	'location_options' => array(
		'count_replies' => false, // Should topics in this location keep track of reply count?
		'count_topics' => false,  // Should threads in this location keep track of topic count?
		'count_views' => false, // Should topics in this location keep track of view count?
		'stickies' => false, // Are we able to sticky topics in this location
		'record_last_topic' => false, // Should threads in this location keep track of the last made topic
		'record_last_post' => false // Should topics in this location keep track of the user who made the last reply?
	)
);