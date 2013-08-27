<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Location model
 *
 * @package    Quill/models
 * @author     Maxim Kerstens 'happyDemon'
 * @copyright  (c) 2013 Maxim Kerstens
 * @license    MIT
 */
class Kohana_Model_Quill_Location extends ORM {

	// Table specification
	protected $_table_name = 'quill_locations';

	protected $_table_columns = array(
		'id' => null,
		'name' => null,
		'count_topics' => null,
		'record_last_topic' => null,
		'stickies' => null,
		'count_replies' => null,
		'record_last_post' => null
	);

	// Relationships
	protected $_has_many = array(
		'threads' => array('model' => 'Quill_Thread', 'foreign_key' => 'location_id'),
	);

	public function rules()
	{
		return array(
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', 4)),
				array('max_length', array(':value', 60)),
			)
		);
	}

} // End Quill location model
