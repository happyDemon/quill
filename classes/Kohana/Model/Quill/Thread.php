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
		'location_id' => null,
		'title' => null,
		'description' => null,
		'status' => null,
		'topic_count' => null
	);

	// Relationships
	protected $_has_many = array(
		'topics' => array('model' => 'Quill_Topic', 'foreign_key' => 'thread_id'),
	);

	protected $_belongs_to = array(
		'location' => array('model' => 'Quill_Location', 'foreign_key' => 'location_id'),
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
			'topic_count' => array(
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
