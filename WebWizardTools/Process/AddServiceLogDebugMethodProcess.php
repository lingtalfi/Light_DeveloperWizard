<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\Light\ServiceContainer\LightServiceContainerAwareInterface;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_DeveloperWizard\Util\ServiceManagerUtil;


/**
 * The AddServiceLogDebugMethodProcess class.
 */
class AddServiceLogDebugMethodProcess extends LightDeveloperWizardBaseProcess implements LightServiceContainerAwareInterface
{


    /**
     * This property holds the container for this instance.
     * @var LightServiceContainerInterface
     */
    protected $container;

    /**
     * This property holds the util for this instance.
     * @var ServiceManagerUtil
     */
    protected $util;

    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName("create-service-log-debug-method");
        $this->setLabel("Adds a logDebug method to the service.");
        $this->setLearnMore('See the <a href="https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#logdebug-method">logDebug method convention</a> for more details.');
        $this->container = null;
        $this->util = null;
    }


    /**
     * @implementation
     */
    public function setContainer(LightServiceContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @overrides
     */
    public function prepare()
    {


        $util = $this->container->get("developer_wizard")->getServiceManagerUtil();
        $planetName = $this->getContextVar("planet");
        $galaxyName = $this->getContextVar("galaxy");
        $util->setPlanet($planetName, $galaxyName);
        $util->setContainer($this->container);
        $this->util = $util;

    }


    /**
     * @implementation
     */
    protected function doExecute(array $options = [])
    {


        $util = $this->util;


        $planetIdentifier = $util->getPlanetIdentifier();
        $hasLogDebugMethod = $util->serviceHasMethod("logDebug");


        //--------------------------------------------
        // UPDATE SERVICE CLASS
        //--------------------------------------------
        if (true === $hasLogDebugMethod) {
            $this->infoMessage("The service class for planet $planetIdentifier already has logDebug method.");

        } else {
            $this->infoMessage("Adding the <a href=\"https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#logdebug-method\">logDebug method</a> for planet $planetIdentifier's service.");
            $tpl = __DIR__ . "/../../assets/method-templates/ServiceClass/logDebug.php.txt";


            $serviceName = $util->getServiceName();

            $content = file_get_contents($tpl);
            $content = str_replace([
                "task_scheduler",
            ], [
                $serviceName,
            ], $content);
            $util->addMethod('logDebug', $content);
        }


        if (false === $util->serviceHasUseStatement('Ling\Light_Logger\LightLoggerService')) {
            $this->traceMessage("Adding use statement for LightLoggerService.");
            $useStatement = 'use Ling\Light_Logger\LightLoggerService;' . PHP_EOL;
            $util->addUseStatements($useStatement);
        } else {
            $this->traceMessage("Use statement for LightLoggerService already exists, skipping.");
        }

        if (true === $util->serviceHasProperty("options")) {
            $util->updatePropertyComment('options', function ($oldComment) {
                $newComment = $oldComment;
                if (false === strpos($newComment, '- useDebug:')) {
                    $this->traceMessage("Adding useDebug in the options property's comments.");

                    $newComment = str_replace('* Available options are:', '* Available options are:'
                        . PHP_EOL
                        . str_repeat(" ", 5) . "* - useDebug: bool, whether to enable the debug log", $newComment);
                } else {
                    $this->traceMessage("useDebug already found in the options property's comments.");
                }


                return $newComment;
            });
        } else {
            $this->importantMessage("The service class isn't a <a href=\"https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service\">basic service</a> yet, please turn the class into a <b>basic service</b> first (you can use the basic service task for that).");
        }


        //--------------------------------------------
        // UPDATE SERVICE CONFIG FILE
        //--------------------------------------------
        if (true === $util->configHasOption("useDebug")) {
            $this->infoMessage("The service config file already has the useDebug option (for planet $planetIdentifier).");
        } else {
            $serviceConfigFile = $util->getBasicServiceConfigPath();
            $this->infoMessage("Adding useDebug option to the service config file \"$serviceConfigFile\".");
            $util->addConfigOption('useDebug', false, ['inlineComment' => '         # default is false']);
        }


        if (true === $util->configHasHook("logger", [
                "with" => [
                    'method' => 'addListener',
                    'args' => [
                        "channels" => "train.debug",
                    ],
                ],
            ])) {
            $this->infoMessage("The service config file already has a hook to the logger service (for planet $planetIdentifier).");
        } else {
            $serviceConfigFile = $util->getBasicServiceConfigPath();
            $serviceName = $util->getServiceName();
            $this->infoMessage("Adding hook to the logger service in \"$serviceConfigFile\".");
            $util->addConfigHook('logger', [
                "method" => 'addListener',
                "args" => [
                    'channels' => $serviceName . '.debug',
                    'listener' => [
                        'instance' => 'Ling\Light_Logger\Listener\LightFileLoggerListener',
                        'methods' => [
                            'configure' => [
                                'options' => [
                                    "file" => '${app_dir}/log/' . $serviceName . '_debug.txt',
                                ]
                            ]
                        ],
                    ],
                ],
            ]);
        }


    }

}