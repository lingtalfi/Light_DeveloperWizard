<?php


namespace Ling\Light_DeveloperWizard\Util;


use Ling\Bat\CaseTool;
use Ling\Light\ServiceContainer\LightServiceContainerInterface;
use Ling\Light_DeveloperWizard\Exception\LightDeveloperWizardException;
use Ling\UniverseTools\PlanetTool;

/**
 * The ServiceManagerUtil class.
 */
class ServiceManagerUtil
{


    /**
     * This property holds the galaxy name for this instance.
     * @var string
     */
    protected $galaxy;

    /**
     * This property holds the planet name for this instance.
     * @var string
     */
    protected $planet;


    /**
     * This property holds the container for this instance.
     * @var LightServiceContainerInterface
     */
    protected $container;


    /**
     * Builds the ServiceManagerUtil instance.
     */
    public function __construct()
    {
        $this->galaxy = null;
        $this->planet = null;
        $this->container = null;
    }


    /**
     * Sets the planet and galaxy for this instance.
     *
     * @param string $planet
     * @param string|null $galaxy
     */
    public function setPlanet(string $planet, string $galaxy = null)
    {
        if (null === $galaxy) {
            $galaxy = 'Ling';
        }
        $this->planet = $planet;
        $this->galaxy = $galaxy;
    }

    /**
     * Sets the container.
     *
     * @param LightServiceContainerInterface $container
     */
    public function setContainer(LightServiceContainerInterface $container)
    {
        $this->container = $container;
    }





    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Returns the galaxy of this instance.
     *
     * @return string
     */
    public function getGalaxyName(): string
    {
        return $this->galaxy;
    }

    /**
     * Returns the planet of this instance.
     *
     * @return string
     */
    public function getPlanetName(): string
    {
        return $this->planet;
    }


    /**
     * Returns the @page(planet identifier).
     *
     * @return string
     */
    public function getPlanetIdentifier(): string
    {
        return "$this->galaxy/$this->planet";
    }


    /**
     * Returns the service name.
     *
     * @return string
     */
    public function getServiceName(): string
    {
        if (0 !== strpos($this->planet, 'Light_')) {
            $this->error("This method is only available for Light planets, $this->planet was given.");
        }
        $rest = substr($this->planet, 6);
        $rest = CaseTool::toHumanFlatCase($rest);
        $rest = CaseTool::toSnake($rest);
        return $rest;
    }


    /**
     * Returns the absolute path to the @page(basic service) class path.
     * @return string
     */
    public function getBasicServiceClassPath(): string
    {
        $tightName = PlanetTool::getTightPlanetName($this->planet);
        return $this->container->getApplicationDir() . "/universe/$this->galaxy/$this->planet/Service/$tightName" . "Service.php";
    }

    /**
     * Returns the absolute path to the @page(basic service) exception path.
     * @return string
     */
    public function getBasicServiceExceptionPath(): string
    {
        $tightName = PlanetTool::getTightPlanetName($this->planet);
        return $this->container->getApplicationDir() . "/universe/$this->galaxy/$this->planet/Exception/$tightName" . "Exception.php";
    }


    /**
     * Returns the absolute path to the @page(basic service) config path.
     * @return string
     */
    public function getBasicServiceConfigPath(): string
    {
        return $this->container->getApplicationDir() . "/config/services/$this->planet.byml";
    }


    /**
     * Returns the @page(tight planet name).
     *
     * @return string
     */
    public function getTightPlanetName(): string
    {
        return PlanetTool::getTightPlanetName($this->planet);
    }


    /**
     * Returns whether there is a @page(basic service) class file for the planet.
     * @return bool
     */
    public function hasBasicServiceClassFile(): bool
    {
        $file = $this->getBasicServiceClassPath();
        return file_exists($file);
    }

    /**
     * Returns whether there is a @page(basic service) exception file for the planet.
     * @return bool
     */
    public function hasBasicServiceExceptionFile(): bool
    {
        $file = $this->getBasicServiceExceptionPath();
        return file_exists($file);
    }

    /**
     * Returns whether there is a @page(basic service) config file for the planet.
     * @return bool
     */
    public function hasBasicServiceConfigFile(): bool
    {
        $file = $this->getBasicServiceConfigPath();
        return file_exists($file);
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Throws an exception.
     * @param string $msg
     * @throws \Exception
     */
    private function error(string $msg)
    {
        throw new LightDeveloperWizardException($msg);
    }
}