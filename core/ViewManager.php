<?php
// file: /core/ViewManager.php

/**
* Class ViewManager
*
* This class implements a layout engine, which responibility is:
*
* 1.Render views. This basically performs an 'include' of the view
*	 file, but with more MVC-oriented parameters
*	 (controller name and view name).
*
* 2.Layout (or templating) system. Based on PHP output buffers
*	 (ob_ functions). Once the view manager is initialized,
*	 the output buffer is enabled. By default, all contents that are
*	 generated inside your views will be saved in a DEFAULT_FRAGMENT.
*	 The DEFAULT_FRAGMENT is normally used as the "main" content of
*	 the resulting layout. However, you can generate contents for
*	 other fragments that will go into the layout. For example, inside
*	 your views, you have to call moveToFragment(fragmentName) before
*	 generating content for a desired fragment. This fragment normally
*	 will be after retrieved by the layout (via calls to getFragment).
*	 Typical fragments are 'css', 'javascript', so you can specify
*	 additional css and javascripts from your specific views.
*
* @author lipido <lipido@gmail.com>
*/
class ViewManager {

	/**
	* key for the default fragment
	*
	* @var string
	*/
	const DEFAULT_FRAGMENT = "__default__";

	/**
	* Buffered contents accumulted per each fragment
	*
	* @var mixed
	*/
	private $fragmentContents = array();

	/**
	* Values of view variables
	*
	* @var mixed
	*/
	private $variables = array();

	/**
	* The current fragment name where output is being
	* accumulated
	*
	* @var string
	*/
	private $currentFragment = self::DEFAULT_FRAGMENT;

	/**
	* The name of the layout to be used in renderLayout
	*
	* @var string
	*/
	private $layout = "default";


	private function __construct() {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		ob_start();
	}

	/// BUFFER MANAGEMENT
	/**
	* Saves the contents of the output buffer into
	* the current fragment. Cleans the ouput buffer
	*
	* @return void
	*/
	private function saveCurrentFragment() {
		//save current fragment
		$this->fragmentContents[$this->currentFragment].=ob_get_contents();
		//clean output buffer
		ob_clean();
	}

	/**
	* Changes the current fragment where output is accumulating
	*
	* The current output is saved before changing.
	* The subsequent outputs will be accumulted in the specified
	* fragment.
	*
	* @param string $name The name of the fragment to move to
	* @return void
	*/
	public function moveToFragment($name) {
		//save the current fragment contents
		$this->saveCurrentFragment();
		$this->currentFragment = $name;
	}

	/**
	* Changes to the default fragment.
	*
	* The current output is saved before changing.
	* The subsequent outputs will be accumulated in the default fragment
	*
	* @return void
	*/
	public function moveToDefaultFragment(){
		$this->moveToFragment(self::DEFAULT_FRAGMENT);
	}

	/**
	*	Shortcut method to set the content for a fragment directly without the need
	* to move to the specified fragment with moveToFragment(). The
	* current fragment is not changed.
	*
	* @param string fragmentName the fragment to set the content
	* @param string content the content for the fragment
	*/
	public function setFragmentContent($fragmentName, $content) {
		$this->fragmentContents[$fragmentName] = $content;
	}

	/**
	* Gets the contents occumulated in an specified fragment
	*
	* @param string $fragment The fragment to retrieve the contents from
	* @param string $default The default content if the $fragment does
	* not exist
	* @return string The fragment contents
	*/
	public function getFragment($fragment, $default="") {
		if (!isset($this->fragmentContents[$fragment])) {
			return $default;
		}
		return $this->fragmentContents[$fragment];
	}

	/// RENDERING

	/**
	* Sets the layout to be used when renderLayout will be called
	*
	* @param string $layout The layout to use
	* @return void
	*/
	public function setLayout($layout) {
		$this->layout = $layout;
	}

	/**
	* Renders an specified view
	*
	* If the $view=myview, the selected php file will be: view/myview.php
	*
	* It uses the the selected layout (via setLayout)
	* or the default layout if it was not specified before
	* calling the setLayout method
	*
	* @param string $viewname Name of the view
	* @return void
	*/
	public function render($viewname) {
		include(__DIR__."/../view/$viewname.php");
		$this->renderLayout();
	}

	/**
	* Sends an HTTP 302 redirection to a given view
	*
	* @param string $view The name of the action
	* @param string $queryString An optional query string
	* @return void
	*/
	public function redirect($view, $queryString=NULL) {
		header("Location: index.php?view=$view".(isset($queryString)?"&$queryString":""));
		die();
	}

	/**
	* Sends an HTTP 302 redirection to the refererring page, which
	* is the page where the user was, just before making the current
	* request.
	*
	* @param string $queryString An optional query string
	* @return void
	*/
	public function redirectToReferer($queryString=NULL) {
		//remove events in url, since we will call the event again
		$_SERVER["HTTP_REFERER"] = preg_replace("/event=[^&]*/", "", $_SERVER["HTTP_REFERER"]);
		$_SERVER["HTTP_REFERER"] = preg_replace("/component=[^&]*/", "", $_SERVER["HTTP_REFERER"]);
		$_SERVER["HTTP_REFERER"] = preg_replace("/&&/", "&", $_SERVER["HTTP_REFERER"]);

		header("Location: ".$_SERVER["HTTP_REFERER"].(isset($queryString)?"&$queryString":""));
		die();
	}

	/**
	* Renders the layout
	*
	* It basically includes the /view/layouts/[layout].php.
	* Normally, inside the layout file, there will be calls to
	* retrieve fragment contents, especially the default fragment
	* contents.
	*/
	private function renderLayout() {
		// move to layout fragment so
		// all previously generated output contents
		// were saved in the $this->fragmentContents
		// array
		$this->moveToFragment("layout");

		// draw the layout. Inside the layout we use this
		// view manager to retrieve previously generated contents,
		// specially the DEFAULT_FRAGMENT (the main content)
		include(__DIR__."/../view/layouts/".$this->layout.".php");

		ob_flush();
	}

	// singleton
	private static $viewmanager_singleton = NULL;
	public static function getInstance() {
		if (self::$viewmanager_singleton == null) {
			self::$viewmanager_singleton = new ViewManager();
		}
		return self::$viewmanager_singleton;
	}

}


// force the first instantiation of the ViewManager
// since the buffered output will be needed including
// those cases where neither the controller nor the view get the instance of the viewmanager
ViewManager::getInstance();
