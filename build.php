<?php

$startTime = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';

use Calcine\Config\Parser;
use Calcine\Path;
use Calcine\Path\PathService;
use Calcine\SiteBuilder;
use Calcine\Template\TemplateRenderer;
use Calcine\User;
use Calcine\Version;

echo 'Calcine version ', Version::getVersion(), PHP_EOL;

$config = new Parser(Path::join(__DIR__, 'app', 'config', 'calcine.json'));

$user = new User($config->get('user.name'), $config->get('user.email'));

$templateRenderer = new TemplateRenderer(
    $user,
    $config->get('paths.templates'),
    $config->get('paths.posts'),
    $config->get('paths.web')
);
$templateRenderer->setTheme($config->get('site.theme'));

$pathService = new PathService(
    $config->get('paths.posts'),
    $config->get('paths.templates'),
    $config->get('paths.web')
);

$site = new SiteBuilder(
    $user,
    $templateRenderer,
    $pathService
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
