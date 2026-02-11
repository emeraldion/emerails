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

// This path is relative to the Composer autoload path if available
require_once $GLOBALS['_composer_autoload_path'] ?? __DIR__ . '/../vendor/autoload.php';
// This path is relative to the Composer bin directory if available
include_once ($GLOBALS['_composer_bin_dir'] ?? __DIR__ . '/../vendor/bin') . '/../../config/db.conf.php';
require_once __DIR__ . '/../include/common.inc.php';

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\DbAdapters\MysqliAdapter;
use Emeraldion\EmeRails\DbAdapters\MysqlAdapter;
use Emeraldion\EmeRails\Helpers\ANSIColorWriter;
use Emeraldion\EmeRails\Models\Relationship;

Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);
Db::register_adapter(new MysqlAdapter(), MysqlAdapter::NAME);

abstract class ScriptCommand extends CLI
{
    const OPTION_DRY_RUN = 'dry-run';
    const OPTION_NO_LOGO = 'no-logo';
    const OPTION_VERBOSE = 'verbose';

    protected $name = '<COMMAND NAME>';
    protected $version = '<VERSION>';

    private function hello()
    {
        ANSIColorWriter::print(
            <<<EOT
                                               _ __
               ___  ____ ___  ___  _________ _(_) /____
              / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
             /  __/ / / / / /  __/ /  / /_/ / / (__  )
             \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/


            EOT
            ,
            'bright-green'
        );
        printf(
            <<<EOT
            (c) Claudio Procida 2008-2026

            %s %s


            EOT
            ,
            $this->name,
            $this->version
        );
    }

    protected function execute()
    {
        if (!$this->options->getOpt(self::OPTION_NO_LOGO)) {
            $this->hello();
        }
        parent::execute();
    }

    protected function register_common_options(Options $options): void
    {
        $options->registerOption(self::OPTION_DRY_RUN, 'Do not save anything');
        $options->registerOption(self::OPTION_NO_LOGO, 'Do not print a logo');
        $options->registerOption(self::OPTION_VERBOSE, 'Print additional information', 'v');
    }
}
