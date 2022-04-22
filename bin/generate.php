<?php

use Carbon\Carbon;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkShikiHighlighter\HighlightCodeExtension;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Finder\Finder;

require_once __DIR__ . '/../vendor/autoload.php';

function base_path(string $path = ''): string
{
    return realpath(__DIR__ . '/../') . ($path ? '/' . $path : $path);
}

function println(string $message): void
{
    echo $message . PHP_EOL;
}

final class Document
{
    public function __construct(
        public array $matter,
        public string $html,
    ) {}

    public function publishedAt(): ?Carbon
    {
        if (! isset($this->matter['published_at'])) {
            return null;
        }

        return Carbon::parse($this->matter['published_at']);
    }
}

$files = Finder::create()
    ->in(base_path('ryangjchandler.co.uk/content/posts'))
    ->files();

$documents = [];
$parser = new MarkdownConverter(
    (new Environment)
        ->addExtension(new CommonMarkCoreExtension)
        ->addExtension(new GithubFlavoredMarkdownExtension)
        ->addExtension(new HighlightCodeExtension('github-light'))
);

foreach ($files as $file) {
    println("Parsing {$file->getPathname()}.");

    $contents = $file->getContents();

    if (file_exists($cache = base_path('cache/' . md5($contents)))) {
        println('[INFO] Reading from cache.');

        $documents[] = unserialize(file_get_contents($cache));

        continue;
    }

    $parsed = YamlFrontMatter::parse($contents);

    $documents[] = $document = new Document(
        $parsed->matter(),
        $parser->convert($parsed->body())
    );

    file_put_contents($cache, serialize($document));
}

function render(string $view, array $data = []) {
    extract($data);
    ob_start();

    require base_path("resources/views/{$view}.php");

    return ob_get_clean();
}

$published = collect($documents)
    ->filter(fn (Document $document) => $document->publishedAt()?->isPast())
    ->sortByDesc(fn (Document $document) => $document->publishedAt()->getTimestamp());

$index = render('index', [
    'posts' => $published,
]);

file_put_contents(base_path('build/index.html'), $index);

if (! is_dir(base_path('build/posts'))) {
    mkdir(base_path('build/posts'));
}

foreach ($published as $post) {
    $rendered = render('post', [
        'post' => $post,
    ]);

    file_put_contents(base_path("build/posts/{$post->matter['slug']}.html"), $rendered);
}

if (! is_dir(base_path('build'))) {
    mkdir(base_path('build'));
}