<?php
App::uses('Component', 'Controller');
/**
 * Permissions component, will enabled you to implement CRUD methods on model level
 * 
 * @author Joris Blaak <joris@label305.com>
 * @requires CakePHP 2.x
 *
 * Copyright (c) 2013 Label305. All Rights Reserved.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY 
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 */
class PermissionsComponent extends Component {

	/**
	 * Model which has the current entity who needs rights
	 * @var String
	 */
	private $permissibleModel = null;

	/**
	 * Current entity who wants to have rights
	 * @var integer
	 */
	private $permissibleId = null;

	/**
	 * Settings:
	 *
	 * - accountModel, the model to check isAdmin on
	 * 
	 * @var array
	 */
	public $defaults = array(
		'accountModel' => 'User'
		);

	/**
	 * Merge passed settings
	 * @param ComponentCollection $collection 
	 * @param Array $settings 
	 * @param array               $settings   
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);

		//Merge settings
		$this->settings = Set::merge($this->defaults, $settings);
	}

	/**
	 * Initialize with controller
	 * @param  Controller $controller 
	 * @return void
	 */
	public function initialize(Controller $controller) {
		$this->controller = $controller;
	}

	/**
	 * Set the model that needs rights
	 * @param String $modelName 
	 */
	public function setPermissibleModel($modelName) {
		$this->permissibleModel = $modelName;
	}

	/**
	 * Getter for the permissible model, defaults to the 
	 * Form's userModel in the AuthComponent
	 * @return String
	 */
	public function getPermissibleModel() {
		$result = $this->permissibleModel;

		//If not explicitly set try use AuthComponent settings
		if(empty($result) && !empty($this->controller->Auth->settings['authenticate']['Form']['userModel'])) {
			$result = $this->controller->Auth->settings['authenticate']['Form']['userModel'];
		}

		return $result;
	}

	/**
	 * Set the id who wants to break out
	 * @param integer $id 
	 */
	public function setPermissibleId($id) {
		if(!empty($id))
			$this->permissibleId = $id;
	}	

	/**
	 * Getter for the permissible id, defaults to the logged in user
	 * if the currently set permissible model matches the AuthComponent's
	 * userModel
	 * @return integer
	 */
	public function getPermissibleId() {

		$result = $this->permissibleId;

		//Make sure the set id belongs to the set permissible model
		if(
			empty($result) 
			&& $this->controller->Auth->settings['authenticate']['Form']['userModel'] == $this->getPermissibleModel()
			&& $this->controller->Auth->loggedIn()
			) {
			$result = $this->controller->Auth->User('id');
		}

		return $result;
	}

	/**
	 * Checks if the current user is an admin, the settings "accountModel" model will decide this
	 *
	 * @todo  write test
	 * @todo  implement
	 * @return Boolean
	 */
	public function isAdmin() {
		if(!method_exists($this->controller->{$this->settings['accountModel']}, 'isAdmin')) {
			return false;
		}

		return $this->controller->Auth->loggedIn() && $this->controller->{$this->settings['accountModel']}->isAdmin($this->getPermissibleId());
	}

	/**
	 * The "C" in CRUD, checks if for rights
	 * @param  String $modelName name of the model to have rights over
	 * @param  Array  $options   (optional) extra options
	 * @return Boolean
	 */
	public function canCreate($modelName, $options = array()) {
		return $this->_getRights('canCreate', $modelName, $options);
	}

	/**
	 * The "R" in CRUD, checks if for rights
	 * @param  String $modelName name of the model to have rights over
	 * @param  Array  $options   (optional) extra options
	 * @return Boolean
	 */
	public function canRead($modelName, $options = array()) {
		return $this->_getRights('canRead', $modelName, $options);
	}

	/**
	 * The "U" in CRUD, checks if for rights
	 * @param  String $modelName name of the model to have rights over
	 * @param  Array  $options   (optional) extra options
	 * @return Boolean
	 */
	public function canUpdate($modelName, $options = array()) {
		return $this->_getRights('canUpdate', $modelName, $options);
	}

	/**
	 * The "D" in CRUD, checks if for rights
	 * @param  String $modelName name of the model to have rights over
	 * @param  Array  $options   (optional) extra options
	 * @return Boolean
	 */
	public function canDelete($modelName, $options = array()) {
		return $this->_getRights('canDelete', $modelName, $options);
	}

	/**
	 * Get the rights from the model
	 * @param  String $action     the CRUD method, e.g. canCreate
	 * @param  String $modelName  name of the model
	 * @param  String $options    (optional) extra options
	 * @return Boolean
	 */
	public function _getRights($action, $modelName, $options = array()) {
		if($this->isAdmin()) {
			return true;
		}

		//Normalize
		$options = $this->_normalizeOptions($options);

		//Make sure the model is present
		$this->controller->loadModel($modelName);

		//Check if we can call the method on the model
		if(!is_callable(array(get_class($this->controller->{$modelName}), $action))) {
			return false;
		}
		
		return $this->controller->{$modelName}->{$action}($options);
	}

	/**
	 * Normalizes the options
	 * @param  Mixed $options 
	 * @return Array
	 */
	public function _normalizeOptions($options) {
		if(is_numeric($options)) {
			$options = array('id' => $options);
		} else if(!is_array($options)) {
			$options = array();
		}

		//Set from existing data
		$options['PermissibleModel'] = $this->getPermissibleModel();
		$options['PermissibleId'] = $this->getPermissibleId();

		return $options;
	}
}