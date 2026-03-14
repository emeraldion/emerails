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

use Emeraldion\EmeRails\Config;

function getc(string &$str): string
{
    $c = substr($str, 0, 1);
    $str = substr($str, 1);
    return $c;
}

abstract class ComponentParser
{
    const COMPONENT_OPENING_TAG = '<x:';
    const COMPONENT_CLOSING_TAG_SIMPLE = '>';
    const COMPONENT_CLOSING_TAG_CONTAINER = '</x:';

    const STATE_COMPONENT_NAME = 0;
    const STATE_ATTRIBUTE_LIST = 1;
    const STATE_ATTRIBUTE_NAME = 2;
    const STATE_ATTRIBUTE_NAME_EXPECTING_EQUAL = 3;
    const STATE_ATTRIBUTE_EQUAL = 4;
    const STATE_ATTRIBUTE_OPENING = 5;
    const STATE_ATTRIBUTE_VALUE = 6;
    const STATE_ATTRIBUTE_EXPRESSION = 7;
    const STATE_ATTRIBUTE_CLOSING_QUOTE = 8;
    const STATE_ATTRIBUTE_CLOSING_BRACE = 9;
    const STATE_CHILDREN = 10;
    const STATE_OPENING_TAG = 11;
    const STATE_CLOSING_TAG = 12;

    const ATTRIBUTE_TYPE_STRING = 'string';
    const ATTRIBUTE_TYPE_EXPRESSION = 'expression';

    const MAX_COMPONENTS = 16;
    const MAX_COMPONENT_LENGTH = 1024;

    const RESERVED_ATTRIBUTE_NAMES = ['action', 'name', 'type'];

    public static function parse_contents(
        string $contents,
        int $max_components = self::MAX_COMPONENTS,
        int $max_component_length = self::MAX_COMPONENT_LENGTH
    ): string {
        $ret = '';

        if (strpos($contents, self::COMPONENT_OPENING_TAG) === false) {
            $ret = $contents;
        } else {
            $i = 0;
            while (($start_pos = strpos($contents, self::COMPONENT_OPENING_TAG)) !== false && $i < $max_components) {
                $ret .= substr($contents, 0, $start_pos);
                $contents = substr($contents, $start_pos + strlen(self::COMPONENT_OPENING_TAG));

                $is_at_end = false;
                $state = self::STATE_COMPONENT_NAME;
                $has_children = true;
                $component_name = '';
                $attribute_name = '';
                $attribute_type = null;
                $attribute_quote = '';
                $attribute_value = '';
                $attributes = [];
                $children = '';
                $j = 0;
                while (!$is_at_end && $j < $max_component_length) {
                    $c = getc($contents);
                    // printf("Character: '%s'\t", $c);
                    switch ($state) {
                        case self::STATE_COMPONENT_NAME:
                            switch ($c) {
                                case ' ':
                                case '\t':
                                case '\n':
                                    $state = self::STATE_ATTRIBUTE_LIST;
                                    break;
                                case '>':
                                    $state = self::STATE_CHILDREN;
                                    break;
                                case '/':
                                    $state = self::STATE_CLOSING_TAG;
                                    break;
                                default:
                                    $component_name .= $c;
                                    break;
                            }
                            break;
                        case self::STATE_ATTRIBUTE_LIST:
                            switch ($c) {
                                case '>':
                                    $state = self::STATE_CHILDREN;
                                    break;
                                case '/':
                                    $state = self::STATE_CLOSING_TAG;
                                    break;
                                case ' ':
                                case '\t':
                                case '\n':
                                    // Stay in STATE_ATTRIBUTE_LIST
                                    break;
                                default:
                                    // if (is_alphanumeric($c)) {
                                    $state = self::STATE_ATTRIBUTE_NAME;
                                    $attribute_name = $c;
                                // }
                            }
                            break;
                        case self::STATE_ATTRIBUTE_NAME:
                            switch ($c) {
                                case ' ':
                                case '\t':
                                case '\n':
                                    $state = self::STATE_ATTRIBUTE_NAME_EXPECTING_EQUAL;
                                    break;
                                case '=':
                                    $state = self::STATE_ATTRIBUTE_EQUAL;
                                    break;
                                default:
                                    $attribute_name .= $c;
                                    break;
                            }
                            break;
                        case self::STATE_ATTRIBUTE_NAME_EXPECTING_EQUAL:
                            switch ($c) {
                                case ' ':
                                case '\t':
                                case '\n':
                                    // Stay in STATE_ATTRIBUTE_NAME_EXPECTING_EQUAL
                                    break;
                                case '=':
                                    $state = self::STATE_ATTRIBUTE_EQUAL;
                                    break;
                                case '/':
                                    // Commit the previous attribute
                                    $attributes[$attribute_name] = [
                                        'value' => true,
                                        'type' => self::ATTRIBUTE_TYPE_EXPRESSION
                                    ];
                                    $state = self::STATE_CLOSING_TAG;
                                    break;
                                default:
                                    // Commit the previous attribute
                                    $attributes[$attribute_name] = [
                                        'value' => true,
                                        'type' => self::ATTRIBUTE_TYPE_EXPRESSION
                                    ];
                                    // Start a new one
                                    $state = self::STATE_ATTRIBUTE_NAME;
                                    $attribute_name = $c;
                                    break;
                            }
                            break;
                        case self::STATE_ATTRIBUTE_EQUAL:
                            switch ($c) {
                                case '"':
                                case '\'':
                                    $state = self::STATE_ATTRIBUTE_VALUE;
                                    $attribute_quote = $c;
                                    break;
                                case '{':
                                    $state = self::STATE_ATTRIBUTE_EXPRESSION;
                                    break;
                                case ' ':
                                case '\t':
                                case '\n':
                                    $state = self::STATE_ATTRIBUTE_OPENING;
                                    break;
                                default:
                                    throw new Exception('Malformed attribute ' . $attribute_name);
                            }
                            break;
                        case self::STATE_ATTRIBUTE_OPENING: // Conflate with STATE_ATTRIBUTE_EQUAL ?
                            switch ($c) {
                                case '"':
                                case '\'':
                                    $state = self::STATE_ATTRIBUTE_VALUE;
                                    $attribute_quote = $c;
                                    break;
                                case '{':
                                    $state = self::STATE_ATTRIBUTE_EXPRESSION;
                                    break;
                                case ' ':
                                case '\t':
                                case '\n':
                                    // $state = self::STATE_ATTRIBUTE_OPENING;
                                    break;
                                default:
                                    throw new Exception('Malformed attribute ' . $attribute_name);
                            }
                            break;
                        case self::STATE_ATTRIBUTE_VALUE:
                            switch ($c) {
                                case $attribute_quote:
                                    // Commit the previous attribute
                                    $attributes[$attribute_name] = [
                                        'value' => $attribute_value,
                                        'type' => self::ATTRIBUTE_TYPE_STRING
                                    ];
                                    $state = self::STATE_ATTRIBUTE_LIST;
                                    // Reset attribute
                                    $attribute_name = '';
                                    $attribute_value = '';
                                    break;
                                default:
                                    $attribute_value .= $c;
                                    break;
                            }
                            break;
                        case self::STATE_ATTRIBUTE_EXPRESSION:
                            switch ($c) {
                                // TODO: support nested braces
                                case '}':
                                    // Commit the previous attribute
                                    $attributes[$attribute_name] = [
                                        'value' => $attribute_value,
                                        'type' => self::ATTRIBUTE_TYPE_EXPRESSION
                                    ];
                                    $state = self::STATE_ATTRIBUTE_LIST;
                                    // Reset attribute
                                    $attribute_name = '';
                                    $attribute_value = '';
                                    break;
                                default:
                                    $attribute_value .= $c;
                                    break;
                            }
                            break;
                        case self::STATE_CHILDREN:
                            // TODO: push a new component onto the stack
                            break;
                        case self::STATE_OPENING_TAG:
                            // TODO: do we really need this?
                            break;
                        case self::STATE_CLOSING_TAG:
                            switch ($c) {
                                case '>':
                                    $is_at_end = true;
                                    // Append parsed component
                                    $ret .= self::render_component($component_name, $attributes);
                                    // TODO: and pop the stack
                                    break;
                                case ' ':
                                case '\t':
                                case '\n':
                                    // Stay in STATE_CLOSING_TAG
                                    break;
                                default:
                                    throw new Exception('Unexpected character after \'/\': ', $c);
                            }
                            break;
                    }
                    $j += 1;
                    // printf("State: %d\n", $state);
                }

                // self::print_component($component_name, $attributes);

                $i += 1;
            }
            $ret .= $contents;
        }

        return $ret;
    }

    protected static function normalize_component_name(string $component_name): string
    {
        return str_replace('-', '_', strtolower($component_name));
    }

    protected static function normalize_attribute_name(string $attribute_name): string
    {
        return str_replace('-', '_', strtolower($attribute_name));
    }

    public static function print_component(string $component_name, array $attributes, $children = []): void
    {
        $attributes_string = "{\n";
        foreach ($attributes as $name => $attr) {
            ['value' => $value, 'type' => $type] = $attr;
            switch ($type) {
                case self::ATTRIBUTE_TYPE_STRING:
                    $ret .= "\t'$name' => '$value',\n";
                    break;
                case self::ATTRIBUTE_TYPE_EXPRESSION:
                    $ret .= "\t'$name' => $value,\n";
                    break;
            }
        }
        $attributes_string .= '    }';
        printf(
            <<<EOT
            {
                component: %s
                attributes: %s
            }
            EOT
            ,
            $component_name,
            $attributes_string
        );
    }

    protected static function print_attributes(array $attributes): string
    {
        $ret = "[\n";
        foreach ($attributes as $name => $attr) {
            // var_dump($attr);
            $name = self::normalize_attribute_name($name);
            if (in_array($name, self::RESERVED_ATTRIBUTE_NAMES)) {
                $name = '_' . $name;
            }
            ['value' => $value, 'type' => $type] = $attr;
            switch ($type) {
                case self::ATTRIBUTE_TYPE_STRING:
                    $ret .= "\t'$name' => '$value',\n";
                    break;
                case self::ATTRIBUTE_TYPE_EXPRESSION:
                    if ($value === true) {
                        $ret .= "\t'$name' => true,\n";
                    } elseif ($value === false) {
                        $ret .= "\t'$name' => false,\n";
                    } else {
                        $ret .= "\t'$name' => $value,\n";
                    }
                    break;
            }
        }
        $ret .= "]\n";

        return $ret;
    }

    public static function render_component(string $component_name, array $attributes, $children = []): string
    {
        $component_name_parts = explode(':', self::normalize_component_name($component_name));
        if (count($component_name_parts) == 1) {
            $controller = 'common';
            [$action] = $component_name_parts;
        } else {
            [$controller, $action] = $component_name_parts;
        }
        return sprintf(
            <<<EOT
            <?php
            \$this->render_component([
                'controller' => '%s',
                'action' => '%s',
                'props' => %s
            ]);
            ?>
            EOT
            ,
            $controller,
            $action,
            self::print_attributes($attributes)
        );
    }
}
