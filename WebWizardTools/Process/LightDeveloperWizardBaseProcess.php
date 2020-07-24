<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\Bat\BDotTool;
use Ling\ClassCooker\FryingPan\FryingPan;
use Ling\ClassCooker\FryingPan\Ingredient\BasicConstructorVariableInitIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\MethodIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\PropertyIngredient;
use Ling\ClassCooker\FryingPan\Ingredient\UseStatementIngredient;
use Ling\Light_DeveloperWizard\Exception\LightDeveloperWizardException;
use Ling\Light_DeveloperWizard\Helper\DeveloperWizardGenericHelper;
use Ling\Light_DeveloperWizard\Tool\DeveloperWizardFileTool;
use Ling\Light_DeveloperWizard\Util\ServiceManagerUtil;
use Ling\SqlWizard\Util\MysqlStructureReader;
use Ling\WebWizardTools\Process\WebWizardToolsProcess;


/**
 * The LightDeveloperWizardBaseProcess class.
 */
abstract class LightDeveloperWizardBaseProcess extends WebWizardToolsProcess
{


    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Returns the given absolute path, with the application directory replaced by a symbol if found.
     * If not, the path is returned as is.
     *
     *
     * For instance: [app]/my/image.png
     *
     * @param string $path
     * @return string
     */
    protected function getSymbolicPath(string $path): string
    {
        $appDir = $this->getContextVar("container")->getApplicationDir();
        return DeveloperWizardGenericHelper::getSymbolicPath($path, $appDir);
    }


    /**
     * Returns the table prefix from either the preferences (if found), or guessed from the given createFile otherwise.
     *
     * @param string $planetDir
     * @param string $createFile
     * @return string
     * @throws \Exception
     */
    protected function getTablePrefix(string $planetDir, string $createFile): string
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


    /**
     * Returns a FryingPan instance configured to work with the given file.
     * @param string $file
     * @return FryingPan
     */
    protected function getFryingPanForService(string $file)
    {
        $pan = new FryingPan();
        $pan->setFile($file);
        $pan->setOptions([
            "loggerCallable" => function (string $msg, string $type) {
                switch ($type) {
                    case "add":
                        $this->infoMessage($msg);
                        break;
                    case "skip":
                        $this->traceMessage($msg);
                        break;
                    case "error":
                        $this->errorMessage($msg);
                        break;
                    default:
                        $this->error("Unknown message type: $type.");
                        break;
                }
            }
        ]);

        return $pan;
    }


    /**
     * Adds incrementally the options property, the options variable init, and the setOptions method to the service container class.
     *
     * Add the moment, this only works properly if the setContainer method and the container property are already there.
     * You can add those using the addServiceContainer method.
     *
     *
     *
     * @param FryingPan $pan
     * @param string $planetName
     */
    protected function addServiceOptions(FryingPan $pan, string $planetName)
    {
        $pan->addIngredient(PropertyIngredient::create()->setValue("options", [
            'template' => '
    /**
     * This property holds the options for this instance.
     *
     * Available options are:
     *
     *
     *
     * See the @page(' . $planetName . ' conception notes) for more details.
     *
     *
     * @var array
     */
    protected $options;
    
',
            'afterProperty' => 'container',
        ]));


        $pan->addIngredient(BasicConstructorVariableInitIngredient::create()->setValue('options', [
            'template' => str_repeat(' ', 8) . '$this->options = [];        
',
        ]));

        $pan->addIngredient(MethodIngredient::create()->setValue("setOptions", [
            'template' => '
    /**
     * Sets the options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
    
',
            "afterMethod" => 'setContainer',
        ]));


    }

    /**
     * Adds incrementally the container property, the container variable init, the setContainer method, and the necessary use statements, to the service container class.
     *
     *
     * @param FryingPan $pan
     */
    protected function addServiceContainer(FryingPan $pan)
    {

        $pan->addIngredient(UseStatementIngredient::create()->setValue('Ling\Light\ServiceContainer\LightServiceContainerInterface'));


        $pan->addIngredient(PropertyIngredient::create()->setValue("container", [
            'template' => '         
    /**
     * This property holds the container for this instance.
     * @var LightServiceContainerInterface
     */
    protected $container;
    
',
            'top' => true,
        ]));


        $pan->addIngredient(BasicConstructorVariableInitIngredient::create()->setValue('container', [
            'template' => str_repeat(' ', 8) . '$this->container = null;        
',
        ]));


        $pan->addIngredient(MethodIngredient::create()->setValue("setContainer", [
            'template' => '
    /**
     * Sets the container.
     *
     * @param LightServiceContainerInterface $container
     */
    public function setContainer(LightServiceContainerInterface $container)
    {
        $this->container = $container;
    }
    
',
            "afterMethod" => '__construct',
        ]));

    }


    /**
     * Throws an exception.
     *
     * @param string $msg
     * @throws LightDeveloperWizardException
     */
    protected function error(string $msg)
    {
        throw new LightDeveloperWizardException($msg);
    }
}