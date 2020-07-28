<?php


namespace Ling\Light_DeveloperWizard\Service;


use Ling\Bat\CaseTool;
use Ling\Bat\ClassTool;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_DeveloperWizard\Helper\DeveloperWizardGenericHelper;
use Ling\Light_DeveloperWizard\Tool\DeveloperWizardFileTool;
use Ling\Light_DeveloperWizard\Util\serviceManagerUtil;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\AddServiceLingBreeze2GetFactoryMethodProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\AddServiceLogDebugMethodProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\AddStandardPermissionsProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\CreateLss01ServiceProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\CreateServiceProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\GenerateBreezeApiProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\GenerateLkaPlanetProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\SynchronizeDbProcess;
use Ling\Light_DeveloperWizard\WebWizardTools\WebWizard\LightDeveloperWizardWebWizard;
use Ling\Light_PluginInstaller\Service\LightPluginInstallerService;
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
     * This property holds the serviceManagerUtil for this instance.
     * @var serviceManagerUtil
     */
    protected $serviceManagerUtil;


    /**
     * Builds the LightDeveloperWizardService instance.
     */
    public function __construct()
    {
        $this->container = null;
        $this->serviceManagerUtil = null;
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
     * Returns a ServiceManagerUtil instance.
     *
     * @return ServiceManagerUtil
     */
    public function getServiceManagerUtil(): ServiceManagerUtil
    {
        if (null === $this->serviceManagerUtil) {
            $this->serviceManagerUtil = new ServiceManagerUtil();
        }
        return $this->serviceManagerUtil;
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
        $guiDisplay = (int)($_GET['display'] ?? 0);
        $selectedPlanetDir = $_GET['planetdir'] ?? null;
        $task = $_GET['task'] ?? null;


        //--------------------------------------------
        // GUI WIZARD MAIN WINDOW
        //--------------------------------------------
        if (
            0 === $guiDisplay ||
            1 === $guiDisplay
        ) {
            if (null === $selectedPlanetDir) {
                $planetDirs = PlanetTool::getPlanetDirs($universeDir);
            } else {


                $guiDisplay = 1;

                $planetDir = $selectedPlanetDir;
                $preferencesExist = DeveloperWizardFileTool::hasFile($planetDir);
                $preferences = DeveloperWizardFileTool::getPreferences($planetDir);
                list($galaxy, $planet) = PlanetTool::getGalaxyNamePlanetNameByDir($planetDir);
                $tightName = PlanetTool::getTightPlanetName($planet);


                $createFile = $planetDir . "/assets/fixtures/create-structure.sql";
                $createFileExists = file_exists($createFile);
                $serviceFile = $planetDir . "/Service/${tightName}Service.php";
                $serviceFileExists = file_exists($serviceFile);


                $ww = new LightDeveloperWizardWebWizard();
                $ww->setContainer($container);

                $ww->setProcess((new SynchronizeDbProcess())->setCategory("database"));
                $ww->setProcess((new GenerateBreezeApiProcess())->setCategory("class generation"));
                $ww->setProcess((new AddStandardPermissionsProcess())->setCategory("database"));
                $ww->setProcess((new GenerateLkaPlanetProcess())->setCategory("class generation"));
                $ww->setProcess((new CreateServiceProcess())->setCategory("service"));
                $ww->setProcess((new AddServiceLogDebugMethodProcess())->setCategory("service"));
                $ww->setProcess((new AddServiceLingBreeze2GetFactoryMethodProcess())->setCategory("service"));
                $ww->setProcess((new CreateLss01ServiceProcess())->setCategory("service"));


                $ww->setContext([
                    "createFile" => $createFile,
                    "createFileExists" => $createFileExists,
                    "preferencesExist" => $preferencesExist,
                    "preferences" => $preferences,
                    "container" => $container,
                    "galaxy" => $galaxy,
                    "planet" => $planet,
                    "planetDir" => $planetDir,
                ]);
                $ww->setTriggerExtraParams([
                    "planetdir" => $planetDir,
                ]);
                $ww->setOnProcessSuccessMessage('
            <a href="?planetdir=' . htmlspecialchars($planetDir) . '">Click here to continue</a>');

                $ww->setProcessFilter(function ($pName) use ($createFileExists, $serviceFileExists, $serviceFile, $galaxy, $planet) {
                    switch ($pName) {
                        case "syncdb":
                        case "generate-breeze-api":
                        case "generate-lka-planet":
                            if (false === $createFileExists) {
                                return 'Missing <a target="_blank" href="https://github.com/lingtalfi/TheBar/blob/master/discussions/create-file.md">create file.</a>';
                            }
                            break;
                        case "create-service-log-debug-method":
                        case "create-service-get-factory-method":
                            if (false === $serviceFileExists) {
                                return 'Missing the service class file (' . $this->getSymbolicPath($serviceFile) . ').';
                            }

                            if ('create-service-get-factory-method' === $pName) {
                                $factoryName = 'Custom' . CaseTool::toFlexiblePascal($planet) . 'ApiFactory';
                                $factoryClass = $galaxy . "\\" . $planet . '\\Api\\Custom\\' . $factoryName;
                                if (false === ClassTool::isLoaded($factoryClass)) {
                                    return "Factory class not found ($factoryClass). You can add it using the <a href='https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/task-details.md#generate-breeze-api'>Generate Breeze api</a> task";
                                }
                            }


                            break;
                        default:
                            break;
                    }

                    return true;
                });


                $ww->run();

            }
        }
        //--------------------------------------------
        // PLUGIN INSTALLER WINDOW
        //--------------------------------------------
        elseif (2 === $guiDisplay) {

            /**
             * @var $service LightPluginInstallerService
             */
            $service = $container->get("plugin_installer");


            $action = $_GET['action'] ?? null;
            $plugin = $_GET['plugin'] ?? null;


            switch ($action) {
                case "uninstallall":
                    $service->uninstallAll();
                    break;
                case "installall":
                    $service->installAll();
                    break;
                case "uninstall":
                    $service->uninstall($plugin);
                    break;
                case "install":
                    $service->install($plugin);
                    break;
                default:
                    break;
            }


            $pluginNames = $service->getRegisteredPluginNames();


        }

        ?>


        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <script src="/libs/universe/Ling/Jquery/3.5.1/jquery.min.js"></script>
            <title>Light Developer Wizard</title>
            <style>
                .topmenu {
                    background: #2a2f75;
                    display: flex;
                    padding: 3px;
                    color: white;
                }

                .topmenu .item:not(:last-child) {
                    margin-right: 10px;
                }

                .topmenu .item:not(:last-child)::after {
                    content: "|";
                    margin-left: 10px;
                }

                .topmenu a {
                    color: white;
                }
            </style>
        </head>


        <body>

        <div class="topmenu">
            <div class="item"><a href="?display=0">Wizard</a></div>
            <div class="item"><a href="?display=2">Plugin installer</a></div>
        </div>


        <?php if (0 === $guiDisplay): ?>
            <h1>Welcome to the Light_DeveloperWizard script</h1>
            <p>
                Please select a planet <input id="search-input" type="text" value=""/>
            </p>

            <ul id="planet-list">
                <?php foreach ($planetDirs as $planetDir):

                    list($galaxy, $planet) = PlanetTool::getGalaxyNamePlanetNameByDir($planetDir);
                    ?>

                    <li class="planet-item" data-name="<?php echo htmlspecialchars($galaxy . "/" . $planet); ?>">
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

            <?php $ww->render(); ?>
        <?php elseif (2 === $guiDisplay): ?>
            <h1>Light_PluginInstaller plugin</h1>

            <ul>
                <li><a href="?action=uninstallall">Uninstall all</a></li>
                <li><a href="?action=installall">Install all</a></li>
            </ul>

            <table>
                <?php foreach ($pluginNames as $name): ?>
                    <tr>
                        <td><?php echo $name; ?></td>
                        <td><a href="?display=2&action=install&plugin=<?php echo $name; ?>">Install</a></td>
                        <td><a href="?display=2&action=uninstall&plugin=<?php echo $name; ?>">Uninstall</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>


        <script>
            document.addEventListener("DOMContentLoaded", function (event) {
                $(document).ready(function () {


                    //----------------------------------------
                    // SEARCH FILTER
                    //----------------------------------------
                    var jSearch = $('#search-input');
                    var jPlanetList = $('#planet-list');

                    jSearch.on('keydown', function () {
                        var $this = $(this);
                        clearTimeout($.data(this, 'timer'));
                        var wait = setTimeout(function () {
                            var val = $this.val().toLowerCase();
                            jPlanetList.find('.planet-item').each(function () {
                                var planetName = $(this).attr("data-name").toLowerCase();
                                if (-1 !== planetName.indexOf(val)) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            });
                        }, 250);
                        $(this).data('timer', wait);
                    });


                    jSearch.focus();
                });
            });
        </script>
        </body>
        </html>
        <?php
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Returns the symbolic version of the given path.
     *
     * @param string $path
     * @return string
     */
    protected function getSymbolicPath(string $path): string
    {
        return DeveloperWizardGenericHelper::getSymbolicPath($path, $this->container->getApplicationDir());
    }

}