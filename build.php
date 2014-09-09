<?php

$startTime = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';

use Calcine\Config\Parser;
use Calcine\Path;
use Calcine\SiteBuilder;
use Calcine\Template\TemplateRenderer;
use Calcine\User;
use Calcine\Version;

echo 'Calcine version ', Version::getVersion(), PHP_EOL;

$config = new Parser(Path::join(__DIR__, 'app', 'config', 'calcine.json'));

if (! empty($argv[1]) && substr($argv[1], 0, 8) == '--theme=') {
    $theme = substr($argv[1], 8);
} else {
    $theme = $config->get('site.theme');
}

echo 'Building with theme \'', $theme, '\'', PHP_EOL;

$user = new User($config->get('user.name'), $config->get('user.email'));

$templateRenderer = new TemplateRenderer(
    $user,
    $config->get('templates.path'),
    $config->get('web.path')
);
$templateRenderer->setTheme($theme)
    ->setGlobal('title', $config->get('site.title'))
    ->setGlobal('description', $config->get('site.description'))
;

$site = new SiteBuilder(
    $templateRenderer,
    $config->get('posts.path')
);

$stats = $site->build();

echo sprintf(
    'Site built in %.3fs',
    microtime(true) - $startTime
), PHP_EOL;

echo sprintf(
    'Built %d %s.',
    $stats['posts'],
    $stats['posts'] == 1 ? 'post' : 'posts'
), PHP_EOL;

echo sprintf(
    'Built %d %s.',
    $stats['indexes'],
    $stats['indexes'] == 1 ? 'index' : 'indexes'
), PHP_EOL;
