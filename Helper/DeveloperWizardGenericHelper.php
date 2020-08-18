<?php


namespace Ling\Light_DeveloperWizard\Helper;


use Ling\Bat\BDotTool;
use Ling\Light_DeveloperWizard\Exception\LightDeveloperWizardException;
use Ling\Light_DeveloperWizard\Tool\DeveloperWizardFileTool;
use Ling\SqlWizard\Util\MysqlStructureReader;

/**
 * The DeveloperWizardGenericHelper class.
 */
class DeveloperWizardGenericHelper
{


    /**
     * Returns a symbolic path, where the given absolute path to the application directory is replaced by the symbol [app].
     *
     * @param string $path
     * @param string $appDir
     * @return string
     */
    public static function getSymbolicPath(string $path, string $appDir): string
    {
        $p = explode($appDir, $path, 2);
        if (2 === count($p)) {
            return '[app]' . array_pop($p);
        }
        return $path;
    }


    /**
     * Returns the name of the tables found in the given create file.
     *
     * @param string $createFile
     * @return array
     */
    public static function getTablesByCreateFile(string $createFile): array
    {
        $reader = new MysqlStructureReader();
        $infos = $reader->readFile($createFile);
        return array_keys($infos);
    }


    /**
     * Returns the table prefix from either the preferences (if found), or guessed from the given createFile otherwise.
     *
     * @param string $planetDir
     * @param string $createFile
     * @return string
     * @throws \Exception
     */
    public static function getTablePrefix(string $planetDir, string $createFile): string
    {
        $preferences = DeveloperWizardFileTool::getPreferences($planetDir);
        $tablePrefix = BDotTool::getDotValue("general.table_prefix", $preferences, null);

        // guessing the table prefix
        //--------------------------------------------
        if (null === $tablePrefix) {
            $reader = new MysqlStructureReader();
            $infos = $reader->readFile($createFile);
            $firstTable = key($infos);
            $p = explode('_', $firstTable, 2);
            if (1 === count($p)) {
                throw new LightDeveloperWizardException("I wasn't able to guess the prefix for table $firstTable.");
            } else {
                $tablePrefix = array_shift($p);
                // memorizing...
                DeveloperWizardFileTool::updateFile($planetDir, [
                    "general" => [
                        "table_prefix" => $tablePrefix,
                    ],
                ]);
            }
        }
        return $tablePrefix;
    }


}