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

	protected $_table_name = 'quill_topics';

	// Relationships
	protected $_belongs_to = array(
		'thread' => array('model' => 'Quill_Thread', 'foreign_key' => 'thread_id'),
		'user' => array('model' => 'User', 'foreign_key' => 'user_id'),
		'last_user' => array('model' => 'User', 'foreign_key' => 'last_post_user_id')
	);

	protected $_has_many = array(
		'replies' => array('model' => 'Quill_Reply', 'foreign_key' => 'topic_id')
	);

	protected $_load_with = array('user', 'last_user');

	protected $_created_column = array('column' => 'created_at', 'format' => '');
	protected $_updated_column = array('column' => 'updated_at', 'format' => '');

	public function __construct($id = NULL)
	{
		//set the time formats
		$format = Kohana::$config->load('quill.time_format');
		$this->_created_column['format'] = $format;
		$this->_updated_column['format'] = $format;

		parent::__construct($id);
	}

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
				array('in_array', array(':value', array('open', 'closed', 'deleted'))),
			)
		);
	}

} // End Quill topic model
