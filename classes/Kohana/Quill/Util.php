<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Quill utility class
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
	 * @param integer|Model_Quill_Topic $topic
	 * @param integer|Model_Quill_Thread|string $to
	 */
	public static function move_topic($topic, $to)
	{
		$from = null;

		if(Valid::digit($topic))
		{
			$topic = ORM::factory('Quill_Topic', $topic);
		}

		if(Valid::digit($to))
		{
			$to = ORM::factory('Quill_Thread', $to);
		}

		if( ! is_a($topic, 'Model_Quill_Topic') || ! $topic->loaded())
		{
			throw new Kohana_Exception('Specify a topic to move');
		}

		if( ! is_a($topic, 'Model_Quill_Thread') || ! $to->loaded())
		{
			throw new Kohana_Exception('Specify a thread to move this topic to');
		}

		if($topic->thread->location->count_topics == true)
		{
			$from = $topic->thread;
		}

		$topic->thread_id = $to->id;
		$topic->save();

		if($from != null)
		{
			$from->topic_count -= 1;
			$from->save();
		}

		if($to->location->count_topics == true)
		{
			$to->topic_count += 1;
			$to->save();
		}
	}

	/**
	 * Move all topics from a threads to another.
	 *
	 * @param array $from
	 * @param integer|Model_Quill_Thread $to
	 */
	public static function move_topics(Array $from, $to)
	{

	}

	/**
	 * Recount all topics and/or replies in a thread or location.
	 */
	public static function recount()
	{

	}

	/**
	 * Remove any topics and replies with their status marked as deleted from the database.
	 *
	 * @param integer|Model_Quill_Thread|array $thread_id an id, thread instance or an array of IDs
	 * @return array How many topics and replies were deleted.
	 */
	public static function clean($thread_id)
	{
		if(is_a($thread_id, 'Model_Quill_Thread'))
		{
			$thread_id = $thread_id->id;
		}

		$operator = (is_array($thread_id)) ? 'IN' : '=';

		$return = array();

		$return['topics'] = DB::delete('quill_topics')
			->where('status', '=', 'deleted')
			->where('thread_id', $operator, $thread_id)
			->execute();

		$return['replies'] = DB::delete('quill_replies')
			->join('quill_topics')
			->on('quill_replies.topic_id', '=', 'quill_topics.id')
			->where('quill_topics.thread_id', $operator, $thread_id)
			->where('quill_replies.status', '=', 'deleted')
			->execture();

		return $return;
	}

	/**
	 * Merge 2 replies into 1.
	 *
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