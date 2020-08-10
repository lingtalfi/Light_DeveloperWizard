<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process\Light_Kit_Admin;


use Ling\Light_DeveloperWizard\WebWizardTools\Process\Generators\GenerateLkaPluginProcess;

/**
 * The CreateLkaPlanetProcess class.
 *
 */
class CreateLkaPlanetProcess extends GenerateLkaPluginProcess
{


    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName("create-lka-planet");
        $this->setLabel("Creates the lka planet.");
        $this->setLearnMoreByHash('create-lka-planet');
    }


//    /**
//     * @overrides
//     */
//    public function prepare()
//    {
//        // don't call the parent.
//        $util = $this->container->get("developer_wizard")->getServiceManagerUtil();
//        $planetName = $this->getContextVar("planet");
//        $galaxyName = $this->getContextVar("galaxy");
//        $util->setPlanet($planetName, $galaxyName);
//        $util->setContainer($this->container);
//        $this->util = $util;
//
//        if (0 !== strpos($planetName, "Light_")) {
//            $this->setDisabledReason("The planet name must start with the \"Light_\" prefix.");
//        }
//
//    }

    /**
     * @implementation
     */
    protected function doExecute(array $options = [])
    {

        //--------------------------------------------
        // PLANET
        //--------------------------------------------
        $planet = $this->getContextVar("planet");
        $galaxy = $this->getContextVar("galaxy");
        $newPlanetName = $this->getLkaPlanetNameByPlanet($planet);


        $this->generateLkaPlanet([
            'galaxy' => $galaxy,
            'planet' => $newPlanetName,
        ]);


    }
}