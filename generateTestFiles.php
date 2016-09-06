<?php

define('DATETIME_FORMAT_MYSQL', 'Y-m-d H:i:s');

$template = file_get_contents(__DIR__ . '/tests/postTemplate.markdown');

$baseDate = DateTime::createFromFormat(DATETIME_FORMAT_MYSQL, '2014-09-03 09:00:00');

for ($i = -10; $i < 1000; ++$i) {
    $date = clone $baseDate;
    $date->modify(sprintf('+%d days', abs($i)));

    $slug = sprintf('%s-%04d', $i <= 0 ? 'url-prefix-0' : 'url-prefix-1', abs($i));

    $tokens = array(
        '{{ title }}' => sprintf('Blog Post %d', abs($i)),
        '{{ tags }}'  => 'Tag, Test, PHP',
        '{{ slug }}'  => $slug,
        '{{ date }}'  => $date->format(DATETIME_FORMAT_MYSQL),
        '{{ body }}'  => implode(PHP_EOL, array(
            '# Heading',
            '',
            'This is a test post.',
        )),
    );

    $post = strtr($template, $tokens);

    $postFilename = sprintf(
        '%s-%s.markdown',
        $date->format('Y-m-d'),
        $slug
    );

    file_put_contents(__DIR__ . '/posts/' . $postFilename, $post);
}

echo 'Last date is ', $date->format(DATETIME_FORMAT_MYSQL), PHP_EOL;
