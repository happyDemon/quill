<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Quill
 *
 * A portable discussion helper.
 *
 * @package    Quill
 * @author     Maxim Kerstens 'happyDemon'
 * @copyright  (c) 2013 Maxim Kerstens
 * @license    MIT
 */
class Kohana_Quill {

	/**
	 * Load all threads for a location.
	 *
	 * @param string $location Name of the location
	 * @param string $status Which status should the threads have? open|closed (false to ignore)
	 * @return array List of loaded Quill instances
	 */
	public static function threads($location, $status='open')
	{
		$threads = ORM::factory('Quill_Thread')->where('location', '=', $location);

		if($status != false)
		{
			$threads->where('status', '=', $status);
		}

		$threads = $threads->find_all();

		$list = array();

		if(count($threads) > 0)
		{
			foreach($threads as $thread)
			{
				$list[] = self::factory($thread);
			}
		}
		return $list;
	}

	/**
	 * Prepare a Quill instance.
	 *
	 * @param integer|string|Model_Quill_Thread $thread_id Which thread are we loading
	 * @param array $options see the config file for settable options
	 * @return Kohana_Quill
	 */
	public static function factory($thread_id, $options = array())
	{
		$quill = get_called_class();

		// merge given options with defaults defined in the config
		$options = array_merge(Kohana::$config->load('quill.default_options'), $options);

		// if an ID was specified
		if(Valid::digit($thread_id))
		{
			$thread = ORM::factory('Quill_Thread', $thread_id);
		}
		// if an instance was passed
		else if(is_a($thread_id, 'Model_Quill_Thread'))
		{
			$thread = $thread_id;
		}
		// otherwise a string to load from a location
		else
		{
			$thread = ORM::factory('Quill_Thread')->where('location', '=', $thread_id)->find();
		}

		return new $quill($thread, $options);
	}

	/**
	 * @var Model_Quill_Thread A Thread instance we can get info out of
	 */
	protected $_thread = null;

	public function __construct(Model_Quill_Thread $thread, $options)
	{
		if(!$thread->loaded())
		{
			//if auto_create_thread is enabled we'll at least need a location defined to do so.
			if(Kohana::$config->load('quill.auto_create_thread') == true && !empty($thread->location))
			{
				//set default option if not defined
				foreach($options as $opt => $value)
				{
					if(empty($thread->{$opt}))
					{
						$thread->set($opt, $value);
					}
				}
				$thread->save();
			}
			else
			{
				throw new Kohana_Exception('It seems like the thread you want to load does not exists');
			}
		}

		$this->_thread = $thread;
	}

	/**
	 * Return a topic in this thread.
	 *
	 * @param integer|string $id Which id are we looking for
	 * @param string $search_for Will we be checking the id or title column
	 * @param string|false $status Which status does the topic need to have active|archived|deleted (false for any status)
	 * @return Model_Quill_Topic
	 * @throws Kohana_Exception
	 */
	public function topic($id, $search_for='id', $status='active')
	{
		if(!in_array($search_for, array('id', 'title')))
		{
			throw new Kohana_Exception('You can only look for a topic based on id or title');
		}
		else
		{
			$search_for = 'quill_topic.'.$search_for;
		}

		$topic = $this->_thread->topics;

		if($status != false)
		{
			$topic->where('status', '=', $status);
		}

		$topic->where($search_for, '=', $id);

		return $topic->find();
	}

	/**
	 * Find topics for this thread.
	 *
	 * @param bool $find Execute the query when returning or not (when not doing so you could paginate the results)
	 * @param string $status Which status does the topic need to have active|archived|deleted (false for any status)
	 * @return Model_Quill_Topic
	 */
	public function topics($find=true, $status='active') {
		$topics = $this->_thread->topics;

		if($status != false)
		{
			$topics->where('status', '=', $status);
		}

		if($this->_thread->stickies == true)
		{
			$topics->order_by('stickied', 'DESC');
		}

		$topics->order_by('updated_at', 'DESC');

		return ($find == true) ? $topics->find_all() : $topics;
	}

	/**
	 * Create a topic for this thread.
	 *
	 * Required $value keys:
	 *  - user_id
	 *  - title
	 *  - content
	 *
	 * Optionally you can define the $value key 'stickied' (1 or 0) defaults to 0
	 *
	 * @param array $values
	 * @param Kohana_Validation|null $extra_validation
	 * @return Model_Quill_Topic
	 */
	public function create_topic(Array $values, $extra_validation=null)
	{
		// are we able to create a new topic in this thread
		if($this->_thread->status == 'closed')
		{
			throw new Kohana_Exception('You can not create a topic in a closed thread.');
		}

		if(!isset($values['user_id']))
		{
			$values['user_id'] = Kohana::$config->load('quill.default_user_id');
		}

		$values['thread_id'] = $this->_thread->id;

		// this is required for ordering topics, so set it to creation time
		$values['updated_at'] = date(Kohana::$config->load('quill.time_format'));

		// defaults
		$values['reply_count'] = 0;
		$values['status'] = 'active';

		// optional
		if(!isset($values['stickied']))
		{
			$values['stickied'] = 0;
		}

		// save the topic
		$topic = ORM::factory('Quill_Topic')
			->values($values, array('thread_id', 'user_id', 'title', 'content', 'status', 'stickied', 'updated_at', 'reply_count'))
			->save();

		// if we're keeping track of active topic count, update it
		if($this->_thread->count_topics == true)
		{
			$this->_thread->topic_count = DB::select(array(DB::expr('COUNT(*)'), 'topics'))
				->from('quill_topics')
				->where('thread_id', '=', $this->_thread->id)
				->where('status', '=', 'active')
				->execute()
				->get('topics');

			$this->_thread->save();
		}

		return $topic;
	}

	/**
	 * Create a reply for the provided topic.
	 *
	 * Required $values keys:
	 *  - user_id
	 *  - content
	 *
	 * Updates reply count for the topic, if enabled.
	 * Adds last_post_user_id if enabled.
	 *
	 * if none of the above is enabled it 'touches' the topic to update the column 'updated_at' for ordering.
	 *
	 * @param Model_Quill_Topic|int $topic_id
	 * @param array $values
	 * @param null|Kohana_Validation $extra_validation
	 * @return Model_Quill_Reply
	 * @throws Kohana_Exception
	 */
	public function create_reply($topic_id, $values, $extra_validation=null)
	{
		$topic = null;

		// Retrieve the topic
		if(is_a($topic_id, 'Model_Quill_Topic'))
		{
			$topic = $topic_id;
			$topic_id = $topic->id;
		}
		else if(!Valid::digit($topic))
		{
			throw new Kohana_Exception('The provided topic id is not a number.');
		}
		else
		{
			$topic = ORM::factory('Quill_Topic', $topic_id);
		}

		// check if the topic actually exists before going further
		if(!$topic->loaded())
		{
			throw new Kohana_Exception('There\'s no topic to reply to.');
		}

		// are we able to post a reply to the topic?
		if($topic->status != 'active')
		{
			throw new Kohana_Exception('You can\'t post a reply on a :status topic', array(':status' => $topic->status));
		}

		if(!isset($values['user_id']))
		{
			$values['user_id'] = Kohana::$config->load('quill.default_user_id');
		}

		$values['topic_id'] = $topic_id;

		// save the reply
		$reply = ORM::factory('Quill_Reply')
			->values($values, array('topic_id', 'user_id', 'content'))
			->save($extra_validation, ($this->_thread->count_replies || $this->_thread->record_last_post));

		// if we need to keep reply count, calculate before updating
		if($this->_thread->count_replies == true)
		{
			$count = DB::select(array(DB::expr('COUNT(*)'), 'replies'))
				->from('quill_replies')
				->where('topic_id', '=', $topic_id)
				->where('status', '=', 'active')
				->execute()
				->get('replies');

			$topic->reply_count = $count;
		}

		// if we need to record the last post's user
		if($this->_thread->record_last_post)
		{
			$topic->last_post_user_id = $values['user_id'];
		}

		$topic->save();

		return $reply;
	}

	public function __get($col)
	{
		return $this->_thread->get($col);
	}
}