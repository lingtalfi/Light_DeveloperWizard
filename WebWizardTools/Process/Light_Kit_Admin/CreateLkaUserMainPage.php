<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process\Light_Kit_Admin;


use Ling\Bat\FileSystemTool;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\LightDeveloperWizardCommonProcess;

/**
 * The CreateLkaUserMainPage class.
 *
 */
class CreateLkaUserMainPage extends LightDeveloperWizardCommonProcess
{


    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName("create-lka-user-mainpage");
        $this->setLabel("Creates the lka user main page.");
        $this->setLearnMoreByHash('create-the-lka-user-main-page');
    }


    /**
     * @overrides
     */
    public function prepare()
    {
        parent::prepare();
        $planetName = $this->getContextVar("planet");

        if (0 !== strpos($planetName, "Light_Kit_Admin_")) {
            $this->setDisabledReason("The planet name must start with the \"Light_Kit_Admin_\" prefix.");
        }

    }

    /**
     * @implementation
     */
    protected function doExecute(array $options = [])
    {

        //--------------------------------------------
        // CONTROLLER
        //--------------------------------------------
        $planet = $this->getContextVar("planet");
        $galaxy = $this->getContextVar("galaxy");
        $tight = $this->util->getTightPlanetName();
        $serviceName = $this->util->getServiceName();
        $humanName = $this->util->getHumanPlanetName();


        $dst = $this->container->getApplicationDir() . "/universe/$galaxy/$planet/Controller/Custom/${tight}UserMainPageController.php";
        $symbol = $this->getSymbolicPath($dst);
        if (true === file_exists($dst)) {
            $this->importantMessage("The controller file already exist in $symbol, cannot create the controller class.");
        } else {

            $this->infoMessage("Creating the controller in \"$symbol\".");

            $tpl = __DIR__ . "/../../../assets/class-templates/Controller/UserMainPageController.phptpl";
            $content = file_get_contents($tpl);
            $content = str_replace([
                'Light_Kit_Admin_XXX',
                'LightKitAdminXXX',
                'kit_admin_xxx',
                'pluginHuman',
            ], [
                $planet,
                $tight,
                $serviceName,
                $humanName,
            ], $content);
            FileSystemTool::mkfile($dst, $content);
        }


        //--------------------------------------------
        // KIT PAGE CONFIG
        //--------------------------------------------
        $tpl = __DIR__ . "/../../../assets/conf-template/data/kit/usermainpage.byml";
        $content = file_get_contents($tpl);
        $content = str_replace([
            'pluginHuman',
        ], [
            $humanName,
        ], $content);
        $dst = $this->container->getApplicationDir() . "/config/data/$planet/kit/zeroadmin/generated/${serviceName}_mainpage.byml";
        $symbol = $this->getSymbolicPath($dst);
        $this->infoMessage("Creating the kit admin page nugget in \"$symbol\".");
        FileSystemTool::mkfile($dst, $content);


        //--------------------------------------------
        // BMENU NUGGET
        //--------------------------------------------
        $tpl = __DIR__ . "/../../../assets/conf-template/data/bmenu/user-mainpage.byml";
        $content = file_get_contents($tpl);
        $content = str_replace([
            'Light_Kit_Admin_XXX',
            'LightKitAdminXXX',
            'kit_admin_xxx',
            'pluginHuman',
        ], [
            $planet,
            $tight,
            $serviceName,
            $humanName,
        ], $content);
        $dst = $this->container->getApplicationDir() . "/config/data/$planet/bmenu/generated/$serviceName.admin_mainmenu-usermainpage.byml";
        $symbol = $this->getSymbolicPath($dst);
        $this->infoMessage("Creating the bmenu nugget in \"$symbol\".");
        FileSystemTool::mkfile($dst, $content);


        //--------------------------------------------
        // UPDATE SERVICE CONFIG FILE
        //--------------------------------------------
        $file = '${app_dir}/config/data/' . $planet . '/bmenu/generated/' . $serviceName . '.admin_mainmenu-usermainpage.byml';
        $this->addServiceConfigHook('bmenu', [
            'method' => 'addDirectItemsByFileAndParentPath',
            'args' => [
                'menu-type' => 'admin_main_menu',
                'file' => $file,
                'path' => 'lka-user',
            ],
        ], [
            'file' => $file,
            'path' => 'lka-user',
        ]);

    }
}