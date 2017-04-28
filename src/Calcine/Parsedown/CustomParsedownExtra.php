<?php

namespace Calcine\Parsedown;

use ParsedownExtra;

/**
 * This class extends ParsedownExtra in order to add some custom classes from our CSS,
 * and to support attributes within curly braces.
 */
class CustomParsedownExtra extends ParsedownExtra
{
    protected $regexAttribute = '(?:[#.]?[-\w=]+\s*)';

    protected function inlineCode($Excerpt)
    {
        $result = parent::inlineCode($Excerpt);
        if (!$result) {
            return $result;
        }

        $result['element']['attributes'] = [
            'class' => 'mono',
        ];

        return $result;
    }

    /**
     * This is overridden to add support for attributes on fenced code blocks {#id .class lang=fr}
     *
     * @param array $Line
     *
     * @return array
     */
    protected function blockFencedCode($Line)
    {
        $pattern = '/^['.$Line['text'][0].']{3,}\s*([\w-]+)?\s*(?:{(' . $this->regexAttribute . '+)})*$/';

        if (preg_match($pattern, $Line['text'], $matches)) {
            $Element = array(
                'name' => 'code',
                'text' => '',
            );

            if (!empty($matches[1])) {
                $class = 'language-'.$matches[1];

                $Element['attributes'] = array(
                    'class' => $class,
                );
            }

            $Element['attributes'] = $this->mergeAttributes($Element, $Line['text']);

            return [
                'char' => $Line['text'][0],
                'element' => [
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => $Element,
                ],
            ];
        }
    }

    protected function parseAttributeData($attributeString)
    {
        $Data = array();

        $attributes = preg_split('/\s+/', $attributeString, - 1, PREG_SPLIT_NO_EMPTY);

        foreach ($attributes as $attribute) {
            if ($attribute[0] === '#') {
                $Data['id'] = substr($attribute, 1);
            } elseif (strpos($attribute, '=') !== false) {
                list($key, $value) = explode('=', $attribute, 2);
                $Data[$key] = $value;
            } else {
                $classes[] = substr($attribute, 1);
            }
        }

        if (isset($classes))
        {
            $Data['class'] = implode(' ', $classes);
        }

        return $Data;
    }

    private function mergeAttributes(array $element, $text)
    {
        $pattern = '/(?:{(' . $this->regexAttribute . '+)})$/';

        $attr = !empty($element['attributes']) ? $element['attributes'] : [];

        if (preg_match($pattern, $text, $matches)) {
            $data = $this->parseAttributeData($matches[1]);
            foreach ($data as $key => $value) {
                if (!empty($attr[$key])) {
                    $attr[$key] .= ' ' . $value;
                } else {
                    $attr[$key] = $value;
                }
            }
        }

        return $attr;
    }
}
