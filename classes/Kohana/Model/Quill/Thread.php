<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Thread model
 *
 * @package    Quill/models
 * @author     Maxim Kerstens 'happyDemon'
 * @copyright  (c) 2013 Maxim Kerstens
 * @license    MIT
 */
class Kohana_Model_Quill_Thread extends ORM {

	// Table specification
	protected $_table_name = 'quill_threads';

	protected $_table_columns = array(
		'id' => null,
		'location' => null,
		'title' => null,
		'description' => null,
		'status' => null,
		'topic_count' => null,
		'count_topics' => null,
		'record_last_topic' => null,
		'stickies' => null,
		'count_replies' => null,
		'record_last_post' => null
	);

	// Relationships
	protected $_has_many = array(
		'topics' => array('model' => 'Quill_Topic', 'foreign_key' => 'thread_id'),
	);

	public function rules()
	{
		return array(
			'location' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 60)),
			),
			'title' => array(
				array('min_length', array(':value', 3)),
				array('max_length', array(':value', 45)),
			),
			'description' => array(
				array('max_length', array(':value', 144)),
			),
			'status' => array(
				array('in_array', array(':value', array('open', 'closed'))),
			),
			'topics' => array(
				array('digit', array(':value')),
			),
		);
	}

	/**
	 * Add the optional last_topic column (only accessible if record_last_topic is true)
	 *
	 * @param string $column
	 * @return mixed|ORM
	 */
	public function get($column)
	{
		if(parent::get('record_last_topic') == 1 && $column == 'last_topic')
		{
			return ORM::factory('Quill_Topic')
				->where('thread_id', '=', $this->id)
				->order_by('updated_at', 'DESC')
				->limit(1)
				->find();
		}

		return parent::get($column);
	}

} // End Quill thread model
