<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\Bat\CaseTool;
use Ling\Light\ServiceContainer\LightServiceContainerAwareInterface;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_DeveloperWizard\Util\ServiceManagerUtil;


/**
 * The AddServiceLingBreeze2GetFactoryMethodProcess class.
 */
class AddServiceLingBreeze2GetFactoryMethodProcess extends LightDeveloperWizardBaseProcess implements LightServiceContainerAwareInterface
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
        $this->setName("create-service-get-factory-method");
        $this->setLabel("Adds a (LingBreeze 2) getFactory method to the service if it doesn't exist.");
        $this->setLearnMore('See the <a href="https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#getfactory-method">getFactory method convention</a> for more details.');
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
        $galaxyName = $this->getContextVar("galaxy");
        $hasMethod = $util->serviceHasMethod("getFactory");
//        $serviceName = $util->getServiceName();
        $planetName = $this->getContextVar("planet");


        //--------------------------------------------
        // UPDATE SERVICE CLASS
        //--------------------------------------------
        if (true === $hasMethod) {
            $this->infoMessage("The service class for planet $planetIdentifier already has getFactory method.");

        } else {
            $this->infoMessage("Adding the getFactory method for planet $planetIdentifier's service class.");


            $factoryName = 'Custom' . CaseTool::toFlexiblePascal($planetName) . 'ApiFactory';
            $tpl = __DIR__ . "/../../assets/method-templates/ServiceClass/getFactory.php.txt";

            $accessorMethod = file_get_contents($tpl);
            $accessorMethod = str_replace([
                "CustomLightTaskSchedulerApiFactory",
            ], [
                $factoryName,
            ], $accessorMethod);


            $tpl = __DIR__ . "/../../assets/property-templates/service-factory.txt";
            $factoryProp = file_get_contents($tpl);
            $factoryProp = str_replace('CustomLightTaskSchedulerApiFactory', $factoryName, $factoryProp);


            $useStatementClass = $galaxyName . "\\" . $planetName . '\\Api\\Custom\\' . $factoryName;

            $util->addPropertyByTemplate("factory", $factoryProp, [
                'accessors' => $accessorMethod,
                'accessorsAfter' => '__construct',
                'constructorInit' => '        $this->factory = null;' . PHP_EOL,
                'useStatements' => [
                    'use ' . $useStatementClass . ";" . PHP_EOL,
                ],
                'onError' => false,
                'process' => $this,
            ]);
        }
    }

}