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
		'status' => null
	);

	// Relationships
	protected $_belongs_to = array(
		'topic' => array('model' => 'Quill_Topic', 'foreign_key' => 'topic_id'),
		'user' => array('model' => 'User', 'foreign_key' => 'user_id')
	);

	protected $_load_with = array('user');

	// Auto-update time column
	protected $_created_column = array('column' => 'created_at', 'format' => true);

	public function rules()
	{
		return array(
			'content' => array(
				array('not_empty')
			),
			'status' => array(
				array('in_array', array(':value', array('active', 'deleted'))),
			),
			'user_id' => array(
				array('not_empty')
			),
			'topic_id' => array(
				array('not_empty')
			),
		);
	}

	/**
	 * Automatically 'touch' the related topic if needed.
	 *
	 * @param Validation $validation
	 * @param bool $touch_topic
	 * @return ORM
	 */
	public function save(Validation $validation=null, $touch_topic=false)
	{
		if($touch_topic == true)
		{
			$this->topic->updated_at = time();
			$this->topic->save();
		}

		return parent::save($validation);
	}

	/**
	 * When deleting a reply, check if topic reply_count should be recalculated.
	 * @return ORM
	 */
	public function delete()
	{
		if($this->topic->category->location->count_replies == 1)
		{
			$this->topic->reply_count = DB::select(array(DB::expr('COUNT(*)'), 'replies'))
				->from('quill_replies')
				->where('topic_id', '=', $this->topic_id)
				->where('status', '=', 'active')
				->execute()
				->get('replies') - 1;

			$this->topic->save();
		}

		//check if the last posted user is still correct
		if($this->topic->category->location->record_last_post == 1)
		{
			$last_post = $this->topic->replies->where('quill_reply.id', '!=', $this->id)->order_by('created_at', 'DESC')->find();

			if($last_post->user_id != $this->topic->last_post_user_id)
			{
				$this->topic->last_post_user_id = $last_post->user_id;
				$this->topic->save(null, false);
			}

		}

		return parent::delete();
	}

	public function get($col)
	{
		if($col == $this->_created_column['column'])
		{
			return date(Kohana::$config->load('quill.time_format'), parent::get($col));
		}

		return parent::get($col);
	}
} // End Quill reply model
