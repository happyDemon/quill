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
	 * Load all categories for a location.
	 *
	 * @param string $location Name of the location
	 * @param string $status Which status should the categories have? open|closed (false to ignore)
	 * @return array List of loaded Quill instances
	 */
	public static function categories($location, $status='open', $options = array())
	{
		$categories = ORM::factory('Quill_Category')->where('location.name', '=', $location);

		if($status != false)
		{
			$categories->where('status', '=', $status);
		}

		$categories = $categories->find_all();

		$list = array();

		if(count($categories) > 0)
		{
			foreach($categories as $category)
			{
				$list[] = self::factory($category);
			}
		}
		else if(Kohana::$config->load('quill.auto_create_location') == true)
		{
			// merge given options with defaults defined in the config
			$options = array_merge(Kohana::$config->load('quill.default_options'), $options);

			$loc = ORM::factory('Quill_Location', array('name' => $location));

			//if there's no existing location we'll create it with the provided or default options
			if(!$loc->loaded())
			{
				$options['name'] = $location;
				$loc->reset()
					->values($options)
					->save();
			}
		}

		return $list;
	}

	/**
	 * Prepare a Quill instance.
	 *
	 * @param integer|string|Model_Quill_Category $category_id Which category are we loading
	 * @return Kohana_Quill
	 */
	public static function factory($category_id)
	{
		$quill = get_called_class();

		// if an ID was specified
		if(Valid::digit($category_id))
		{
			$category = ORM::factory('Quill_Category', $category_id);
		}
		// if an instance was passed
		else if(is_a($category_id, 'Kohana_Model_Quill_Category'))
		{
			$category = $category_id;
		}
		// otherwise a string of the name of the category
		else
		{
			$category = ORM::factory('Quill_Category')->where('title', '=', $category_id)->find();
		}

		return new $quill($category);
	}

	/**
	 * @var Model_Quill_Category A Category instance we can get info out of
	 */
	protected $_category = null;

	public function __construct(Model_Quill_Category $category)
	{
		if(!$category->loaded())
		{
			throw new Kohana_Exception('It seems like the category you want to load does not exists');
		}

		$this->_category = $category;
	}

	/**
	 * Return a topic in this category.
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

		$topic = $this->_category->topics;

		if($status == null)
		{
			$topic->where('status', '!=', 'deleted');
		}
		else if($status != false)
		{
			$topic->where('status', '=', $status);
		}

		$topic->where($search_for, '=', $id);

		return $topic->find();
	}

	/**
	 * Find topics for this category.
	 *
	 * @param bool $find Execute the query when returning or not (when not doing so you could paginate the results before executing it yourself)
	 * @param string $status Which status does the topic need to have active|archived|deleted (false for any status, null for none-deleted)
	 * @return Model_Quill_Topic
	 */
	public function topics($find=true, $status='active', $order=true)
	{
		$topics = $this->_category->topics;

		if($status == null)
		{
			$topics->where('quill_topic.status', '!=', 'deleted');
		}
		else if($status != false)
		{
			$topics->where('quill_topic.status', '=', $status);
		}

		if($this->_category->location->stickies == true)
		{
			$topics->order_by('quill_topic.stickied', 'DESC');
		}

		if($order == true)
		{
			$topics->order_by('quill_topic.updated_at', 'DESC');
		}

		return ($find == true) ? $topics->find_all() : $topics;
	}

	/**
	 * Create a topic for this category.
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
		// are we able to create a new topic in this category
		if($this->_category->status == 'closed')
		{
			throw new Kohana_Exception('You can not create a topic in a closed category.');
		}

		if(!isset($values['user_id']))
		{
			$user = Kohana::$config->load('quill.default_user_id');
			$values['user_id'] = $user();
		}

		$values['category_id'] = $this->_category->id;

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
			->values($values, array('category_id', 'user_id', 'title', 'content', 'status', 'stickied', 'updated_at', 'reply_count'))
			->save($extra_validation);

		// if we're keeping track of active topic count, update it
		if($this->_category->location->count_topics == true)
		{
			$this->_category->topic_count = DB::select(array(DB::expr('COUNT(*)'), 'topics'))
				->from('quill_topics')
				->where('category_id', '=', $this->_category->id)
				->where('status', '=', 'active')
				->execute()
				->get('topics');

			$this->_category->save();
		}

		// If Kohana-Plugin-System was installed fire a topic event
		// by supplying the topic and the user
		if(class_exists('Plug'))
		{
			Plug::fire('quill.topic', array($topic, $topic->user));
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
		if(is_a($topic_id, 'Kohana_Model_Quill_Topic'))
		{
			$topic = $topic_id;
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

		return $topic->create_reply($values, $extra_validation);
	}

	public function __get($col)
	{
		return $this->_category->get($col);
	}
}