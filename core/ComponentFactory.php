<?php
//file: core/ComponentFactory

/**
 * Class ComponentFactory
 *
 * This class implements the means to instantiate components. Its main
 * responsibility is to manage the components scope in order. Scope is
 * indicated in the components in its public static $scope property.
 *
 * Components are requested by their "name". A class called NameComponent must be
 * available inside /components/NameComponent.php.
 *
 * Component scopes are:
 * - Request scope (default, no $scope property needed). The factory gives the same
 *		component during the request.
 * - View scope ($scope = "view"). The factory gives the same component while
 *		request goes to the same view (including redirects to the same view).
 * - Session scope ($scope = "session"). The factory gives the same component
 *		during all the user session.
 */
class ComponentFactory {

	/**
	* Retrieve or create the required component in its corresponding scope
	*
	* @param string $controllerName The controller name found in the URL
	* @return Object A Controller instance
	*/
	public static function getComponent($componentName) {

		$componentClassName = ComponentFactory::getComponentClassName($componentName);

		if (property_exists($componentClassName, "scope")
				&& $componentClassName::$scope == "session") {

			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}

			if (!isset($_SESSION['__COMPONENT__'.$componentClassName])) {
				echo ("creating session component");
				$_SESSION['__COMPONENT__'.$componentClassName] = new $componentClassName();
			}

			return $_SESSION['__COMPONENT__'.$componentClassName];

		} else if (property_exists($componentClassName, "scope")
				&& $componentClassName::$scope == "view") {

			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}

			if (!isset($_SESSION['__VIEW__SCOPE__'.$_REQUEST["__current_view"]])) {
				$_SESSION['__VIEW__SCOPE__'.$_REQUEST["__current_view"]] = array();
			}

			if (!isset($_SESSION['__VIEW__SCOPE__'.$_REQUEST["__current_view"]][$componentClassName])) {
				$_SESSION['__VIEW__SCOPE__'.$_REQUEST["__current_view"]][$componentClassName] = new $componentClassName();
			}

			return  $_SESSION['__VIEW__SCOPE__'.$_REQUEST["__current_view"]][$componentClassName];

		} else {
			// request-scoped components should be singletons during the request
			if (!isset($_REQUEST["__COMPONENT__".$componentClassName])) {
				$_REQUEST["__COMPONENT__".$componentClassName] = new $componentClassName();
			}
			return $_REQUEST["__COMPONENT__".$componentClassName];
		}
	}

	/**
	 * Clears the view scope for a provided view
	 *
	 * @param string $view the view name to clear all components in this view scope
	 */
	public static function clearViewScope($view) {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		$_SESSION['__VIEW__SCOPE__'.$view] = array();
	}

	/**
	* Obtain the class name for a component name
	*
	* For example $controllerName = "users" will return "UsersController"
	*
	* @param $componentName The name of the controller found in the URL
	* @return string The component class name
	*/
	private static function getComponentClassName($componentName) {
		return strToUpper(substr($componentName, 0, 1)).substr($componentName, 1)."Component";
	}
}

// discover and load all components dynamically, since we need that all classes
// being readed before session start, since session-scoped components are in
// session, and the session is deserialized completely at once, we can not
// expect to load the component php file just when it is requested, because other
// components in session will fail to be deserialized.
$files = scandir(__DIR__."/../components");
foreach($files as $file) {
	if (preg_match('/.*Component\.php/', $file)) {
		require_once(__DIR__."/../components/".$file);
	}
}
