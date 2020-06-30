<?php


namespace Ling\Light_DeveloperWizard\Service;


use Ling\BabyYaml\BabyYamlUtil;
use Ling\Bat\BDotTool;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_DeveloperWizard\Helper\DeveloperWizardBreezeGeneratorHelper;
use Ling\Light_DeveloperWizard\Tool\DeveloperWizardFileTool;
use Ling\SqlWizard\Util\MysqlStructureReader;
use Ling\UniverseTools\PlanetTool;

/**
 * The LightDeveloperWizardService class.
 */
class LightDeveloperWizardService
{


    /**
     * This property holds the container for this instance.
     * @var LightServiceContainerInterface
     */
    protected $container;


    /**
     * Builds the LightDeveloperWizardService instance.
     */
    public function __construct()
    {
        $this->container = null;
    }

    /**
     * Sets the container.
     *
     * @param LightServiceContainerInterface $container
     */
    public function setContainer(LightServiceContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Runs the wizard.
     *
     *
     *
     * @throws \Exception
     */
    public function runWizard()
    {

        $appDir = $this->container->getApplicationDir();
        $container = $this->container;

        //--------------------------------------------
        // CONFIG
        //--------------------------------------------
        $universeDir = $appDir . "/universe";


        //--------------------------------------------
        //
        //--------------------------------------------
        /**
         * Note to future self:
         *
         * in this script we don't try to catch exceptions, we let them flow.
         * The idea is that we are developers, this is a tool for developers, we want the full trace when a problem occurs.
         *
         *
         */
        $guiDisplay = 0;
        $error = null;
        $taskMsgs = [];
        $selectedPlanetDir = $_GET['planetdir'] ?? null;
        $task = $_GET['task'] ?? null;
        $preferences = [];


        if (null === $selectedPlanetDir) {
            $planetDirs = PlanetTool::getPlanetDirs($universeDir);
        } else {
            $guiDisplay = 1;
            $planetDir = $selectedPlanetDir;
            $preferencesExist = DeveloperWizardFileTool::hasFile($planetDir);
            $preferences = DeveloperWizardFileTool::getPreferences($planetDir);
            list($galaxy, $planet) = PlanetTool::getGalaxyNamePlanetNameByDir($planetDir);

            $createFile = $planetDir . "/assets/fixtures/create-structure.sql";
            $createFileExists = file_exists($createFile);


            if (null !== $task) {
                switch ($task) {
                    case "syncdb":


                        if (true === $createFileExists) {
                            $options = [];
                            if (false === $preferencesExist) {
                                /**
                                 * Let's gather the created tables and memorize them as scope for the next time
                                 */
                                $taskMsgs[] = "creating developer-wizard preferences file in " . DeveloperWizardFileTool::getFilePath($planetDir);
                                $reader = new MysqlStructureReader();
                                $infos = $reader->readFile($createFile);
                                $tables = array_keys($infos);
                                DeveloperWizardFileTool::updateFile($planetDir, [
                                    "db_synchronizer" => [
                                        "scope" => $tables,
                                    ]
                                ]);
                                $preferences = DeveloperWizardFileTool::getPreferences($planetDir);
                            }


                            $scope = BDotTool::getDotValue("db_synchronizer.scope", $preferences, []);
                            $sScope = '';
                            if (empty($scope)) {
                                $sScope = 'empty scope';
                            } else {
                                $sScope = 'scope: ' . implode(', ', $scope);
                            }
                            $taskMsgs[] = "Synchronizing db for planet $planet, with $sScope.";
                            $container->get("db_synchronizer")->synchronize($createFile, $options);

                        } else {
                            $error = "Create file not found, cannot synchronize the database.";
                        }

                        break;
                    case "generate_api":

                        if (true === $createFileExists) {

                            $preferences = DeveloperWizardFileTool::getPreferences($planetDir);
                            $tablePrefix = BDotTool::getDotValue("breeze_generator.table_prefix", $preferences, null);

                            // guessing the table prefix
                            //--------------------------------------------
                            if (null === $tablePrefix) {
                                $reader = new MysqlStructureReader();
                                $infos = $reader->readFile($createFile);
                                $firstTable = key($infos);
                                $p = explode('_', $firstTable, 2);
                                if (1 === count($p)) {
                                    $error = "No prefix found for table $firstTable.";
                                } else {
                                    $tablePrefix = array_shift($p);
                                    // memorizing...
                                    DeveloperWizardFileTool::updateFile($planetDir, [
                                        "breeze_generator" => [
                                            "table_prefix" => $tablePrefix,
                                        ],
                                    ]);
                                }
                            }

                            $taskMsgs[] = "Using the table prefix: $tablePrefix.";


                            $genConfPath = $appDir . "/config/data/$planet/Light_BreezeGenerator/$tablePrefix.byml";
                            if (false === file_exists($genConfPath)) {
                                $taskMsgs[] = "Creating generator conf file in $genConfPath.";
                                DeveloperWizardBreezeGeneratorHelper::spawnConfFile($genConfPath, [
                                    "galaxyName" => $galaxy,
                                    "planetName" => $planet,
                                    "createFilePath" => $createFile,
                                    "prefix" => $tablePrefix,
                                    "otherPrefixes" => [], // collecting all prefixes from db?
                                ]);
                            }

                            $genConf = BabyYamlUtil::readFile($genConfPath);
                            $taskMsgs[] = "Generating api based on the configuration file $genConfPath.";
                            $container->get("breeze_generator")->setConf(['tmpId' => $genConf])->generate("tmpId");


                        } else {
                            $error = "Create file not found, cannot generate the api.";
                        }


                        break;
                    default:
                        $error = 'Unrecognized task: ' . $task;
                        break;
                }
            }


        }


        ?>


        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Title</title>
            <style>
                .error {
                    color: red;
                }

                .info {
                    color: blue;
                }

                .success {
                    color: green;
                }
            </style>
        </head>


        <body>


        <h1>Welcome to the Light_DeveloperWizard script</h1>

        <?php if (0 === $guiDisplay): ?>
            <p>
                Please select a planet.
            </p>

            <ul>
                <?php foreach ($planetDirs as $planetDir):

                    list($galaxy, $planet) = PlanetTool::getGalaxyNamePlanetNameByDir($planetDir);
                    ?>

                    <li>
                        <a href="?planetdir=<?php echo htmlspecialchars($planetDir); ?>"><?php echo $galaxy . "/" . $planet; ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (1 === $guiDisplay): ?>

            <h4><?php echo $galaxy . "/" . $planet; ?> (<a href="?">back</a>)</h4>

            <p>
                <?php if (true === $createFileExists): ?>
                    Create file detected
                <?php else: ?>
                    Create file not detected, please consider creating a <a
                            href="https://github.com/lingtalfi/TheBar/blob/master/discussions/create-file.md">create
                        file</a>.
                <?php endif; ?>
            </p>


            <div class="result">
                <?php if (null !== $task): ?>

                    <h3>Report
                        <?php if (null === $error): ?>
                            <span class="success">(success)</span>
                        <?php else: ?>
                            <span class="error">(error)</span>
                        <?php endif; ?>
                    </h3>
                    <?php if (null !== $error): ?>
                        <span class="error"><?php echo $error; ?></span>
                    <?php else: ?>
                        <?php if (null !== $task): ?>

                            <span class="info">Executing task <?php echo $task; ?>:</span>

                            <?php if ($taskMsgs): ?>
                                <ul>
                                    <?php foreach ($taskMsgs as $msg): ?>
                                        <li class="info"><?php echo $msg; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>


            <?php if (null !== $task && null === $error): ?>
                <a href="?planetdir=<?php echo htmlspecialchars($planetDir); ?>">Click here to continue</a>
            <?php else: ?>
                <div class="tasklist">
                    <h3>Available tasks</h3>
                    <ul>
                        <?php if (true === $createFileExists): ?>
                            <li><a href="?planetdir=<?php echo htmlspecialchars($planetDir); ?>&task=syncdb">Synchronize
                                    the
                                    current db with the create file (using Light_DbSynchronizer)</a></li>
                            <li><a href="?planetdir=<?php echo htmlspecialchars($planetDir); ?>&task=generate_api">Generate
                                    the
                                    api from the create file (using Ling Breeze Generator 2) </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        </body>
        </html>
        <?php
    }
}