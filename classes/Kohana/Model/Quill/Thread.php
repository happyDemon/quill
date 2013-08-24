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

	protected $_table_name = 'quill_threads';

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
			)
		);
	}

} // End Quill thread model
