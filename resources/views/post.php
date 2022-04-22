<?= render('partials/header', ['title' => 'Ryan Chandler']) ?>

<h2><?= $post->matter['title'] ?></h2>

<?= $post->html ?>

<?= render('partials/footer') ?>