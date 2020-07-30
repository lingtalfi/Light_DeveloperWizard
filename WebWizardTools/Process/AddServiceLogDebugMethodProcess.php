<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\ClassCooker\FryingPan\Ingredient\MethodIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\UseStatementIngredient;
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
        $planetName = $this->getContextVar("planet");
        $serviceName = $util->getServiceName();


        //--------------------------------------------
        // UPDATE SERVICE CLASS
        //--------------------------------------------


        $pan = $this->getFryingPanForService($util->getBasicServiceClassPath());
        $pan->addIngredient(MethodIngredient::create()->setValue("logDebug", [
            'template' => '
    /**
     * Sends a message to the debug log, only if the useDebug option is set to true.
     * If useDebug is set to false, this method does nothing.
     *
     * @param string $msg
     * @throws \Exception
     */
    public function logDebug(string $msg)
    {
        $useDebug = $this->options[\'useDebug\'] ?? false;
        if (true === $useDebug) {
            /**
             * @var $logger LightLoggerService
             */
            $logger = $this->container->get("logger");
            $logger->log($msg, "' . $serviceName . '.debug");
        }
    }
    
',
        ]));

        $pan->addIngredient(UseStatementIngredient::create()->setValue("Ling\Light_Logger\LightLoggerService"));


        $this->addServiceContainer($pan);
        $this->addServiceOptions($pan, $planetName);


        $pan->cook();


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




        // add hook
        $this->addServiceConfigHook('logger', [
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
        ], [
            "channels" => "$serviceName.debug",
        ]);


    }

}