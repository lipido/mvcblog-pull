<?php
//file: components/LanguageController.php

require_once(__DIR__."/../core/I18n.php");

/**
* Class LanguageComponent
*
* Component to manage the session language.
* Allows you to change the current language
* by establishing it in the I18n singleton instance
*
* @author lipido <lipido@gmail.com>
*/
class LanguageComponent {
	const LANGUAGE_SETTING = "__language__";

	/**
	* Event to change the current language
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>lang: lange to change to (via HTTP GET)</li>
	* </ul>
	* @return string always return ":redirect" to the same view
	*/
	public function change() {
		if(!isset($_GET["lang"])) {
			throw new Exception("no lang parameter was provided");
		}

		I18n::getInstance()->setLanguage($_GET["lang"]);

		//go back to previous page
		return ":redirect";
	}
}
