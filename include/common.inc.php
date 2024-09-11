<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

use Emeraldion\EmeRails\Helpers\Localization;

global $irregular_nouns;
global $default_irregular_nouns;

$default_irregular_nouns = [
    'person' => 'people',
    'child' => 'children',
    'man' => 'men',
    'woman' => 'women',
    'portfolio' => 'portfolios'
];

$irregular_nouns = $default_irregular_nouns;

function default_to($value, $default)
{
    return empty($value) ? $default : $value;
}

function http_error($code)
{
    header(sprintf('Location: http://%s/error/%s.html', $_SERVER['HTTP_HOST'], $code));
    exit();
}

function pluralize($term)
{
    global $irregular_nouns;

    foreach ($irregular_nouns as $singular => $plural) {
        if (ends_with($term, $singular)) {
            return substr($term, 0, strlen($term) - strlen($singular)) . $plural;
        }
    }
    if (array_key_exists($term, $irregular_nouns)) {
        return $irregular_nouns[$term];
    }
    if (ends_with($term, 'child')) {
        // WARNING: NOT EXACTLY WHAT WANTED!!
        return str_replace('child', 'children', $term);
    }
    if (
        ends_with($term, 's') ||
        ends_with($term, 'x') ||
        ends_with($term, 'z') ||
        ends_with($term, 'sh') ||
        (ends_with($term, 'o') && !ends_with($term, 'oo') && !ends_with($term, 'eo'))
    ) {
        return $term . 'es';
    }
    if (ends_with($term, 'y')) {
        return substr($term, 0, strlen($term) - 1) . 'ies';
    }
    return $term . 's';
}

function singularize($term)
{
    global $irregular_nouns;

    foreach ($irregular_nouns as $singular => $plural) {
        if (ends_with($term, $plural)) {
            return substr($term, 0, strlen($term) - strlen($plural)) . $singular;
        }
    }
    if (in_array($term, $irregular_nouns)) {
        return array_search($term, $irregular_nouns);
    }
    if (
        ends_with($term, 'ses') ||
        ends_with($term, 'xes') ||
        ends_with($term, 'zes') ||
        ends_with($term, 'oes') ||
        ends_with($term, 'shes')
    ) {
        return substr($term, 0, strlen($term) - 2);
    }
    if (ends_with($term, 'ies')) {
        return substr($term, 0, strlen($term) - 3) . 'y';
    }
    if (ends_with($term, 's')) {
        return substr($term, 0, strlen($term) - 1);
    }
    return $term;
}

function add_irregular_nouns($values)
{
    global $irregular_nouns;

    foreach ($values as $singular => $plural) {
        $irregular_nouns[$singular] = $plural;
    }
}

function reset_irregular_nouns()
{
    global $irregular_nouns;
    global $default_irregular_nouns;

    $irregular_nouns = $default_irregular_nouns;
}

function ends_with($term, $suffix)
{
    return strrpos($term, $suffix) === strlen($term) - strlen($suffix);
}

function class_name_to_table_name($classname)
{
    return pluralize(camel_case_to_joined_lower($classname));
}

function table_name_to_class_name($tablename)
{
    return joined_lower_to_camel_case(singularize($tablename));
}

function joined_lower($text)
{
    return preg_replace('/[^a-z0-9]+/i', '_', strtolower($text));
}

function joined_lower_to_camel_case($text)
{
    return preg_replace_callback(
        '/(^[a-z])|_([a-z])/',
        function ($match) {
            return strtoupper(@$match[1] . @$match[2]);
        },
        $text
    );
}

function camel_case_to_joined_lower($text)
{
    $text = preg_replace('/([A-Z])/', '_$1', $text);
    $text = preg_replace('/^_/', '', $text);
    return strtolower($text);
}

function class_name_to_foreign_key($classname)
{
    $fkey = camel_case_to_joined_lower($classname);
    $fkey .= '_id';
    return $fkey;
}

function table_name_to_foreign_key($tablename)
{
    $fkey = singularize($tablename);
    $fkey .= '_id';
    return $fkey;
}

/**
 * @fn l($str)
 * @short Shorthand method for the Localization::localize method.
 * @param str The string key to localize
 */
function l($str)
{
    return localized($str);
}

/**
 * @fn localized($str)
 * @short Shorthand for <tt>Localization::localize</tt>
 * @param str The string key to localize
 */
function localized($str)
{
    return Localization::localize($str);
}

/**
 * @fn h($str)
 * @short Shorthand for <tt>htmlentities</tt>
 * @param str The string to escape
 */
function h($str)
{
    return htmlentities($str);
}

/**
 * @fn s($str)
 * @short Shorthand for <tt>addslashes</tt>
 * @param str The string to escape
 */
function s($str)
{
    return addslashes($str);
}

function limit_3($val, $a, $b)
{
    $min = min($a, $b);
    $max = max($a, $b);
    if (is_numeric($val)) {
        if ($min <= $val) {
            if ($val <= $max) {
                return $val;
            }
            return $max;
        }
        return $min;
    }
    return $min;
}

/**
 * @short Returns the first element of $array
 */
function first(array $array)
{
    return count($array) > 0 ? $array[array_key_first($array)] : null;
}

/**
 * @fn get_safe_path($unsafe_path, $base_path, $replacement)
 * @short Hides sensitive information from a path
 */
function get_safe_path($unsafe_path, $base_path, $replacement)
{
    return preg_replace('/' . addcslashes(realpath($base_path), '/') . '/', h($replacement), $unsafe_path);
}

// Prevents a conflict with illuminate/collections/helpers:last()
if (!function_exists('last')) {
    /**
     * @short Returns the last element of $array
     */
    function last(array $array)
    {
        return count($array) > 0 ? $array[array_key_last($array)] : null;
    }
}

/**
 * @short Returns true if at least one element of $array satisfies the predicate $fn
 */
function array_some(array $array, callable $fn)
{
    foreach ($array as $value) {
        if ($fn($value)) {
            return true;
        }
    }
    return false;
}

/**
 * @short Returns true if every element of $array satisfies the predicate $fn
 */
function array_every(array $array, callable $fn)
{
    foreach ($array as $value) {
        if (!$fn($value)) {
            return false;
        }
    }
    return true;
}

/**
 * @short Returns the first element of $array that satisfies the predicate $fn
 */
function array_find(array $array, callable $fn)
{
    foreach ($array as $value) {
        if ($fn($value)) {
            return $value;
        }
    }
    return false;
}

/**
 * Ponyfill
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        $length = strlen($needle);
        return $length > 0 ? substr($haystack, -$length) === $needle : true;
    }
}

/**
 * @short Like <tt>var_dump</tt>, but wrapped in a <tt>&lt;pre&gt;</tt> for readability
 */
if (!function_exists('pre_dump')) {
    function pre_dump($whatever)
    {
        ?>
<pre><?php var_dump($whatever); ?></pre>
<?php
    }
}
