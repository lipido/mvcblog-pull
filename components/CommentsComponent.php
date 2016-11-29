<?php
//file: /components/CommentsComponent.php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Post.php");
require_once(__DIR__."/../model/Comment.php");

require_once(__DIR__."/../model/PostMapper.php");
require_once(__DIR__."/../model/CommentMapper.php");


/**
* Class CommentsComponent
*
* Component for managing post comments.
*
* @author lipido <lipido@gmail.com>
*/
class CommentsComponent {

	/*
		since we do not specify any $scope, the scope is "request", so this
		component is created everytime an event refers or view refers to it.
	*/


	/// STATE

	/* We can use another components. In this case, the user component wil allow
	us to retrieve the current logged user */
	private $usersComponent;

	/* Validation errors during creating or editing */
	private $errors;

	/* The current comment being created */
	private $currentComment;

	public function __construct() {
		$this->usersComponent = ComponentFactory::getComponent("users");
	}

	public function getErrors() {
		return $this->errors;
	}

	public function getCurrentComment() {
		if ($this->currentComment == null) {
			$this->currentComment = new Comment();
		}
		return $this->currentComment;
	}

	/**
	* Event to add a comment to a post
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: Id of the post (via HTTP POST)</li>
	* <li>content: Content of the comment (via HTTP POST)</li>
	* </ul>
	*
	* @throws Exception if no user is in session
	* @throws Exception if no post exists with the provided post id
	* @throws Exception if no post id was given
	* @return string none if there are validation errors, and ":redirect" if the
	* addition was successful
	*/
	public function add() {
		if ($this->usersComponent->getCurrentUser() == null) {
			throw new Exception("Not in session. Adding posts requires login");
		}

		if (isset($_POST["id"])) { // reaching via HTTP Post...

			// Get the Post object from the database
			$postMapper = new PostMapper();

			$postid = $_POST["id"];
			$post = $postMapper->findById($postid);

			// Does the post exist?
			if ($post == NULL) {
				throw new Exception("no such post with id: ".$postid);
			}

			// Create and populate the Comment object
			$this->currentComment = new Comment();
			$this->currentComment->setContent($_POST["content"]);
			$this->currentComment->setAuthor($this->usersComponent->getCurrentUser());
			$this->currentComment->setPost($post);

			try {

				// validate Comment object
				$this->currentComment->checkIsValidForCreate(); // if it fails, ValidationException

				// save the Comment object into the database
				$commentMapper = new CommentMapper();
				$commentMapper->save($this->currentComment);

				return ":redirect";

			}catch(ValidationException $ex) {
				$this->errors = $ex->getErrors();
			}
		} else {
			throw new Exception("No such post id");
		}
	}
}
