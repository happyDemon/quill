<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Quill utility class
 *
 *
 * @package    Quill
 * @author     Maxim Kerstens 'happyDemon'
 * @copyright  (c) 2013 Maxim Kerstens
 * @license    MIT
 */
class Kohana_Quill_Util {

	/**
	 * Move a topic from one thread to another.
	 *
	 * @param $topic
	 * @param $to
	 */
	public static function move_topic($topic, $to)
	{

	}

	/**
	 * Move all topics from a threads to another.
	 *
	 * @param $from
	 * @param $to
	 */
	public static function move_topics($from, $to)
	{

	}

	/**
	 * Recount all topics and/or replies in a thread.
	 */
	public static function recount()
	{

	}

	/**
	 * Remove any deleted topics and/or replies.
	 */
	public static function clean()
	{

	}

	/**
	 * @param integer|Model_Quill_Reply $reply
	 * @param bool $same_user Should the replies be made by the same user?
	 * @throws Kohana_Exception
	 */
	public static function merge_prev_reply($reply, $same_user=true)
	{
		// if an ID was specified
		if(Valid::digit($reply))
		{
			$reply = ORM::factory('Quill_Reply', $reply);
		}

		//if the model wasn't loaded
		if(! $reply->loaded())
		{
			throw new Kohana_Exception('No reply to merge');
		}

		//load the previous reply
		$prev_reply = ORM::factory('Quill_Reply')
			->where('id', '<', $reply->id)
			->where('topic_id', '=', $reply->topic_id)
			->order_by('id', 'DESC')
			->limit(1)
			->find();

		//check if it's there
		if( !$prev_reply->loaded())
		{
			throw new Kohana_Exception('No previous reply to merge with');
		}

		//check if the replies were made by the same user
		if($same_user == true && $reply->user_id != $prev_reply->user_id)
		{
			throw new Kohana_Exception('Both replies come from a different user.');
		}

		//if the content is different we'll append it
		if($prev_reply->content != $reply->content)
		{
			$reply->content .= '\n\n'.$prev_reply->content;
			$reply->save(null, false);
		}

		//delete the previous reply
		$prev_reply->delete();
	}
}