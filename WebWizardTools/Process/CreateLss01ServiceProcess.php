<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process;


use Ling\Bat\FileSystemTool;
use Ling\ClassCooker\FryingPan\Ingredient\ParentIngredient;
use Ling\Light\ServiceContainer\LightServiceContainerAwareInterface;


/**
 * The CreateLss01ServiceProcess class.
 */
class CreateLss01ServiceProcess extends CreateServiceProcess implements LightServiceContainerAwareInterface
{


    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName("create-lss01-service");
        $this->setLabel("Create a lss01 service.");
        $this->setLearnMore('See the <a href="https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#ling-standard-service-01">lss01 definition</a>.');
    }


    /**
     * @implementation
     */
    protected function doExecute(array $options = [])
    {


        $util = $this->util;


        $planetIdentifier = $util->getPlanetIdentifier();
        $hasClassFile = $util->hasBasicServiceClassFile();
        $galaxyName = $this->getContextVar("galaxy");


        //--------------------------------------------
        // SERVICE CLASS
        //--------------------------------------------
        if (true === $hasClassFile) {
            $this->infoMessage("The service class for planet $planetIdentifier was already created.");


            $pan = $this->getFryingPanForService($util->getBasicServiceClassPath());
            $planet = $util->getPlanetName();
            $tightName = $util->getTightPlanetName();
            $useStatementClass = "Ling\Light_LingStandardService\Service\LightLingStandardService01";
            $pan->addIngredient(ParentIngredient::create()->setValue('LightLingStandardService01', [
                'useStatement' => $useStatementClass,
            ]));

            $this->addServiceFactory($pan, $galaxyName, $planet);

            $pan->cook();


        } else {

            $this->infoMessage("Creating <a href=\"https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#ling-standard-service-01\">lss01 service class</a> for planet $planetIdentifier.");
            $tpl = __DIR__ . "/../../assets/class-templates/Service/Lss01ServiceClass.phptpl";
            $planet = $util->getPlanetName();
            $tightName = $util->getTightPlanetName();


            $content = file_get_contents($tpl);
            $content = str_replace([
                "Light_XXX",
                "LightXXX",
            ], [
                $planet,
                $tightName,
            ], $content);
            $dstPath = $util->getBasicServiceClassPath();
            FileSystemTool::mkfile($dstPath, $content);
        }


        $this->createExceptionClass();
        $this->createBasicConfigFile();

    }

}