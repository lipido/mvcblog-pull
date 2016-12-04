<?php
//file: view/layouts/default.php

$view = ViewManager::getInstance();

$usersC = ComponentFactory::getComponent("users");

?><!DOCTYPE html>
<html>
<head>
	<title><?= $view->getFragment("title") ?></title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="css/style.css" type="text/css">
	<!-- enable ji18n() javascript function to translate inside your scripts -->
	<script src="index.php?view=messages/i18njs">
	</script>
	<?= $view->getFragment("css") ?>
	<?= $view->getFragment("javascript") ?>
</head>
<body>
	<!-- header -->
	<header>
		<h1>Blog</h1>
		<nav id="menu">
			<ul>
				<li><a href="index.php?view=posts">Posts</a></li>

				<?php if ($usersC->getCurrentUser() !== null): ?>
					<li><?= sprintf(i18n("Hello %s"), $usersC->getCurrentUser()->getUsername()) ?>
						<a 	href="index.php?view=<?=$_GET['view']?>&amp;component=users&amp;event=logout">(Logout)</a>
					</li>

				<?php else: ?>
					<li><a href="index.php?view=login"><?= i18n("Login") ?></a></li>
				<?php endif ?>
			</ul>
		</nav>
	</header>

	<main>
		<?= $view->getFragment(ViewManager::DEFAULT_FRAGMENT) ?>
	</main>

	<footer>
		<?php
		include(__DIR__."/language_select_element.php");
		?>
		<!-- example of the pull-based framework. We are retrieving values using
				 more than one component -->
		<?= i18n('Registered users') ?>: <?= $usersC->getUserCount() ?>
	</footer>

</body>
</html>
