<?php
//file: components/PostsComponent.php

require_once(__DIR__."/../model/Comment.php");
require_once(__DIR__."/../model/Post.php");
require_once(__DIR__."/../model/PostMapper.php");
require_once(__DIR__."/../model/User.php");


require_once(__DIR__."/UsersComponent.php");


/**
* Class PostsComponent
*
* Component to make a CRUDL of Posts entities
*
* @author lipido <lipido@gmail.com>
*/
class PostsComponent {

	/*
		Scope "view". This component will be preserved while the user is adding,
		editing and deleting posts, since these events do not send you to another
		view.
	*/
	public static $scope = "view";


	/// STATE

	/* We can use another components. In this case, the user component wil allow
	us to retrieve the current logged user */
	private $usersComponent;

	/* The current post being created or edited */
	private $currentPost;

	/* Validation errors during creating or editing */
	private $errors;

	/* boolean indicating edit state (edit=false, means adding new post) */
	private $edit;

	public function __construct() {
		$this->errors = array();
		$this->usersComponent = ComponentFactory::getComponent("users");

		// Initialize currentCompnent with a post if a postid is given in the request
		// This is useful for the view-post view.
		if (isset($_REQUEST["postid"])) {
			$postMapper = new PostMapper();
			$this->currentPost = $postMapper->findByIdWithComments($_REQUEST["postid"]);
		} else {
			$this->currentPost = new Post();
		}

		$this->edit = false;
	}

	public function getCurrentPost() {
		return $this->currentPost;
	}

	public function getErrors() {
		return $this->errors;
	}

	public function isEditing() {
		return $this->edit;
	}

	/**
	* Find all posts in the database.
	*
	* @return mixed An array of Post objects.
	*/
	public function getAllPosts() {
		$postMapper = new PostMapper();
		return $postMapper->findAll();
	}


	/// EVENTS


	/**
	* Event to add a new post
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>title: Title of the post (via HTTP POST)</li>
	* <li>content: Content of the post (via HTTP POST)</li>
	* </ul>
	*
	* @throws Exception if no user is in session
	* @return string none if there are validation errors, and ":redirect" if the
	* addition was successful
	*/
	public function add() {
		if ($this->usersComponent->getCurrentUser() == null) {
			throw new Exception("Not in session. Adding posts requires login");
		}

		$postMapper = new PostMapper();

		// populate the Post object with data form the form
		$this->currentPost->setTitle($_POST["title"]);
		$this->currentPost->setContent($_POST["content"]);

		// The user of the Post is the currentUser (user in session)
		$this->currentPost->setAuthor($this->usersComponent->getCurrentUser());

		try {
			// validate Post object
			$this->currentPost->checkIsValidForCreate(); // if it fails, ValidationException

			// save the Post object into the database
			$postMapper->save($this->currentPost);

			// change our state
			$this->currentPost = new Post();

			// return to the same page, but via 302 redirect to implement
			// POST-REDIRECT-GET
			return ":redirect";

		} catch(ValidationException $ex) {
			// Get the errors array inside the exepction...
			$this->errors = $ex->getErrors();
		}
	}

	/**
	* Event to save the current editing post
	*
	* The currentPost should be setted previously (which contains the id)
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>title: Title of the post (via HTTP POST)</li>
	* <li>content: Content of the post (via HTTP POST)</li>
	* </ul>
	*
	* @throws Exception if no post with a id was set
	* @throws Exception if no user is in session
	* @throws Exception if the current logged user is not the author of the post
	* @return string none if there are validation errors, and ":redirect" if the
	* addition was successful
	*/
	public function saveEdit() {
		// Are we editing something?
		if ($this->getCurrentPost() == NULL
			|| $this->getCurrentPost()->getId() == NULL) {
			throw new Exception("No editing any post");
		}
		// Check if the Post author is the currentUser (in Session)
		if ($this->currentPost->getAuthor() != $this->usersComponent->getCurrentUser()) {
			throw new Exception(
			"logged user is not the author of the post id: ".
			$this->getCurrentPost()->getId());
		}

		// populate the Post object with data form the form
		$this->currentPost->setTitle($_POST["title"]);
		$this->currentPost->setContent($_POST["content"]);

		try {
			// validate Post object
			$this->currentPost->checkIsValidForUpdate(); // if it fails, ValidationException

			// update the Post object in the database
			$postMapper = new PostMapper();
			$postMapper->update($this->currentPost);

			// change our state
			$this->currentPost = new Post();
			$this->edit = false;

			// return to the same page, but via 302 redirect to implement
			// POST-REDIRECT-GET
			return ":redirect";

		} catch(ValidationException $ex) {
			// Get the errors array inside the exepction
			// and set them in our state
			$this->errors = $ex->getErrors();
		}
	}

	/**
	* Event to start editing a post which is setted as the currentPost
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: The id of the post to edit (via HTTP GET)</li>
	* </ul>
	*
	* @throws Exception if no post with the provided id is in the database
	* @return string always returns null, keeping you in the same view
	*/
	public function edit() {
		$postMapper = new PostMapper();

		$post = $postMapper->findByIdWithComments($_GET["id"]);

		if ($post == NULL) {
			throw new Exception("no such post with id: ".$postid);
		}

		// update state
		$this->errors = array();
		$this->currentPost =  $post;
		$this->edit = true;
	}

	/**
	* Event to delete a post
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: Id of the post (via HTTP POST)</li>
	* </ul>
	*
	* @throws Exception if no id was provided
	* @throws Exception if no user is in session
	* @throws Exception if there is not any post with the provided id
	* @throws Exception if the author of the post to be deleted is not the current user
	* @return string always return ":redirect" if the deletion was successful
	*/
	public function delete() {
		if (!isset($_POST["id"])) {
			throw new Exception("id is mandatory");
		}
		if ($this->usersComponent->getCurrentUser() == null) {
			throw new Exception("Not in session. Deleting posts requires login");
		}

		$postMapper = new PostMapper();

		// Get the Post object from the database
		$postid = $_REQUEST["id"];
		$post = $postMapper->findById($postid);

		// Does the post exist?
		if ($post == NULL) {
			throw new Exception("no such post with id: ".$postid);
		}

		// Check if the Post author is the currentUser (in Session)
		if ($post->getAuthor() != $this->usersComponent->getCurrentUser()) {
			throw new Exception("Post author is not the logged user");
		}

		// Delete the Post object from the database
		$postMapper->delete($post);

		// return to the same page, but via 302 redirect to implement
		// POST-REDIRECT-GET
		return ":redirect";
	}
}
