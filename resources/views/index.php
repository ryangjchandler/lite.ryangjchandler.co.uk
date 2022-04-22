<?= render('partials/header', ['title' => 'Ryan Chandler']) ?>

<div>
    <?php foreach ($posts as $post): ?>
        <a href="<?= 'posts/' . $post->matter['slug'] . '.html' ?>" style="color: blue; display: flex; align-items: center; margin-top: 25px">
            <h4 style="margin: 0; margin-right: 10px;">
                <?= $post->matter['title'] ?>
            </h4>

            <hr style="flex: 1 1 0%; text-decoration: none; margin: 0;">

            <span style="margin: 0; margin-left: 10px; font-size: 14px;">
                <?= $post->publishedAt()->format('d/m/Y') ?>
            </span>
        </a>
    <?php endforeach ?>
</div>

<?= render('partials/footer') ?>