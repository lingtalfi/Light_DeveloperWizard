<?php


namespace Ling\Light_DeveloperWizard\WebWizardTools\Process\Light_Kit_Admin;


use Ling\Light\Helper\LightNamesAndPathHelper;
use Ling\Light_DeveloperWizard\WebWizardTools\Process\Generators\GenerateLkaPluginProcess;

/**
 * The ExecuteLkaGeneratorProcess class.
 */
class ExecuteLkaGeneratorProcess extends GenerateLkaPluginProcess
{


    /**
     * This property holds the configFiles for this instance.
     * It contains the lka generator config files found for the planet in this order:
     *
     * - $appDir/config/data/$planetName/Light_Kit_Admin_Generator/$serviceName.byml
     * - $appDir/config/data/$planetName/Light_Kit_Admin_Generator/$serviceName.generated.byml
     *
     * If a config file is not found, it's value is set to false.
     *
     *
     *
     *
     * @var array
     */
    protected $configFiles;

    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName("execute-lka-generator-config");
        $this->setLabel("Executes lka generator config file.");
        $this->setLearnMoreByHash('execute-the-lka-generator-config-file');
        $this->configFiles = null;
        $this->checkCreateFileExists = false;
    }


    /**
     * @overrides
     */
    public function prepare()
    {
        parent::prepare();
        if (true === empty($this->getDisabledReason())) {

            $planet = $this->getContextVar("planet");

            if (0 !== strpos($planet, "Light_Kit_Admin_")) {
                $this->setDisabledReason("The planet name must start with Light_Kit_Admin_");
            } else {
                $appDir = $this->container->getApplicationDir();
                $serviceName = LightNamesAndPathHelper::getServiceName($planet);

                $lkaGenConfigPath = $appDir . "/config/data/$planet/Light_Kit_Admin_Generator/$serviceName.byml";
                $lkaGenConfigPath2 = $appDir . "/config/data/$planet/Light_Kit_Admin_Generator/$serviceName.generated.byml";


                if (false === file_exists($lkaGenConfigPath)) {
                    $lkaGenConfigPath = false;
                }
                if (false === file_exists($lkaGenConfigPath2)) {
                    $lkaGenConfigPath2 = false;
                }

                $this->configFiles = [
                    $lkaGenConfigPath,
                    $lkaGenConfigPath2,
                ];

                if (false === $lkaGenConfigPath && false === $lkaGenConfigPath2) {
                    $this->setDisabledReason('No lka generator config file found. See more details in the task details.');
                }
            }
        }
    }


    /**
     * @implementation
     */
    protected function doExecute(array $options = [])
    {
        //--------------------------------------------
        // LKA GENERATOR CONFIG
        //--------------------------------------------
        foreach ($this->configFiles as $configFile) {
            if (false !== $configFile) {
                $this->executeGeneratorConfigFile($configFile);
                break;
            }
        }
    }
}