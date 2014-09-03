<?php

$template = file_get_contents(__DIR__ . '/tests/postTemplate.markdown');

$baseDate = DateTime::createFromFormat('Y-m-d H:i:s', '2014-09-03 08:00:00');

for ($i = 0; $i < 100; ++$i) {
    $date = clone $baseDate;
    $date->modify(sprintf('+%d days', $i));

    $tokens = array(
        '{{ title }}' => sprintf('Blog Post %d', $i),
        '{{ tags }}'  => 'Tag, Test, PHP',
        '{{ slug }}'  => $slug = sprintf('url-slug-%d', $i),
        '{{ date }}'  => $date->format('Y-m-d H:i:s'),
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
