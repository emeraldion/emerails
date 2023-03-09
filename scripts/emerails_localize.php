#!/usr/bin/php
<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
 */

require_once __DIR__ . '/base.php';

use splitbrain\phpcli\Options;

use Emeraldion\EmeRails\Helpers\ANSIColorWriter;

class EmerailsLocalize extends ScriptCommand
{
    protected $name = 'EmeRails Localization Utility';
    protected $version = 'v1.0';

    const DEFAULT_BASE_DIR = __DIR__ . '/../assets/strings';
    const STRINGS_FILE_EXTENSION = '.strings';

    private $thesaurus = array();

    protected function setup(Options $options)
    {
        $options->setHelp('EmeRails script to manage localization resources');

        // General options
        $options->registerOption('dry-run', 'Do not save anything');
        $options->registerOption('verbose', 'Print additional information', 'v');
        $options->registerOption('base-dir', 'Base directory of strings files', 'd');

        // Check command
        $options->registerCommand('check', 'Check localizations');
        // Check options
        $options->registerOption('recursive', 'Recursively descend into subdirectories', 'r', false, 'check');
        $options->registerOption('strict', 'Fail in presence of warnings', null, false, 'check');

        // Create command
        $options->registerCommand('create', 'Create localizations');
        // Check options
        $options->registerOption(
            'global',
            sprintf(
                'Create global localizations. When provided, the %s option will be ignored.',
                ANSIColorWriter::colorize('--controller', 'cyan')
            ),
            'g',
            false,
            'create'
        );
        $options->registerOption('controller', 'Create localizations for a controller', 'c', 'controller', 'create');
        $options->registerOption(
            'languages',
            'Comma-separated list of languages for localizations',
            'l',
            'languages',
            'create'
        );
    }

    protected function main(Options $options)
    {
        $dry_run = $options->getOpt('dry-run');
        $verbose = $options->getOpt('verbose');
        $base_dir = $options->getOpt('base-dir') ?: self::DEFAULT_BASE_DIR;

        switch ($options->getCmd()) {
            case 'check':
                if (!(file_exists($base_dir) && is_dir($base_dir))) {
                    printf("No such directory: %s.\n", $base_dir);
                    ANSIColorWriter::printf("\n%s\n", 'green', ANSIColorWriter::bold('Done'));
                    exit(0);
                }

                if ($this->scandir($base_dir, $options->getOpt('recursive'), $options->getOpt('strict'))) {
                    print "All strings files correctly formed.\n";
                    ANSIColorWriter::printf("\n%s\n", 'green', ANSIColorWriter::bold('Done'));
                } else {
                    ANSIColorWriter::printf("\n%s\n", 'red', ANSIColorWriter::bold('Failure'));
                    exit(1);
                }
                break;
            case 'create':
                if ($options->getOpt('global')) {
                    $dir = $base_dir;
                } else {
                    $controller = $options->getOpt('controller');
                    $dir = $base_dir . '/' . $controller;
                }
                echo "\tcreating localizations under $dir\n";

                $template = <<<EOT
array(
    // Add your keys below:
    // 'key-of-your-string' => 'This is the user visible text',
);

EOT;

                if (!(file_exists($dir) && is_dir($dir))) {
                    if (!mkdir($dir, 0755, true)) {
                        exit(1);
                    }
                }
                foreach (explode(',', $options->getOpt('languages')) as $language) {
                    file_put_contents(sprintf('%s/localizable-%s.strings', $dir, $language), $template);
                }
                break;
            default:
                $options->useCompactHelp();
                print $options->help();
        }
    }

    protected function scandir($dir, $recursive = false, $strict = false)
    {
        $success = true;

        if (!(file_exists($dir) && is_dir($dir))) {
            return false;
        }
        $handle = opendir($dir);
        if ($handle) {
            while (($filename = readdir($handle)) !== false) {
                if ($filename === '.' || $filename === '..') {
                    continue;
                }
                $path = $dir . '/' . $filename;
                if ($recursive && is_dir($path)) {
                    $success = $success && $this->scandir($path, $recursive, $strict);
                }
                if (str_ends_with($filename, self::STRINGS_FILE_EXTENSION)) {
                    $success = $success && $this->check_file($dir, $filename);
                }
            }
        }

        $dir = $this->get_string_file($dir);
        if (isset($this->thesaurus[$dir])) {
            foreach ($this->thesaurus[$dir] as $language => $strings) {
                if ($language == 'en') {
                    continue;
                }
                $common_strings = array_intersect_key($this->thesaurus[$dir]['en'], $this->thesaurus[$dir][$language]);
                if (count($strings) > count($common_strings)) {
                    $success = !$strict;
                    foreach (
                        array_diff_key($this->thesaurus[$dir][$language], $this->thesaurus[$dir]['en'])
                        as $key => $value
                    ) {
                        ANSIColorWriter::printf("Warning: missing key in strings file\n", 'yellow');
                        ANSIColorWriter::printf("------------------------------------\n", 'bright-black');
                        ANSIColorWriter::printf(
                            "Language: %s\n",
                            'bright-black',
                            ANSIColorWriter::colorize('en', 'white')
                        );
                        ANSIColorWriter::printf(
                            "    File: %s\n",
                            'bright-black',
                            ANSIColorWriter::colorize(sprintf('%s/localizable-%s.strings', $dir, 'en'), 'white')
                        );
                        ANSIColorWriter::printf(
                            "     Key: %s\n\n",
                            'bright-black',
                            ANSIColorWriter::colorize($key, 'white')
                        );
                    }
                } elseif (count($this->thesaurus[$dir]['en']) > count($common_strings)) {
                    $success = !$strict;
                    foreach (
                        array_diff_key($this->thesaurus[$dir]['en'], $this->thesaurus[$dir][$language])
                        as $key => $value
                    ) {
                        ANSIColorWriter::printf("Warning: missing key in strings file\n", 'yellow');
                        ANSIColorWriter::printf("------------------------------------\n", 'bright-black');
                        ANSIColorWriter::printf(
                            "Language: %s\n",
                            'bright-black',
                            ANSIColorWriter::colorize($language, 'white')
                        );
                        ANSIColorWriter::printf(
                            "    File: %s\n",
                            'bright-black',
                            ANSIColorWriter::colorize(sprintf('%s/localizable-%s.strings', $dir, $language), 'white')
                        );
                        ANSIColorWriter::printf(
                            "     Key: %s\n\n",
                            'bright-black',
                            ANSIColorWriter::colorize($key, 'white')
                        );
                    }
                }
            }
        }

        return $success;
    }

    protected function check_file($dir, $filename)
    {
        $success = true;

        $path = $dir . '/' . $filename;

        $handle = fopen($path, 'r');
        $contents = fread($handle, filesize($path));
        fclose($handle);

        preg_match('/-([a-z]{2})\.strings$/', $filename, $matches);
        list(, $language) = $matches;

        try {
            eval("\$strings = $contents;");

            foreach ($strings as $key => $value) {
                $this->set_string($dir, $language, $key, $value);
            }
        } catch (Throwable $t) {
            $success = false;
            printf(
                "[%s] Malformed strings file: %s\n\t%s\n\n",
                ANSIColorWriter::colorize('Error', 'red'),
                $path,
                $t->getMessage()
            );
            $lines = explode("\n", $contents);
            foreach (range($t->getLine() - 2, $t->getLine() + 2) as $i) {
                if ($i === $t->getLine()) {
                    ANSIColorWriter::printf("%4d | %s\n", 'red', $i, $lines[$i - 1]);
                } else {
                    printf("%4d | %s\n", $i, $lines[$i - 1]);
                }
            }
            printf("\n");
        } finally {
            return $success;
        }
    }

    protected function set_string($dir, $language, $key, $value)
    {
        $dir = $this->get_string_file($dir);
        if (!isset($this->thesaurus[$dir])) {
            $this->thesaurus[$dir] = array();
        }
        if (!isset($this->thesaurus[$dir][$language])) {
            $this->thesaurus[$dir][$language] = array();
        }
        $this->thesaurus[$dir][$language][$key] = $value;
    }

    protected function get_string_file($dir)
    {
        if (strpos($dir, self::DEFAULT_BASE_DIR) === 0) {
            $dir = substr($dir, strlen(self::DEFAULT_BASE_DIR) + 1);
        }
        return $dir;
    }
}

$cli = new EmerailsLocalize();
$cli->run();


?>
