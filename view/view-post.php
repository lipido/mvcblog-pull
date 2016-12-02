<?php
//file: view/posts/view.php
ViewManager::getInstance()->setFragmentContent("title", i18n('Post'));

$postsC = ComponentFactory::getComponent("posts");
$commentsC = ComponentFactory::getComponent("comments");
$usersC = ComponentFactory::getComponent("users");

?><h1><?= i18n("Post").": ".htmlentities($postsC->getCurrentPost()->getTitle()) ?></h1>

<em><?= sprintf(i18n("by %s"),$postsC->getCurrentPost()->getAuthor()->getUsername()) ?></em>
<p>
	<?= htmlentities($postsC->getCurrentPost()->getContent()) ?>
</p>

<h2><?= i18n("Comments") ?></h2>

<?php foreach($postsC->getCurrentPost()->getComments() as $comment): ?>
	<hr>
	<p><?= sprintf(i18n("%s commented..."),$comment->getAuthor()->getUsername()) ?> </p>
	<p><?= $comment->getContent(); ?></p>
<?php endforeach; ?>

<?php if ($usersC->getCurrentUser() !== null ): ?>
	<h3><?= i18n("Write a comment") ?></h3>

	<form method="POST" action="index.php?view=view-post&amp;postid=<?=$_GET["postid"]?>&amp;component=comments&amp;event=add">
		<?= i18n("Comment")?>:<br>
		<?= isset($commentsC->getErrors()["content"])?$commentsC->getErrors()["content"]:"" ?><br>
		<textarea type="text" name="content"><?=
		$commentsC->getCurrentComment()->getContent()
		?></textarea>
		<input type="hidden" name="id" value="<?= $postsC->getCurrentPost()->getId() ?>" ><br>
		<input type="submit" name="submit" value="<?=i18n("do comment") ?>">
	</form>

<?php endif ?>
