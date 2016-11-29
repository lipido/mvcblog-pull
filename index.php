<?php
// file: index.php
require_once(__DIR__."/core/ComponentFactory.php");
require_once(__DIR__."/core/ViewManager.php");
require_once(__DIR__."/core/I18n.php");

/**
* Default view if none is provided in the request parameter
*/
define("DEFAULT_VIEW", "posts");



/**
* Main router (single entry-point for all requests)
* of the pull-based MVC implementation.
*
* This method will dispatch an event, if there are the component
* and event parameters in the request, calling the corresponding component's
* method.
*
* The rest of GET or POST parameters should be handled by
* the component itself.
*
* After the event-dispatch (if any), a view is rendered. The view to render
* could be (i) the view returned by the event method (if it returns a value)
* or (ii) the view given by parameter in the current request.
*
* Parameters:
* <ul>
* <li>view: Mandatory, the view to render if there is no event or the event
* method does not return a new view. (via HTTP GET)</li>
* <li>component: The component dispatching the event. (via HTTP GET)</li>
* <li>event: The method inside the component dispatching the event.
* (via HTTP GET)</li>
* </ul>
*
* @return void
*
* @author lipido <lipido@gmail.com>
*/
function run() {

	try {

		if (isset($_REQUEST["view"])) {
			// We will put this value in the $_REQUEST as a global handle to know
			// who is the current view during the request lifecycle, since it changes
			// during the event dispatch (if the event returns a view).
			// This, for example, will affect the ComponentFactory to retrieve the
			// view-scoped components.
			$_REQUEST["__current_view"] = $_REQUEST["view"];
		}
		// event-dispatch, if any
		if (isset($_REQUEST["component"]) && isset($_REQUEST["event"])) {
			// we have an event!
			$component = ComponentFactory::getComponent($_GET["component"]);
			$eventName = $_GET["event"];

			// invoke the event
			$eventReturnView = $component->$eventName();

			// some events can return the view to navigate to
			if (isset($eventReturnView)) {
				$view = preg_replace('/:redirect$/','', $eventReturnView);
				if ($view != $_REQUEST["view"] && $view !== ":redirect") {
					// goint to another view, clear view scope
					if (isset($_REQUEST["__current_view"])) {
						ComponentFactory::clearViewScope($_REQUEST["__current_view"]);
					}
					ComponentFactory::clearViewScope($view);
				}
			}
		}

		// no view from event, get the current view from the request parameter
		if (
				(!isset($eventReturnView)	|| (isset($eventReturnView) && $eventReturnView == ":redirect"))
				&& isset($_REQUEST["view"])
		) {
			$view = $_REQUEST["view"];
		} else if (!isset($view)) {
			$view = DEFAULT_VIEW;
		}

		$_REQUEST["__current_view"] = $view;

		if(!isset($_REQUEST["event"])) {
			// skip view-scope clearing if we are reaching via redirect to the same page
			if (!isset($_REQUEST["_noclear"])) {
				// no event, and no _noclear, seems to be a fresh arrival, clear view-scope
				ComponentFactory::clearViewScope($_REQUEST["__current_view"]);
			}
		}

		// if the view ends with ":redirect", we perform a HTTP redirect instead of
		// a simple include of the view in the same request
		if (isset($eventReturnView) && preg_match('/.+:redirect$/', $eventReturnView)) {
			ViewManager::getInstance()->redirect(substr($eventReturnView, 0, -9));
		} else if (isset($eventReturnView) && $eventReturnView == ":redirect") {
			// if we redirect to the same page, do not clear the view-scoped components
			ViewManager::getInstance()->redirectToReferer("_noclear=");
		} else {
			ViewManager::getInstance()->render($view);
		}

	} catch(Exception $ex) {
		//uniform treatment of exceptions
		die("An exception occured!!!!!".$ex->getMessage());
	}
}

//run!
run();
