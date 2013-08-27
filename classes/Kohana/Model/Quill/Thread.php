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

	protected $_load_with = array('location');

	public function rules()
	{
		return array(
			'location_id' => array(
				array('not_empty')
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
		if($column == 'last_topic' && parent::get('location')->record_last_topic == 1)
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
