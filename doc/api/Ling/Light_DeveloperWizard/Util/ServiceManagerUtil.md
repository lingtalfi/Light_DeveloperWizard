[Back to the Ling/Light_DeveloperWizard api](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard.md)



The ServiceManagerUtil class
================
2020-06-30 --> 2020-07-09






Introduction
============

The ServiceManagerUtil class.



Class synopsis
==============


class <span class="pl-k">ServiceManagerUtil</span>  {

- Properties
    - protected string [$galaxy](#property-galaxy) ;
    - protected string [$planet](#property-planet) ;
    - protected [Ling\Light\ServiceContainer\LightServiceContainerInterface](https://github.com/lingtalfi/Light/blob/master/doc/api/Ling/Light/ServiceContainer/LightServiceContainerInterface.md) [$container](#property-container) ;

- Methods
    - public [__construct](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/__construct.md)() : void
    - public [setPlanet](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/setPlanet.md)(string $planet, ?string $galaxy = null) : void
    - public [setContainer](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/setContainer.md)([Ling\Light\ServiceContainer\LightServiceContainerInterface](https://github.com/lingtalfi/Light/blob/master/doc/api/Ling/Light/ServiceContainer/LightServiceContainerInterface.md) $container) : void
    - public [getGalaxyName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getGalaxyName.md)() : string
    - public [getPlanetName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getPlanetName.md)() : string
    - public [getPlanetIdentifier](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getPlanetIdentifier.md)() : string
    - public [getServiceName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getServiceName.md)() : string
    - public [getBasicServiceClassPath](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getBasicServiceClassPath.md)() : string
    - public [getBasicServiceExceptionPath](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getBasicServiceExceptionPath.md)() : string
    - public [getBasicServiceConfigPath](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getBasicServiceConfigPath.md)() : string
    - public [getTightPlanetName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getTightPlanetName.md)() : string
    - public [hasBasicServiceClassFile](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/hasBasicServiceClassFile.md)() : bool
    - public [hasBasicServiceExceptionFile](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/hasBasicServiceExceptionFile.md)() : bool
    - public [hasBasicServiceConfigFile](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/hasBasicServiceConfigFile.md)() : bool
    - private [error](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/error.md)(string $msg) : void

}




Properties
=============

- <span id="property-galaxy"><b>galaxy</b></span>

    This property holds the galaxy name for this instance.
    
    

- <span id="property-planet"><b>planet</b></span>

    This property holds the planet name for this instance.
    
    

- <span id="property-container"><b>container</b></span>

    This property holds the container for this instance.
    
    



Methods
==============

- [ServiceManagerUtil::__construct](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/__construct.md) &ndash; Builds the ServiceManagerUtil instance.
- [ServiceManagerUtil::setPlanet](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/setPlanet.md) &ndash; Sets the planet and galaxy for this instance.
- [ServiceManagerUtil::setContainer](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/setContainer.md) &ndash; Sets the container.
- [ServiceManagerUtil::getGalaxyName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getGalaxyName.md) &ndash; Returns the galaxy of this instance.
- [ServiceManagerUtil::getPlanetName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getPlanetName.md) &ndash; Returns the planet of this instance.
- [ServiceManagerUtil::getPlanetIdentifier](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getPlanetIdentifier.md) &ndash; Returns the [planet identifier](https://github.com/lingtalfi/UniverseTools/blob/master/doc/pages/nomenclature.md#planet-identifier).
- [ServiceManagerUtil::getServiceName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getServiceName.md) &ndash; Returns the service name.
- [ServiceManagerUtil::getBasicServiceClassPath](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getBasicServiceClassPath.md) &ndash; Returns the absolute path to the [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) class path.
- [ServiceManagerUtil::getBasicServiceExceptionPath](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getBasicServiceExceptionPath.md) &ndash; Returns the absolute path to the [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) exception path.
- [ServiceManagerUtil::getBasicServiceConfigPath](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getBasicServiceConfigPath.md) &ndash; Returns the absolute path to the [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) config path.
- [ServiceManagerUtil::getTightPlanetName](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/getTightPlanetName.md) &ndash; Returns the [tight planet name](https://github.com/lingtalfi/UniverseTools/blob/master/doc/pages/nomenclature.md#tight-planet-name).
- [ServiceManagerUtil::hasBasicServiceClassFile](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/hasBasicServiceClassFile.md) &ndash; Returns whether there is a [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) class file for the planet.
- [ServiceManagerUtil::hasBasicServiceExceptionFile](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/hasBasicServiceExceptionFile.md) &ndash; Returns whether there is a [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) exception file for the planet.
- [ServiceManagerUtil::hasBasicServiceConfigFile](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/hasBasicServiceConfigFile.md) &ndash; Returns whether there is a [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) config file for the planet.
- [ServiceManagerUtil::error](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Util/ServiceManagerUtil/error.md) &ndash; Throws an exception.





Location
=============
Ling\Light_DeveloperWizard\Util\ServiceManagerUtil<br>
See the source code of [Ling\Light_DeveloperWizard\Util\ServiceManagerUtil](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/Util/ServiceManagerUtil.php)



SeeAlso
==============
Previous class: [DeveloperWizardFileTool](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/Tool/DeveloperWizardFileTool.md)<br>Next class: [AddStandardPermissionsProcess](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard/WebWizardTools/Process/AddStandardPermissionsProcess.md)<br>
