<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2025
 *
 * @format
 */

function select($name, $values, $default_value, $params = [])
{
    $params_serialized = '';
    foreach ($params as $key => $value) {
        $params_serialized .= " $key=\"" . htmlentities($value) . "\"";
    }
    $options = '';
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
    $params_serialized = '';
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
    $params_serialized = '';
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
    $params_serialized = '';
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

function a($content, $params = [])
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

function p($content, $params = [])
{
    return block_tag('p', $content, $params);
}

function span($content, $params = [])
{
    return inline_tag('span', $content, $params);
}

function div($content, $params = [])
{
    return block_tag('div', $content, $params);
}

function em($content, $params = [])
{
    return inline_tag('em', $content, $params);
}

function h1($content, $params = [])
{
    return block_tag('h1', $content, $params);
}

function h2($content, $params = [])
{
    return block_tag('h2', $content, $params);
}

function h3($content, $params = [])
{
    return block_tag('h3', $content, $params);
}

function h4($content, $params = [])
{
    return block_tag('h4', $content, $params);
}

function h5($content, $params = [])
{
    return block_tag('h5', $content, $params);
}

function h6($content, $params = [])
{
    return block_tag('h6', $content, $params);
}

/**
 *	Form elements
 */

function button($name, $value, $params = [], $enabled = true)
{
    $params['name'] = $name;
    $params['value'] = $value;
    $params['type'] = 'button';
    if (!$enabled) {
        $params['disabled'] = 'disabled';
    }
    return leaf_tag('input', $params);
}

function checkbox($name, $checked, $params = [], $enabled = true)
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

/**
 * @fn strip_external_php_tags($php_code)
 * @short Strips beginning and ending delimiters from the given PHP code.
 * @details This function removes the beginning and ending PHP code delimiters
 * to enable safe parsing with <tt>eval</tt>.
 * The algorithm is as follows:
 *   - If a <tt>&lt;?php</tt> opening tag appears at the beginning of <tt>$php_code</tt>, it is stripped,
 *     otherwise a closing tag <tt>?&gt;</tt> is added to the beginning.
 *   - If a <tt>?&gt;</tt> closing tag appears at the end of <tt>$php_code</tt>, it is stripped, otherwise
 * @param php_code The code snippet to be treated
 */
function strip_external_php_tags($php_code)
{
    $first_opening_tag = strpos($php_code, '<?php');
    $last_opening_tag = strrpos($php_code, '<?php');
    $first_closing_tag = strpos($php_code, '?>');
    $last_closing_tag = strrpos($php_code, '?>');

    if ($first_opening_tag === 0) {
        // Trivial case, opening PHP tag at the beginning of content
        $php_code = substr($php_code, strlen('<?php'));
    } elseif (
        // No opening or closing PHP tags
        ($first_opening_tag === false && $first_closing_tag === false) ||
        // First opening PHP tag appearing before the first closing PHP tag
        ($first_closing_tag === false || $first_opening_tag < $first_closing_tag)
    ) {
        $php_code = "?>\n" . $php_code;
    }
    if (strrpos($php_code, "?>\n") === strlen($php_code) - strlen("?>\n")) {
        // Trivial case, closing PHP tag at the end of content
        $php_code = substr($php_code, 0, strrpos($php_code, "?>\n"));
    } elseif (
        // Last closing PHP tag appearing after the last opening PHP tag
        $last_opening_tag === false ||
        $last_closing_tag > $last_opening_tag
    ) {
        $php_code .= "\n<?php\n";
    }
    return $php_code;
}

/**
 * @fn ensure_external_php_tags($php_code)
 * @short Strips beginning and ending delimiters from the given PHP code.
 * @details This function removes the beginning and ending PHP code delimiters
 * to enable safe parsing with <tt>eval</tt>.
 * The algorithm is as follows:
 *   - If an unbalanced <tt>?&gt;</tt> closing tag appears at the beginning of <tt>$php_code</tt>, an opening tag
 *     is added to the beginning.
 *   - If an unbalanced <tt>&lt;?&php</tt> opening tag appears at the end of <tt>$php_code</tt>, a closing tag is
 *     appended to the end.
 * @param php_code The code snippet to be treated
 */
function ensure_external_php_tags($php_code)
{
    $first_opening_tag = strpos($php_code, '<?php');
    $last_opening_tag = strrpos($php_code, '<?php');
    $first_closing_tag = strpos($php_code, '?>');
    $last_closing_tag = strrpos($php_code, '?>');

    if ($first_opening_tag > $first_closing_tag) {
        // There's an unbalanced closing tag at the beginning
        $php_code = "<?php\n" . $php_code;
    }
    if ($last_closing_tag < $last_opening_tag) {
        // There's an unbalanced opening tag at the end
        $php_code .= "\n?>\n";
    }
    return $php_code;
}
