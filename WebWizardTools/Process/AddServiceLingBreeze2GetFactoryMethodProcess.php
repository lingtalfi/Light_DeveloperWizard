<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\Bat\CaseTool;
use Ling\ClassCooker\FryingPan\Ingredient\BasicConstructorVariableInitIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\MethodIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\PropertyIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\UseStatementIngredient;
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
        $galaxyName = $this->getContextVar("galaxy");
        $planetName = $this->getContextVar("planet");


        //--------------------------------------------
        // UPDATE SERVICE CLASS
        //--------------------------------------------
        $factoryName = 'Custom' . CaseTool::toFlexiblePascal($planetName) . 'ApiFactory';
        $useStatementClass = $galaxyName . "\\" . $planetName . '\\Api\\Custom\\' . $factoryName;

        $pan = $this->getFryingPanForService($util->getBasicServiceClassPath());

        $this->addServiceContainer($pan);


        $pan->addIngredient(UseStatementIngredient::create()->setValue($useStatementClass));


        $pan->addIngredient(PropertyIngredient::create()->setValue("factory", [
            'template' => '
    /**
     * This property holds the factory for this instance.
     * @var ' . $factoryName . '
     */
    protected $factory;
    
',
        ]));


        $pan->addIngredient(BasicConstructorVariableInitIngredient::create()->setValue('factory', [
            'template' => str_repeat(' ', 8) . '$this->factory = null;        
',
        ]));


        $pan->addIngredient(MethodIngredient::create()->setValue("getFactory", [
            'template' => '
    /**
     * Returns the factory for this plugin\'s api.
     *
     * @return ' . $factoryName . '
     */
    public function getFactory(): ' . $factoryName . '
    {
        if (null === $this->factory) {
            $this->factory = new ' . $factoryName . '();
            $this->factory->setContainer($this->container);
            $this->factory->setPdoWrapper($this->container->get("database"));
        }
        return $this->factory;
    }
    
',
        ]));


        $pan->cook();
    }

}