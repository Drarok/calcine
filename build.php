<?php

require_once __DIR__ . '/vendor/autoload.php';

use Calcine\Config\Parser;
use Calcine\Path;
use Calcine\SiteBuilder;
use Calcine\User;
use Calcine\Version;

echo 'Calcine version ', Version::getVersion(), PHP_EOL;

$config = new Parser(Path::join(__DIR__, 'app', 'config', 'calcine.json'));

$user = new User($config->get('user.name'), $config->get('user.email'));

$site = new SiteBuilder(
    $user,
    $config->get('paths.posts'),
    $config->get('paths.web')
);
$site->build();
