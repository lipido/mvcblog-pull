<?php
//file: view/blog-home.php

ViewManager::getInstance()->setFragmentContent("title", i18n('Posts'));

// get components to use in this view
$postsC = ComponentFactory::getComponent("posts");
$usersC = ComponentFactory::getComponent("users");
?>
<h1><?=i18n("Posts")?></h1>

<!-- Add new Post form -->
<?php if ($usersC->getCurrentUser()!== null && !$postsC->isEditing()): ?>
	<h2>Add new post</h2>
	<form action="index.php?view=posts&amp;component=posts&amp;event=add" method="POST">
		<?= i18n("Title") ?>: <input type="text" name="title"
		value="<?= $postsC->getCurrentPost()->getTitle() ?>">
		<?= isset($postsC->getErrors()["title"])?$postsC->getErrors()["title"]:"" ?><br>

		<?= i18n("Contents") ?>: <br>
		<textarea name="content" rows="4" cols="50"><?=
		$postsC->getCurrentPost()->getContent() ?></textarea>
		<?= isset($postsC->getErrors()["content"])?$postsC->getErrors()["content"]:"" ?><br>

		<input type="submit" name="submit" value="submit">
	</form>
<?php endif; ?>

<!-- Edit Post form -->
<?php if ($usersC->getCurrentUser()!== null && $postsC->isEditing()): ?>
	<form action="index.php?view=posts&amp;component=posts&amp;event=saveEdit" method="POST">
		<h2>Edit post</h2>
		<?= i18n("Title") ?>: <input type="text" name="title"
		value="<?= $postsC->getCurrentPost()->getTitle() ?>">
		<?= isset($postsC->getErrors()["title"])?$postsC->getErrors()["title"]:"" ?><br>

		<?= i18n("Contents") ?>: <br>
		<textarea name="content" rows="4" cols="50"><?=
		$postsC->getCurrentPost()->getContent() ?></textarea>
		<?= isset($postsC->getErrors()["content"])?$postsC->getErrors()["content"]:"" ?><br>

		<input type="submit" name="submit" value="submit">
	</form>
<?php endif; ?>
<table border="1">
	<tr>
		<th><?= i18n("Title")?></th><th><?= i18n("Author")?></th><th><?= i18n("Actions")?></th>
	</tr>

	<?php foreach ($postsC->getAllPosts() as $post): ?>
		<tr>
			<td>
				<a href="index.php?view=view-post&amp;postid=<?= $post->getId() ?>"><?= htmlentities($post->getTitle()) ?></a>
			</td>
			<td>
				<?= $post->getAuthor()->getUsername() ?>
			</td>
			<td>
				<?php
				//show actions ONLY for the author of the post (if logged)


				if ($usersC->getCurrentUser() !== null && $usersC->getCurrentUser()->getUsername() == $post->getAuthor()->getUsername()): ?>

				<?php
				// 'Delete Button': show it as a link, but do POST in order to preserve
				// the good semantic of HTTP
				?>
				<form
				method="POST"
				action="index.php?view=posts&amp;component=posts&amp;event=delete"
				id="delete_post_<?= $post->getId(); ?>"
				style="display: inline"
				>

				<input type="hidden" name="id" value="<?= $post->getId() ?>">

				<a href="#"
				onclick="
				if (confirm('<?= i18n("are you sure?")?>')) {
					document.getElementById('delete_post_<?= $post->getId() ?>').submit()
				}"
				><?= i18n("Delete") ?></a>
			</form>

			&nbsp;

			<?php
			// 'Edit Button'
			?>
			<a href="index.php?view=posts&amp;component=posts&amp;event=edit&amp;id=<?= $post->getId() ?>"><?= i18n("Edit") ?></a>

		<?php endif; ?>

	</td>
</tr>
<?php endforeach; ?>

</table>
