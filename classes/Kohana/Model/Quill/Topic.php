<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Topic model
 *
 * @package    Quill/models
 * @author     Maxim Kerstens 'happyDemon'
 * @copyright  (c) 2013 Maxim Kerstens
 * @license    MIT
 */
class Kohana_Model_Quill_Topic extends ORM {

	// Table specification
	protected $_table_name = 'quill_topics';

	protected $_table_columns = array(
		'id' => null,
		'category_id' => null,
		'user_id' => null,
		'title' => null,
		'content' => null,
		'status' => null,
		'stickied' => null,
		'created_at' => null,
		'last_post_user_id' => null,
		'updated_at' => null,
		'reply_count' => null,
	);

	// Relationships
	protected $_belongs_to = array(
		'category' => array('model' => 'Quill_Category', 'foreign_key' => 'category_id'),
		'user' => array('model' => 'User', 'foreign_key' => 'user_id'),
		'last_user' => array('model' => 'User', 'foreign_key' => 'last_post_user_id')
	);

	protected $_has_many = array(
		'replies' => array('model' => 'Quill_Reply', 'foreign_key' => 'topic_id')
	);

	protected $_load_with = array('user', 'last_user');

	// Auto-update time columns
	protected $_created_column = array('column' => 'created_at', 'format' => true);
	protected $_updated_column = array('column' => 'updated_at', 'format' => true);

	public function rules()
	{
		return array(
			'content' => array(
				array('not_empty')
			),
			'title' => array(
				array('not_empty'),
				array('min_length', array(':value', 3)),
				array('max_length', array(':value', 45)),
			),
			'status' => array(
				array('in_array', array(':value', array('active', 'archived', 'deleted'))),
			),
			'category_id' => array(
				array('not_empty')
			),
			'user_id' => array(
				array('not_empty')
			)
		);
	}

	/**
	 * Load all replies related to this topic.
	 *
	 * @param bool $find Execute the query when returning or not (when not doing so you could paginate the results)
	 * @param string $status Which status do the replies need to have active|deleted (false for any status)
	 * @return mixed
	 */
	public function replies($find=true, $status='active')
	{
		$replies = $this->replies;

		if($status != false)
		{
			$replies->where('status', '=', $status);
		}

		$replies->order_by('created_at', 'ASC');

		return ($find == true) ? $replies->find_all() : $replies;
	}

	/**
	 * Move this topic to a different category.
	 *
	 * @param integer|Kohana_Model_Quill_Category $location
	 * @throws Kohana_Exception
	 */
	public function move($location)
	{
		if(Valid::digit($location))
		{
			$location = ORM::factory('Quill_Category', $location);
		}

		if( ! is_a($location, 'Model_Quill_Category') || ! $location->loaded())
		{
			throw new Kohana_Exception('Specify a category to which you want to move topic ":id" to.', array(':id' => $this->id));
		}

		if($this->status == 'active')
		{
			$this->category->topic_count -= 1;
			$this->category->save();
		}

		$this->category_id = $location->id;
		$this->save();

		if($this->status == 'active')
		{
			$location->topic_count += 1;
			$location->save();
		}

		return $this;
	}

	/**
	 * When deleting a topic, check if topic topic_count should be recalculated.
	 * @return ORM
	 */
	public function delete()
	{
		if($this->category->location->count_topics == 1)
		{
			$this->category->topic_count = DB::select(array(DB::expr('COUNT(*)'), 'topics'))
				->from('quill_topics')
				->where('category_id', '=', $this->category_id)
				->where('status', '=', 'active')
				->execute()
				->get('topics') - 1;

			$this->category->save();
		}

		return parent::delete();
	}

	// Standardise the timestamp format when requested
	public function get($col)
	{
		if(in_array($col, array($this->_created_column['column'], $this->_updated_column['column'])))
		{
			return date(Kohana::$config->load('quill.time_format'), parent::get($col));
		}

		return parent::get($col);
	}
} // End Quill topic model
