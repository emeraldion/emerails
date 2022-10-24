<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 */

function select($name, $values, $default_value, $params = array())
{
    $params_serialized = "";
    foreach ($params as $key => $value) {
        $params_serialized .= " $key=\"" . htmlentities($value) . "\"";
    }
    $options = "";
    foreach ($values as $value => $title) {
        $value = htmlentities($value);
        $selected = $value == $default_value ? ' selected="selected"' : '';
        $options .= <<<EOT
		<option value="{$value}"{$selected}>{$title}</option>

EOT;
    }
    $html = <<<EOT
	<select name="{$name}"{$params_serialized}>
	{$options}
	</select>
EOT;
    return $html;
}

/**
 *	Abstract tag factory
 */
function block_tag($tagname, $content, $params = null)
{
    $params_serialized = "";
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $params_serialized .= " $key=\"$value\"";
        }
    }
    $html = <<<EOT
<{$tagname}{$params_serialized}>
	{$content}
</{$tagname}>

EOT;
    return $html;
}

function inline_tag($tagname, $content, $params = null)
{
    $params_serialized = "";
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $params_serialized .= " $key=\"" . htmlentities($value) . "\"";
        }
    }
    $html = <<<EOT
<{$tagname}{$params_serialized}>{$content}</{$tagname}>
EOT;
    return $html;
}

function leaf_tag($tagname, $params = null)
{
    $params_serialized = "";
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $params_serialized .= " $key=\"$value\"";
        }
    }
    $html = <<<EOT
<{$tagname}{$params_serialized} />
EOT;
    return $html;
}

/**
 *	Containers
 */

function a($content, $params)
{
    if (isset($params['query_string'])) {
        $params['href'] .= "?{$params['query_string']}";
        unset($params['query_string']);
    }
    if (isset($params['hash'])) {
        $params['href'] .= "#{$params['hash']}";
        unset($params['hash']);
    }
    return inline_tag('a', $content, $params);
}

function p($content, $params)
{
    return block_tag('p', $content, $params);
}

function span($content, $params)
{
    return inline_tag('span', $content, $params);
}

function div($content, $params)
{
    return block_tag('div', $content, $params);
}

function em($content, $params)
{
    return inline_tag('em', $content, $params);
}

function h1($content, $params)
{
    return block_tag('h1', $content, $params);
}

function h2($content, $params)
{
    return block_tag('h2', $content, $params);
}

function h3($content, $params)
{
    return block_tag('h3', $content, $params);
}

function h4($content, $params)
{
    return block_tag('h4', $content, $params);
}

function h5($content, $params)
{
    return block_tag('h5', $content, $params);
}

function h6($content, $params)
{
    return block_tag('h6', $content, $params);
}

/**
 *	Form elements
 */

function button($name, $value, $params = array(), $enabled = true)
{
    $params['name'] = $name;
    $params['value'] = $value;
    $params['type'] = 'button';
    if (!$enabled) {
        $params['disabled'] = 'disabled';
    }
    return leaf_tag('input', $params);
}

function checkbox($name, $checked, $params = array(), $enabled = true)
{
    $params['name'] = $name;
    $params['type'] = 'checkbox';
    if ($checked) {
        $params['checked'] = 'checked';
    }
    if (!$enabled) {
        $params['disabled'] = 'disabled';
    }
    return leaf_tag('input', $params);
}
?>
