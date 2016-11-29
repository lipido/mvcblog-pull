<?php
//file: components/UsersComponent.php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");

/**
* Class UsersComponent
*
* Component to login, logout and user registration
*
* @author lipido <lipido@gmail.com>
*/
class UsersComponent {

	/*
		Scope "session". This component will be preserved during all the user
		session, because we want to keep the logged user in the currentUser property
	*/
	public static $scope = "session";


	/// STATE

	/*
		Validation errors during login
	*/
	private $loginErrors;

	/*
		Validation errors during registration
	*/
	private $registeringErrors;

	/*
		Current logged user
	*/
	private $currentUser;

	/*
		User being registered
	*/
	private $registeringUser;

	public function __construct() {
		$this->loginErrors = array();
		$this->registeringErrors = array();
	}


	public function getCurrentUser() {
		return $this->currentUser;
	}

	public function getRegisteringUser() {
		if ($this->registeringUser == null) {
			$this->registeringUser = new User();
		}
		return $this->registeringUser;
	}

	public function getLoginErrors() {
		return $this->loginErrors;
	}

	public function getRegisteringErrors() {
		return $this->registeringErrors;
	}


	/**
	* Gets the number of registered users in the application.
	*
	* @return int the number of registered users
	*/
	public function getUserCount() {
		$userMapper = new UserMapper();
		return $userMapper->userCount();
	}
	
	/**
	* Event to login
	*
	* Logins a user checking its creedentials against
	* the database
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>login: The username (via HTTP POST)</li>
	* <li>passwd: The password (via HTTP POST)</li>
	* </ul>
	*
	* @return string none if there are validation errors, and "posts:redirect"
	* if the addition was successful
	*/
	public function login() {
		$userMapper = new UserMapper();
		if (isset($_POST["username"])) { // reaching via HTTP Post...
			//process login form
			if ($userMapper->isValidUser($_POST["username"], $_POST["passwd"])) {
				// change our state
				$this->currentUser = new User($_POST["username"]);
				$this->loginErrors = array(); //clear errors

				// go to the "home", but via 302 redirect to implement
				// POST-REDIRECT-GET
				return "posts:redirect";
			} else {
				$this->loginErrors = array();
				$this->loginErrors["general"] = "Username is not valid";
			}
		}
	}

	/**
	* Event to register
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>login: The username (via HTTP POST)</li>
	* <li>passwd: The password (via HTTP POST)</li>
	* </ul>
	*
	* @return string none if there are validation errors, and "login:redirect" if the
	* addition was successful
	*/
	public function register() {
		$userMapper = new UserMapper();
		$this->registeringUser = new User();

		if (isset($_POST["username"])) { // reaching via HTTP Post...

			// populate the User object with data form the form
			$this->registeringUser->setUsername($_POST["username"]);
			$this->registeringUser->setPassword($_POST["passwd"]);

			try {
				$this->registeringUser->checkIsValidForRegister(); // if it fails, ValidationException

				// check if user exists in the database
				if (!$userMapper->usernameExists($_POST["username"])) {

					// save the User object into the database
					$userMapper->save($this->registeringUser);
					$this->registeringErrors = array(); //clear
					return "login:redirect";
				} else {
					$this->registeringErrors = array();
					$this->registeringErrors["username"] = "Username already exists";
				}
			} catch(ValidationException $ex) {
				// Get the errors array inside the exepction...
				$this->registeringErrors = $ex->getErrors();
			}
		}
	}

	/**
	* Event to logout
	*
	* No HTTP parameters are needed.
	*
	* @return string always return ":redirect" after logout
	*/
	public function logout() {
		$this->currentUser = null;
		//redirect to the current view
		return ":redirect";
	}
}
