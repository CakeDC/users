<?php
/**
 * Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2011, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('UsersAppModel', 'Users.Model');

/**
 * Users Detail Model
 *
 * @package users
 * @subpackage users.models
 */
class UserDetail extends UsersAppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'UserDetail';

/**
 * Displayfield
 *
 * @var string
 */
	public $displayField = 'field';

/**
 * Validation rules for the virtual fields for each section
 *
 * @var array
 */
	public $sectionValidation = array();

/**
 * Used to declare field types for each section to make validation work on the virtual fields
 *
 * @var array
 */
	public $sectionSchema = array();

/**
 * Constructor
 *
 * @param string $id ID
 * @param string $table Table
 * @param string $ds Datasource
 */
	public function __construct($id = false, $table = null, $ds = null) {
		$userClass = Configure::read('App.UserClass');
		if (empty($userClass)) {
			$userClass = 'Users.User';
		}
		$this->belongsTo['User'] = array(
			'className' => $userClass,
			'foreignKey' => 'user_id');
		parent::__construct($id, $table, $ds);
	}

/**
 * Create the default fields for a user
 *
 * @param string $userId User ID
 * @return void
 */
	public function createDefaults($userId) {
		$entries = array(
			array(
				'field' => 'user.firstname',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.middlename',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.lastname',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.abbr-country-name',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.abbr-region',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.country-name',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.location',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.postal-code',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.region',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'),
			array(
				'field' => 'user.timeoffset',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string'));

		$i = 0;
		$data = array();
		foreach ($entries as $entry) {
			$data[$this->alias] = $entry;
			$data[$this->alias]['user_id'] = $userId;
			$data[$this->alias]['position'] = $i++;
			$this->create();
			$this->save($data);
		}
	}

/**
 * Returns details for named section
 *
 * @var string $userId User ID
 * @var string $section Section name
 * @return array
 */
	public function getSection($userId = null, $section = null) {
		$conditions = array(
			"{$this->alias}.user_id" => $userId);

		if (!is_null($section)) {
			$conditions["{$this->alias}.field LIKE"] = $section . '.%'; 
		}

		$results = $this->find('all', array(
			'recursive' => -1,
			'conditions' => $conditions,
			'fields' => array("{$this->alias}.field", "{$this->alias}.value")));

		if (!empty($results)) {
			foreach($results as $result) {
				list($prefix, $field) = explode('.', $result[$this->alias]['field']);
				$userDetails[$prefix][$field] = $result[$this->alias]['value'];
			}
			$results = $userDetails;
		}
		return $results;
	}

/**
 * Overriding this method to inject the active section schema
 *
 * @param mixed $field Set to true to reload schema, or a string to return a specific field
 * @return array Array of table metadata
 */
	public function schema($field = false) {
		if (isset($this->activeSectionSchema) && !empty($this->sectionSchema[$this->activeSectionSchema])) {
			return $this->sectionSchema[$this->activeSectionSchema];
		}
		return parent::schema($field);
	}

/**
 * Save details for named section
 * 
 * @var string $userId User ID
 * @var array $data Data
 * @var string $section Section name
 * @return boolean True on successful validation and saving of the virtual fields
 */
	public function saveSection($userId = null, $data = null, $section = null) {
		if (!empty($this->sectionSchema[$section])) {
			$this->activeSectionSchema = $section;

			foreach($data as $model => $userDetails) {
				if ($model == $this->alias) {
					foreach($userDetails as $key => $value) {
						$data[$model][$key] = $this->deconstruct($key, $value);
					}
				}
			}
		}

		if (!empty($this->sectionValidation[$section])) {
			$tmpValidate = $this->validate;
			$data = $this->set($data);
			$this->validate = $this->sectionValidation[$section];
			if (!$this->validates()) {
				return false;
			}
			$this->validate = $tmpValidate;
		}

		if (isset($this->activeSectionSchema)) {
			unset($this->activeSectionSchema);
		}

		if (!empty($data) && is_array($data)) {
			foreach($data as $model => $userDetails) {
				if ($model == $this->alias) {
					// Save the details
					foreach($userDetails as $key => $value) {
						$newUserDetail = array();
						$field = $section . '.' . $key;
						$userDetail = $this->find('first', array(
							'recursive' => -1,
							'conditions' => array(
								'user_id' => $userId,
								'field' => $field),
							'fields' => array('id', 'field')));
						if (empty($userDetail)) {
							$this->create();
							$newUserDetail[$model]['user_id'] = $userId;
						} else {
							$newUserDetail[$model]['id'] = $userDetail[$this->alias]['id'];
						}

						$newUserDetail[$model]['field'] = $field;
						$newUserDetail[$model]['value'] = $value;
						$newUserDetail[$model]['input'] = '';
						$newUserDetail[$model]['data_type'] = '';
						$newUserDetail[$model]['label'] = '';
						$this->save($newUserDetail, false);
					}
				} elseif (isset($this->{$model})) {
					// Save other model data
					$toSave[$model] = $userDetails;
					if (!empty($userId)) {
						if ($model == 'User') {
							$toSave[$model]['id'] = $userId;
						} elseif ($this->{$model}->hasField('user_id')) {
							$toSave[$model]['user_id'] = $userId;
						}
					}
					$this->{$model}->save($toSave, false);
				}
			}
		}
		return true;
	}
}
;