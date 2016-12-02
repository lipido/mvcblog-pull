<?php
//file: view/register.php

ViewManager::getInstance()->setFragmentContent("title", i18n('Login'));
ViewManager::getInstance()->setLayout("welcome");

// get components to use in this view
$usersC = ComponentFactory::getComponent("users");
?>

<h1><?= i18n("Register")?></h1>
<form action="index.php?view=register&amp;component=users&amp;event=register" method="POST">
	<?= i18n("Username")?>: <input type="text" name="username"
	value="<?= $usersC->getRegisteringUser()->getUsername() ?>">
	<?= isset($usersC->getRegisteringErrors()["username"])?$usersC->getRegisteringErrors()["username"]:"" ?><br>

	<?= i18n("Password")?>: <input type="password" name="passwd"
	value="">
	<?= isset($usersC->getRegisteringErrors()["passwd"])?$usersC->getRegisteringErrors()["passwd"]:"" ?><br>

	<input type="submit" value="<?= i18n("Register")?>">
</form>
