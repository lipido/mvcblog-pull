<?php
//file: view/users/login.php

ViewManager::getInstance()->setFragmentContent("title", i18n('Login'));
ViewManager::getInstance()->setLayout("welcome");

// get components to use in this view
$usersC = ComponentFactory::getComponent("users");
?>
<h1><?= i18n("Login") ?></h1>
<?= isset($usersC->getLoginErrors()["general"])?$usersC->getLoginErrors()["general"]:"" ?>

<form action="index.php?view=login&amp;component=users&amp;event=login" method="POST">
	<?= i18n("Username")?>: <input type="text" name="username">
	<?= i18n("Password")?>: <input type="password" name="passwd">
	<input type="submit" value="<?= i18n("Login") ?>">
</form>

<p><?= i18n("Not user?")?> <a href="index.php?view=register"><?= i18n("Register here!")?></a></p>
<?php ViewManager::getInstance()->moveToFragment("css");?>
<link rel="stylesheet" type="text/css" src="css/style2.css">
<?php ViewManager::getInstance()->moveToDefaultFragment(); ?>
