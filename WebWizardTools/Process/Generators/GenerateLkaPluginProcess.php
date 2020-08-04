<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process\Generators;


use Ling\Bat\CaseTool;
use Ling\Bat\FileSystemTool;
use Ling\ClassCooker\FryingPan\Ingredient\ParentIngredient;
use Ling\Light\Helper\LightNamesAndPathHelper;
use Ling\Light_DatabaseInfo\Service\LightDatabaseInfoService;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\LightDeveloperWizardCommonProcess;
use Ling\Light_Kit_Admin_Generator\Service\LightKitAdminGeneratorService;
use Ling\Light_LingStandardService\Helper\LightLingStandardServiceHelper;
use Ling\Light_UserDatabase\Service\LightUserDatabaseService;
use Ling\SqlWizard\Util\MysqlStructureReader;
use Ling\UniverseTools\PlanetTool;


/**
 * The GenerateLkaPluginProcess class.
 */
abstract class GenerateLkaPluginProcess extends LightDeveloperWizardCommonProcess
{

    /**
     * This property holds the checkCreateFileExists for this instance.
     * @var bool = true
     */
    protected $checkCreateFileExists;


    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->checkCreateFileExists = true;
        $this->setName("generate-lka-plugin");
        $this->setLabel("Generates lka plugin.");
        $this->setLearnMoreByHash('generate-light_kit_admin-plugin');
    }


    /**
     * @overrides
     */
    public function prepare()
    {
        parent::prepare();


        if (true === empty($this->getDisabledReason())) {
            if (true === $this->checkCreateFileExists) {
                $createFileExists = $this->getContextVar("createFileExists");
                if (false === $createFileExists) {
                    $this->setDisabledReason('Missing <a target="_blank" href="https://github.com/lingtalfi/TheBar/blob/master/discussions/create-file.md">create file.</a>');
                }
            }
        }

    }


    /**
     * Generate the Lka planet from the given params.
     *
     * The params are:
     *
     * - galaxy: the name of the galaxy to create
     * - planet: the name of the planet to create
     *
     *
     * Available options are:
     * - recreateEverything: bool=false, whether to force re-creating things even if they already exist
     *
     *
     * @param array $params
     * @param array $options
     */
    protected function generateLkaPlanet(array $params, array $options = [])
    {

        $galaxy = $params['galaxy'];
        $planet = $params['planet'];

        $recreateEverything = $options['recreateEverything'] ?? false;
        $generateConfigFile = true; // might become an option at some point


        $serviceName = LightNamesAndPathHelper::getServiceName($planet);
        $tightPlanetName = PlanetTool::getTightPlanetName($planet);
        $appDir = $this->container->getApplicationDir();
        $planetDir = $appDir . "/universe/$galaxy/$planet";


        //--------------------------------------------
        // SERVICE CONFIG FILE
        //--------------------------------------------
        $configServicePath = $appDir . "/config/services/$planet.byml";


        if (false === $recreateEverything && file_exists($configServicePath)) {
            /**
             * Note: we could also parse the file and add only missing features,
             * but that would require more time, and I believe most of the time, the user starts
             * fresh and just want to create the whole planet from a new plugin that he is working on...
             */
            $this->infoMessage("Service file found already, skipping.");
        } else {
            $this->infoMessage("Creating service config file at \"" . $this->getSymbolicPath($configServicePath) . "\", with service name \"$serviceName\"");
//            $tpl = __DIR__ . "/../../../assets/conf-template/configService.byml";
            $tpl = __DIR__ . "/../../../assets/conf-template/configServiceBasic.byml";
            $tplContent = file_get_contents($tpl);
            $tplContent = str_replace([
                'Ling\Light_Kit_Admin_XXX',
                'kit_admin_xxx',
                'Light_Kit_Admin_XXX',
                'LightKitAdminXXX',
            ], [
                $galaxy . "\\" . $planet,
                $serviceName,
                $planet,
                $tightPlanetName,
            ], $tplContent);


            FileSystemTool::mkfile($configServicePath, $tplContent);
        }


        //--------------------------------------------
        // SERVICE CLASS FILE
        //--------------------------------------------
        $serviceClassName = $tightPlanetName . "Service.php";
        $serviceClassPath = $planetDir . "/Service/$serviceClassName";
        if (file_exists($serviceClassPath)) {

            $this->infoMessage("The service class for planet $planet was already created.");


            $pan = $this->getFryingPanForService($serviceClassPath);

            $useStatementClass = "Ling\Light_LingStandardService\Service\LightLingStandardServiceKitAdminPlugin";
            $pan->addIngredient(ParentIngredient::create()->setValue('LightLingStandardServiceKitAdminPlugin', [
                'useStatement' => $useStatementClass,
            ]));


            $this->addServiceContainer($pan);


            $pan->cook();


        } else {
            $this->infoMessage("Creating service class file at \"" . $this->getSymbolicPath($serviceClassPath) . "\".");
            $tpl = __DIR__ . "/../../../assets/class-templates/Service/LkaPluginLss.phptpl";
            $tplContent = file_get_contents($tpl);
            $tplContent = str_replace([
                'Light_Kit_Admin_XXX',
                'LightKitAdminXXX',
            ], [
                $planet,
                $tightPlanetName,
            ], $tplContent);
            FileSystemTool::mkfile($serviceClassPath, $tplContent);
        }


        //--------------------------------------------
        // LKA GENERATOR CONFIG FILE
        //--------------------------------------------
        if (true === $generateConfigFile) {
            $this->createLkaGeneratorConfigFile([
                'galaxy' => $galaxy,
                'planet' => $planet,
            ], [
                'recreateEverything' => $recreateEverything,
            ]);
        }

    }


    /**
     * Creates the lka generator config file, and returns its path.
     *
     * Params are:
     * - galaxy: string, the name of the galaxy to create the config file for
     * - planet: string, the name of the planet to create the config file for
     *
     *
     * Available options are:
     *
     * - recreateEverything: bool=false, whether to force the creation of this file
     *
     * Available options are:
     * - recreateEverything: bool=false, whether to force re-creating things even if they already exist
     *
     *
     * @param array $params
     * @param array $options
     *
     * @throws \Exception
     */
    protected function createLkaGeneratorConfigFile(array $params, array $options = []): string
    {


        $galaxy = $params['galaxy'];
        $planet = $params['planet'];
        $recreateEverything = $options['recreateEverything'] ?? false;


        $appDir = $this->container->getApplicationDir();
        $lkaOriginPlanet = $this->getLkaOriginPlanet($planet);
        $createFile = $appDir . "/universe/$galaxy/$lkaOriginPlanet/assets/fixtures/create-structure.sql";
        $planetDir = $appDir . "/universe/$galaxy/$lkaOriginPlanet";
        $tables = $this->getTablesByCreateFile($createFile);


        $serviceName = LightNamesAndPathHelper::getServiceName($planet);
        $tablePrefix = $this->getTablePrefix($planetDir, $createFile);


        $lkaGenConfigPath = $appDir . "/config/data/$planet/Light_Kit_Admin_Generator/$serviceName.generated.byml";


        if (false === $recreateEverything && file_exists($lkaGenConfigPath)) {
            $this->infoMessage("Light_Kit_Admin_Generator config file already found in " . $this->getSymbolicPath($lkaGenConfigPath));
        } else {
            $this->infoMessage("Creating Light_Kit_Admin_Generator config file in " . $this->getSymbolicPath($lkaGenConfigPath));
            $tpl = __DIR__ . "/../../../assets/conf-template/lka-gen-config.byml";
            $humanMenuName = ucwords(CaseTool::toHumanFlatCase(substr($lkaOriginPlanet, 6)));
            $sTables = '';
            foreach ($tables as $table) {
                $sTables .= '            - ' . $table . PHP_EOL;
            }


            $tplContent = file_get_contents($tpl);
            $tplContent = str_replace([
                'Light_Kit_Admin_TaskScheduler',
                'Task scheduler',
                'galaxyName: Ling',
                'kit_admin_task_scheduler',
                'prefix: lts',
                '            - lts_task_schedule',
                'createFile: {app_dir}/universe/Ling/Light_TaskScheduler/assets/fixtures/create-structure.sql',
            ], [
                $planet,
                $humanMenuName,
                'galaxyName: ' . $galaxy,
                $serviceName,
                'prefix: ' . $tablePrefix,
                $sTables,
                "createFile: {app_dir}/universe/$galaxy/$lkaOriginPlanet/assets/fixtures/create-structure.sql",
            ], $tplContent);

            FileSystemTool::mkfile($lkaGenConfigPath, $tplContent);
        }
        return $lkaGenConfigPath;
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Returns the name of the tables found in the given create file.
     *
     * @param string $createFile
     * @return array
     */
    protected function getTablesByCreateFile(string $createFile): array
    {
        $reader = new MysqlStructureReader();
        $infos = $reader->readFile($createFile);
        return array_keys($infos);
    }


    /**
     * Returns the name of the planet from which the given lka planet originates from.
     *
     * For instance, if you pass Light_Kit_Admin_XXX, it will return Light_XXX.
     *
     * @param string $lkaPlanetName
     * @return string
     */
    protected function getLkaOriginPlanet(string $lkaPlanetName): string
    {
        return 'Light_' . substr($lkaPlanetName, 16);
    }


    /**
     * Returns the lka service name corresponding to the given planet name.
     *
     * @param string $planet
     * @return string
     */
    protected function getLkaServiceNameByPlanet(string $planet): string
    {
        return "kit_admin_" . CaseTool::toSnake(substr($planet, 6), true);
    }


    /**
     * Returns the planetId corresponding to the given planet name.
     *
     * @param $planet
     * @return string
     */
    protected function getPlanetId($planet): string
    {
        return substr($planet, 6);
    }


    /**
     * Returns the lka planet name for the given planet.
     *
     * @param string $planet
     * @return string
     */
    protected function getLkaPlanetNameByPlanet(string $planet): string
    {
        $planetId = $this->getPlanetId($planet);
        return "Light_Kit_Admin_" . $planetId;
    }

    /**
     * Executes the given generator config file path, using the @page(Light_Kit_Admin_Generator) plugin.
     *
     * Available options are:
     *
     * - recreateEverything: bool = false, whether to recreate things even if they exist already
     *
     *
     * @param string $path
     * @param array $options
     */
    protected function executeGeneratorConfigFile(string $path, array $options = [])
    {
        $recreateEverything = $options['recreateEverything'] ?? false;


        $this->infoMessage("Launching Light_Kit_Admin_Generator with config file " . $this->getSymbolicPath($path));
        /**
         * @var $lkaGenerator LightKitAdminGeneratorService
         */
        $lkaGenerator = $this->container->get("kit_admin_generator");
        $config = $lkaGenerator->generate($path); // assuming identifier=main
        if (false === array_key_exists('create_file', $config)) {
            $this->error("Sorry, we expected the create_file entry in the lka generator config file, but it was not found, aborting.");
        }
        $createFile = $config['create_file'];

        $useForm = $config['use_form'] ?? false;
        $useMenu = $config['use_menu'] ?? false;
        $useController = $config['use_controller'] ?? false;
        $useList = $config['use_list'] ?? false;


        $planet = $config['plugin_name'];
        $variables = $config['variables'] ?? [];
        $tables = $variables['tables'] ?? [];
        if (false === array_key_exists('galaxyName', $variables)) {
            $this->error("Sorry, we expected the variables.galaxyName entry in the lka generator config file, but it was not found, aborting.");
        }


        $galaxy = $variables['galaxyName'];
        $appDir = $this->container->getApplicationDir();


        $serviceName = LightNamesAndPathHelper::getServiceName($planet);
        $createFile = str_replace('{app_dir}', $appDir, $createFile);

        $tightName = PlanetTool::getTightPlanetName($planet);
        $planetDir = $appDir . "/universe/$galaxy/$planet";
        $originPlanet = $this->getLkaOriginPlanet($planet);
        $originPlanetDir = $appDir . "/universe/$galaxy/$originPlanet";


        //--------------------------------------------
        // GENERATING CONTROLLER HUB CLASS
        //--------------------------------------------
        if (true === $useController) {
            $controllerHubClassPath = $planetDir . "/ControllerHub/Generated/$tightName" . "ControllerHubHandler.php";

            if (false === $recreateEverything && true === file_exists($controllerHubClassPath)) {
                $this->infoMessage("ControllerHub class already found in " . $this->getSymbolicPath($controllerHubClassPath));
            } else {
                $this->infoMessage("Creating ControllerHub class in " . $this->getSymbolicPath($controllerHubClassPath));

                $tpl = __DIR__ . "/../../../assets/class-templates/ControllerHub/LightKitAdminTaskSchedulerControllerHubHandler.php";
                $tplContent = file_get_contents($tpl);
                $tplContent = str_replace([
                    'namespace Ling\Light_Kit_Admin_TaskScheduler\ControllerHub;',
                    'LightKitAdminTaskSchedulerControllerHubHandler',
                ], [
                    "namespace $galaxy\\$planet\ControllerHub\Generated;",
                    $tightName . 'ControllerHubHandler',
                ], $tplContent);
                FileSystemTool::mkfile($controllerHubClassPath, $tplContent);
            }
        }


        //--------------------------------------------
        // GENERATING LKA PLUGIN CLASS
        //--------------------------------------------
        $lkaPluginClassPath = $planetDir . "/LightKitAdminPlugin/Generated/$tightName" . "LkaPlugin.php";

        if (false === $recreateEverything && true === file_exists($lkaPluginClassPath)) {
            $this->infoMessage("LkaPlugin class already found in " . $this->getSymbolicPath($lkaPluginClassPath));
        } else {
            $this->infoMessage("Creating LkaPlugin class in " . $this->getSymbolicPath($lkaPluginClassPath));

            $tpl = __DIR__ . "/../../../assets/class-templates/LightKitAdminPlugin/LightKitAdminTaskSchedulerLkaPlugin.php";
            $tplContent = file_get_contents($tpl);
            $tplContent = str_replace([
                'namespace Ling\Light_Kit_Admin_TaskScheduler\LightKitAdminPlugin;',
                'LightKitAdminTaskSchedulerLkaPlugin',
            ], [
                "namespace $galaxy\\$planet\LightKitAdminPlugin\Generated;",
                $tightName . 'LkaPlugin',
            ], $tplContent);
            FileSystemTool::mkfile($lkaPluginClassPath, $tplContent);
        }


        //--------------------------------------------
        // GENERATING LKA PLUGIN CONFIG DATA
        //--------------------------------------------
        if (true === $useForm) {

            $path = $appDir . "/config/data/$planet/Light_Kit_Admin/lka-options.generated.byml";
            $tablePrefix = $this->getTablePrefix($originPlanetDir, $createFile);


            if (false === $recreateEverything && true === file_exists($path)) {
                $this->infoMessage("LkaPlugin config data already found in " . $this->getSymbolicPath($path));
            } else {
                $this->infoMessage("Creating LkaPlugin config data in " . $this->getSymbolicPath($path));

                $tpl = __DIR__ . "/../../../assets/conf-template/data/Light_Kit_Admin/lka-options.byml";
                $tplContent = file_get_contents($tpl);
                $tplContent = str_replace([
                    'lts',
                    'Light_Kit_Admin_TaskScheduler',
                ], [
                    $tablePrefix,
                    $planet,
                ], $tplContent);
                FileSystemTool::mkfile($path, $tplContent);
            }
        }

        //--------------------------------------------
        // GENERATING MICRO-PERMISSION CONFIG DATA
        //--------------------------------------------
        $path = $appDir . "/config/data/$planet/Light_MicroPermission/$serviceName.profile.generated.byml";
        if (false === $recreateEverything && true === file_exists($path)) {
            $this->infoMessage("MicroPermission config data already found in " . $this->getSymbolicPath($path));
        } else {
            $this->infoMessage("Creating MicroPermission config data in " . $this->getSymbolicPath($path));

            $tpl = __DIR__ . "/../../../assets/conf-template/data/Light_MicroPermission/lka_task_scheduler.profile.byml";
            $tplContent = file_get_contents($tpl);
            $sTables = '';
            foreach ($tables as $table) {
                $sTables .= "    - tables.$table.create" . PHP_EOL;
                $sTables .= "    - tables.$table.read" . PHP_EOL;
                $sTables .= "    - tables.$table.update" . PHP_EOL;
                $sTables .= "    - tables.$table.delete" . PHP_EOL;
            }


            $tplContent = str_replace([
                'Light_TaskScheduler',
                '    - tables.lts_task_schedule.create',
            ], [
                $originPlanet,
                $sTables,
            ], $tplContent);
            FileSystemTool::mkfile($path, $tplContent);
        }


        //--------------------------------------------
        // HOOK PERMISSIONS ONLY IF THE TABLE(S) EXIST
        //--------------------------------------------
        if ($tables) {
            reset($tables);
            $firstTable = current($tables);


            /**
             * @var $dbInfo LightDatabaseInfoService
             */
            $dbInfo = $this->container->get("database_info");
            if (true === $dbInfo->hasTable($firstTable)) {
                if (true === $dbInfo->hasTable("lud_permission_group_has_permission")) {


                    $this->infoMessage("Adding $originPlanet permissions to the Light_Kit_Admin.admin and Light_Kit_Admin.user permission groups.");

                    /**
                     * @var $lud LightUserDatabaseService
                     */
                    $userDb = $this->container->get("user_database");
                    LightLingStandardServiceHelper::bindStandardLightPermissionsToLkaPermissionGroups($userDb, $originPlanet);


                } else {
                    $this->errorMessage("The lud_permission_group_has_permission table was not found. Please install the \"Light_UserDatabase\" plugin first.");
                }

            } else {
                $this->importantMessage("The $firstTable table was not found in the database. We recommend that you create it first, then generate the \"standard permissions\", then re-execute this process again to complete the \"hook permissions\" task. 
                        Tip: you can generate the tables easily using the \"Synchronize db task\" on the $originPlanet planet. Then we also have a process to generate the \"standard permissions\".");
            }


        } else {
            $this->infoMessage("No tables detected, skip hooking permissions.");
        }


        //--------------------------------------------
        // ADDING SERVICE CONFIG FILE HOOKS
        //--------------------------------------------
        $serviceConfigFile = $appDir . "/config/services/$planet.byml";
        $symbolicServiceConfigFile = $this->getSymbolicPath($serviceConfigFile);

        if (true === $useMenu) {
            $this->addServiceConfigHook('bmenu', [
                'method' => 'addDefaultItemByFile',
                'args' => [
                    'menu_type' => 'admin_main_menu',
                    'file' => "\${app_dir}/config/data/$planet/bmenu/generated/$serviceName.admin_mainmenu_1.byml",
                ],
            ], [
                'menu_type' => 'admin_main_menu',
            ]);
        }



        if(true === $useController){
            $this->addServiceConfigHook('controller_hub', [
                'method' => 'registerHandler',
                'args' => [
                    'plugin' => $planet,
                    'handler' => [
                        'instance' => "Ling\\$planet\ControllerHub\Generated\\${tightName}ControllerHubHandler",
                        'methods' => [
                            'setContainer' => [
                                'container' => '@container()',
                            ],
                        ],
                    ],
                ],
            ], [
                'plugin' => $planet,
            ]);
        }




        if (true === $useForm) {
            $this->addServiceConfigHook('chloroform_extension', [
                'method' => 'registerTableListConfigurationHandler',
                'args' => [
                    'plugin' => $planet,
                    'handler' => [
                        'instance' => "Ling\Light_Kit_Admin\ChloroformExtension\LightKitAdminTableListConfigurationHandler",
                        'methods' => [
                            'setConfigurationFile' => [
                                'files' => [
                                    "\${app_dir}/config/data/$planet/Light_ChloroformExtension/generated/$serviceName.table_list.byml",
                                ],
                            ],
                        ],
                    ],
                ],
            ], [
                'plugin' => $planet,
            ]);

            $this->addServiceConfigHook('crud', [
                'method' => 'registerHandler',
                'args' => [
                    'pluginId' => $planet,
                    'handler' => [
                        'instance' => "Ling\Light_Kit_Admin\Crud\CrudRequestHandler\LightKitAdminCrudRequestHandler",
                    ],
                ],
            ], [
                'pluginId' => $planet,
            ]);


            $this->addServiceConfigHook('kit_admin', [
                'method' => 'registerPlugin',
                'args' => [
                    'pluginName' => $planet,
                    'plugin' => [
                        'instance' => "Ling\\$planet\\LightKitAdminPlugin\\Generated\\${tightName}LkaPlugin",
                        'methods' => [
                            'setOptionsFile' => [
                                'file' => "\${app_dir}/config/data/$planet/Light_Kit_Admin/lka-options.generated.byml",
                            ],
                        ],
                    ],
                ],
            ], [
                'pluginName' => $planet,
            ]);


            $this->addServiceConfigHook('realform', [
                'method' => 'registerFormHandler',
                'args' => [
                    'plugin' => $planet,
                    'handler' => [
                        'instance' => "Ling\Light_Kit_Admin\Realform\Handler\LightKitAdminRealformHandler",
                        'methods' => [
                            'setConfDir' => [
                                'dir' => "\${app_dir}/config/data/$planet/Light_Realform",
                            ],
                        ],
                    ],
                ],
            ], [
                'plugin' => $planet,
            ]);

        }


        if (true === $useList) {
            $this->addServiceConfigHook('realist', [
                'method' => 'registerListRenderer',
                'args' => [
                    'identifier' => $planet,
                    'renderer' => [
                        'instance' => "Ling\Light_Kit_Admin\Realist\Rendering\LightKitAdminRealistListRenderer",
                    ],
                ],
            ], [
                'identifier' => $planet,
            ]);

            $this->addServiceConfigHook('realist', [
                'method' => 'registerRealistRowsRenderer',
                'args' => [
                    'identifier' => $planet,
                    'renderer' => [
                        'instance' => "Ling\Light_Kit_Admin\Realist\Rendering\LightKitAdminRealistRowsRenderer",
                    ],
                ],
            ], [
                'identifier' => $planet,
            ]);


            $this->addServiceConfigHook('realist', [
                'method' => 'registerActionHandler',
                'args' => [
                    'renderer' => [
                        'instance' => "Ling\Light_Kit_Admin\Realist\ActionHandler\LightKitAdminRealistActionHandler",
                    ],
                ],
            ]);


            $this->addServiceConfigHook('realist', [
                'method' => 'registerListActionHandler',
                'args' => [
                    'plugin' => $planet,
                    'renderer' => [
                        'instance' => "Ling\Light_Kit_Admin\Realist\ListActionHandler\LightKitAdminListActionHandler",
                    ],
                ],
            ], [
                'plugin' => $planet,
            ]);


            $this->addServiceConfigHook('realist', [
                'method' => 'registerListGeneralActionHandler',
                'args' => [
                    'plugin' => $planet,
                    'renderer' => [
                        'instance' => "Ling\Light_Kit_Admin\Realist\ListGeneralActionHandler\LightKitAdminListGeneralActionHandler",
                    ],
                ],
            ], [
                'plugin' => $planet,
            ]);
        }


        $this->addServiceConfigHook('micro_permission', [
            'method' => 'registerMicroPermissionsByProfile',
            'args' => [
                'file' => "\${app_dir}/config/data/$planet/Light_MicroPermission/$serviceName.profile.generated.byml",
            ],
        ], [
            'file' => "\${app_dir}/config/data/$planet/Light_MicroPermission/$serviceName.profile.generated.byml",
        ]);


    }
}