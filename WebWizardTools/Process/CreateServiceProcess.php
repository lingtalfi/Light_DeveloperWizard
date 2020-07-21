<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\Bat\FileSystemTool;
use Ling\Light\ServiceContainer\LightServiceContainerAwareInterface;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_DeveloperWizard\Util\ServiceManagerUtil;


/**
 * The CreateServiceProcess class.
 */
class CreateServiceProcess extends LightDeveloperWizardBaseProcess implements LightServiceContainerAwareInterface
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
        $this->setName("create-basic-service");
        $this->setLabel("Create a basic service.");
        $this->setLearnMore('See the <a href="https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service">basic service definition</a>.');
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
        $hasClassFile = $util->hasBasicServiceClassFile();
        $hasExceptionFile = $util->hasBasicServiceExceptionFile();
        $hasServiceConfigFile = $util->hasBasicServiceConfigFile();


        //--------------------------------------------
        // SERVICE CLASS
        //--------------------------------------------
        if (true === $hasClassFile) {
            $this->infoMessage("The planet $planetIdentifier already has a service class.");


            if (false === $util->serviceHasProperty("options")) {

                $this->infoMessage("Adding options property to the service class for The planet $planetIdentifier.");

                $serviceName = $util->getServiceName();
                $tpl = __DIR__ . "/../../assets/property-templates/service-options.txt";
                $tpl2 = __DIR__ . "/../../assets/method-templates/ServiceClass/setOptions.txt";
                $str = file_get_contents($tpl);
                $str = str_replace('Light_XXX', $serviceName, $str);
                $sGetter = file_get_contents($tpl2);

                $util->addPropertyByTemplate("options", $str, [
                    'constructorInit' => '        $this->options = [];' . PHP_EOL,
                    'accessors' => $sGetter,
                    'accessorsAfter' => '__construct',
                ]);
            }


        } else {

            $this->infoMessage("Creating <a href=\"https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service\">basic service class</a> for planet $planetIdentifier.");
            $tpl = __DIR__ . "/../../assets/class-templates/Service/BasicServiceClass.phptpl";
            $planet = $util->getPlanetName();
            $tightName = $util->getTightPlanetName();


            $content = file_get_contents($tpl);
            $content = str_replace([
                "Light_TaskScheduler",
                "LightTaskScheduler",
            ], [
                $planet,
                $tightName,
            ], $content);
            $dstPath = $util->getBasicServiceClassPath();
            FileSystemTool::mkfile($dstPath, $content);
        }


        //--------------------------------------------
        // EXCEPTION CLASS
        //--------------------------------------------
        if (true === $hasExceptionFile) {
            $this->infoMessage("The planet $planetIdentifier already has an exception class.");

        } else {
            $this->infoMessage("Creating <a href=\"https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service\">basic service exception</a> for planet $planetIdentifier.");
            $tpl = __DIR__ . "/../../assets/class-templates/Exception/BasicException.phptpl";

            $planet = $util->getPlanetName();
            $tightName = $util->getTightPlanetName();


            $content = file_get_contents($tpl);
            $content = str_replace([
                "Light_TaskScheduler",
                "LightTaskScheduler",
            ], [
                $planet,
                $tightName,
            ], $content);
            $dstPath = $util->getBasicServiceExceptionPath();
            FileSystemTool::mkfile($dstPath, $content);
        }

        //--------------------------------------------
        // SERVICE CONFIG FILE
        //--------------------------------------------
        if (true === $hasServiceConfigFile) {
            $this->infoMessage("The planet $planetIdentifier already has a service config file.");

        } else {
            $dstPath = $util->getBasicServiceConfigPath();

            $this->infoMessage("Creating <a href=\"https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service\">basic service config file</a> for planet $planetIdentifier.");
            $tpl = __DIR__ . "/../../assets/conf-template/services/basic-service.byml";

            $planet = $util->getPlanetName();
            $tightName = $util->getTightPlanetName();
            $serviceName = $util->getServiceName();


            $content = file_get_contents($tpl);
            $content = str_replace([
                "task_scheduler",
                "Light_TaskScheduler",
                "LightTaskScheduler",
            ], [
                $serviceName,
                $planet,
                $tightName,
            ], $content);
            FileSystemTool::mkfile($dstPath, $content);
        }


    }

}