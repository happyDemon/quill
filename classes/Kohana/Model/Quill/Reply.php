<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Reply model
 *
 * @package    Quill/models
 * @author     Maxim Kerstens 'happyDemon'
 * @copyright  (c) 2013 Maxim Kerstens
 * @license    MIT
 */
class Kohana_Model_Quill_Reply extends ORM {

	// Table specification
	protected $_table_name = 'quill_replies';

	protected $_table_columns = array(
		'id' => null,
		'user_id' => null,
		'topic_id' => null,
		'content' => null,
		'created_at' => null,
	);

	// Relationships
	protected $_belongs_to = array(
		'topic' => array('model' => 'Quill_Topic', 'foreign_key' => 'topic_id'),
		'user' => array('model' => 'User', 'foreign_key' => 'user_id')
	);

	protected $_load_with = array('user');

	// Auto-update time column
	protected $_created_column = array('column' => 'created_at', 'format' => '');

	public function __construct($id = NULL)
	{
		//set the time formats
		$format = Kohana::$config->load('quill.time_format');
		$this->_created_column['format'] = $format;

		parent::__construct($id);
	}

	public function rules()
	{
		return array(
			'content' => array(
				array('not_empty')
			)
		);
	}

} // End Quill reply model
