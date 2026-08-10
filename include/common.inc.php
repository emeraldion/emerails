<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
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

if (!function_exists('default_to')) {
    function default_to($value, $default)
    {
        return empty($value) ? $default : $value;
    }
}

if (!function_exists('http_error')) {
    function http_error($code)
    {
        header(sprintf('Location: http://%s/error/%s.html', $_SERVER['HTTP_HOST'], $code));
        exit();
    }
}

if (!function_exists('pluralize')) {
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
}

if (!function_exists('singularize')) {
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
}

if (!function_exists('add_irregular_nouns')) {
    function add_irregular_nouns($values)
    {
        global $irregular_nouns;

        foreach ($values as $singular => $plural) {
            $irregular_nouns[$singular] = $plural;
        }
    }
}

if (!function_exists('reset_irregular_nouns')) {
    function reset_irregular_nouns()
    {
        global $irregular_nouns;
        global $default_irregular_nouns;

        $irregular_nouns = $default_irregular_nouns;
    }
}

if (!function_exists('ends_with')) {
    function ends_with($term, $suffix)
    {
        return strrpos($term, $suffix) === strlen($term) - strlen($suffix);
    }
}

if (!function_exists('class_name_to_table_name')) {
    function class_name_to_table_name($classname)
    {
        return pluralize(camel_case_to_joined_lower($classname));
    }
}

if (!function_exists('table_name_to_class_name')) {
    function table_name_to_class_name($tablename)
    {
        return joined_lower_to_camel_case(singularize($tablename));
    }
}

if (!function_exists('joined_lower')) {
    function joined_lower($text)
    {
        return preg_replace('/[^a-z0-9]+/i', '_', strtolower($text));
    }
}

if (!function_exists('joined_lower_to_camel_case')) {
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
}

if (!function_exists('camel_case_to_joined_lower')) {
    function camel_case_to_joined_lower($text)
    {
        $text = preg_replace('/([A-Z])/', '_$1', $text);
        $text = preg_replace('/^_/', '', $text);
        return strtolower($text);
    }
}

if (!function_exists('class_name_to_foreign_key')) {
    function class_name_to_foreign_key($classname)
    {
        $fkey = camel_case_to_joined_lower($classname);
        $fkey .= '_id';
        return $fkey;
    }
}

if (!function_exists('table_name_to_foreign_key')) {
    function table_name_to_foreign_key($tablename)
    {
        $fkey = singularize($tablename);
        $fkey .= '_id';
        return $fkey;
    }
}

if (!function_exists('normalize_entity_name')) {
    function normalize_entity_name(string $str): string
    {
        // Lowercase
        $ret = strtolower($str);
        // Hyphens (-) must be transformed into underscores (_)
        $ret = str_replace('-', '_', $ret);
        // Must start with a letter
        if (preg_match('/^[^a-z]/', $ret)) {
            throw new Exception(sprintf("'%s' must start with a letter", $str));
        }
        if (preg_match('/[^a-z0-9_]/', $ret)) {
            throw new Exception(sprintf("'%s' must only contain letters, numbers and underscores", $str));
        }
        return $ret;
    }
}

/**
 * @fn l($str)
 * @short Shorthand method for the Localization::localize method.
 * @param str The string key to localize
 */
if (!function_exists('l')) {
    function l($str)
    {
        return localized($str);
    }
}

/**
 * @fn localized($str)
 * @short Shorthand for <tt>Localization::localize</tt>
 * @param str The string key to localize
 */
if (!function_exists('localized')) {
    function localized($str)
    {
        return Localization::localize($str);
    }
}

/**
 * @fn h($str)
 * @short Shorthand for <tt>htmlentities</tt>
 * @param str The string to escape
 */
if (!function_exists('h')) {
    function h(?string $str): ?string
    {
        return is_null($str) ? null : htmlentities($str);
    }
}

/**
 * @fn s($str)
 * @short Shorthand for <tt>addslashes</tt>
 * @param str The string to escape
 */
if (!function_exists('s')) {
    function s($str)
    {
        return addslashes($str);
    }
}

if (!function_exists('limit_3')) {
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
}

/**
 * @short Returns the first element of $array
 */
if (!function_exists('first')) {
    function first(array $array)
    {
        return count($array) > 0 ? $array[array_key_first($array)] : null;
    }
}

/**
 * @fn get_safe_path($unsafe_path, $base_path, $replacement)
 * @short Hides sensitive information from a path
 */
if (!function_exists('get_safe_path')) {
    function get_safe_path($unsafe_path, $base_path, $replacement)
    {
        return preg_replace('/' . addcslashes(realpath($base_path), '/') . '/', h($replacement), $unsafe_path);
    }
}

if (!function_exists('vscode_linkify')) {
    function vscode_linkify($path)
    {
        return sprintf('vscode://file/%s', $path);
    }
}

if (!function_exists('sanitize_stacktrace')) {
    function sanitize_stacktrace($stacktrace, $base_path, $replacement)
    {
        return preg_replace_callback(
            '/(' . addcslashes(realpath($base_path), '/') . '[^\'()]*)(\'|\(|\))/',
            function ($matches) use ($base_path, $replacement) {
                [, $path, $terminator] = $matches;
                return a(get_safe_path($path, $base_path, $replacement), ['href' => vscode_linkify($path)]) .
                    $terminator;
            },
            $stacktrace
        );
    }
}

/**
 * @fn symbolicate_stacktrace($t, $levels, $skip_levels)
 * @short Produces a simplified stacktrace for display
 * @details This method produces a simplified stacktrace for debugging purposes, by showing a single line for each frame in the stack.
 * The stack is obtained from a <tt>Throwable</tt> accepted as first argument, or a call to <tt>debug_stacktrace()</tt> if no throwable is passed.
 * The other two arguments control the maximum depth of the stack to return, and the number of initial levels to skip. The latter is useful
 * for custom error handlers where the error handling logic would be included in the stack and clobber the error report.
 * @param t The throwable to inspect, or null to analyze the debug stacktrace
 * @param levels The number of levels
 * @param skip_levels The number of initial levels to skip
 * @return The symbolicated stacktrace
 */
if (!function_exists('symbolicate_stacktrace')) {
    function symbolicate_stacktrace(?Throwable $t = null, int $levels = 10, int $skip_levels = 0): string
    {
        $ret = '';
        $trace = array_slice(
            $t
                ? array_merge([['file' => $t->getFile(), 'line' => $t->getLine()]], $t->getTrace())
                : debug_backtrace(0, $levels + $skip_levels),
            $skip_levels
        );
        $stack_depth = count($trace);
        for ($i = 0; $i < $stack_depth; $i += 1) {
            $frame = $trace[$i];
            $next_frame = $i < $stack_depth - 1 ? $trace[$i + 1] : null;
            $symbol = $next_frame
                ? (array_key_exists('class', $next_frame)
                    ? $next_frame['class'] . '::' . $next_frame['function']
                    : $next_frame['function'])
                : h('<anonymous>');
            $location =
                $next_frame && $next_frame['function'] === 'eval' && array_key_exists('__PART__', $GLOBALS)
                    ? $GLOBALS['__PART__']
                    : (array_key_exists('file', $frame)
                        ? $frame['file']
                        : h('<native code>'));
            if (array_key_exists('line', $frame)) {
                $location .= ':' . $frame['line'];
            }
            $ret .= "\tat {$symbol}({$location})\n";
        }
        return $ret;
    }
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
if (!function_exists('array_some')) {
    function array_some(array $array, callable $fn)
    {
        foreach ($array as $value) {
            if ($fn($value)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * @short Returns true if every element of $array satisfies the predicate $fn
 */
if (!function_exists('array_every')) {
    function array_every(array $array, callable $fn)
    {
        foreach ($array as $value) {
            if (!$fn($value)) {
                return false;
            }
        }
        return true;
    }
}

/**
 * @short Returns the first element of $array that satisfies the predicate $fn
 */
if (!function_exists('array_find')) {
    function array_find(array $array, callable $fn)
    {
        foreach ($array as $value) {
            if ($fn($value)) {
                return $value;
            }
        }
        return false;
    }
}

/**
 * Ponyfills
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $needle_len = strlen($needle);
        return $needle_len === 0 || 0 === substr_compare($haystack, $needle, -$needle_len);
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
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

if (!function_exists('strip_namespace')) {
    function strip_namespace(string $classname): string
    {
        if (strpos($classname, '\\') !== false) {
            return last(explode('\\', $classname));
        }
        return $classname;
    }
}

if (!function_exists('get_unqualified_class')) {
    function get_unqualified_class($obj): string
    {
        return strip_namespace(get_class($obj));
    }
}

if (!function_exists('sanitize_language_cookie')) {
    function sanitize_language_cookie(?string $value, string $default_lang = 'en'): string
    {
        if ($value) {
            $lang = strtolower(substr($value, 0, 2));
            if (in_array($lang, Localization::SUPPORTED_LANGUAGES)) {
                return $lang;
            }
        }
        return $default_lang;
    }
}
