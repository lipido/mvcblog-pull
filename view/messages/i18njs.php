<?php
ViewManager::setLayout("empty");
header('Content-type: application/javascript');
echo "var i18nMessages = [];\n";
echo "function ji18n(key) { if (key in i18nMessages) return i18nMessages[key]; else return key;}\n";
foreach (I18n::getInstance()->getAllMessages() as $key=>$value) {
	echo "i18nMessages['$key'] = '$value';\n";
}
