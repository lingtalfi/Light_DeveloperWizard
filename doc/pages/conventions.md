Conventions
===============
2020-07-09




Here are some conventions used by the developer wizard.


- [Basic service](#basic-service)



Basic service
------------
2020-07-09



It's composed of:
 
- a service class
- an exception class
- a config file
 
 

The path to the service class is: 

- ${planetDir}/Service/${tightPlanetName}Service.php


The path to the exception class is:

- ${planetDir}/Exception/${tightPlanetName}Exception.php


The path to the config file is:

- ${appDir}/config/services/${serviceName}.byml




With:

- planetDir, the path to the planet directory
- tightPlanetName, the planet name, with underscores removed
- appDir, the absolute path to the application directory
- serviceName, the name of the service. Since we're using [Light](https://github.com/lingtalfi/Light),
    it's derived from the planet name, we basically remove the "Light_" prefix, and we take the remaining part,
    set it to a
    [humanized](https://github.com/lingtalfi/ConventionGuy/blob/master/nomenclature.stringCases.eng.md#humanflatcase)
    [snake case](https://github.com/lingtalfi/ConventionGuy/blob/master/nomenclature.stringCases.eng.md#snakecase)
    with the underscore as the separator instead of the space, and that's our service name.



The [bsr-0](https://github.com/lingtalfi/BumbleBee/blob/master/Autoload/convention.bsr0.eng.md) class naming convention is used.


The generated class has the following:


### properties

- protected container
- protected options 

### methods

- public setContainer
- public setOptions
- private error (throws the exception "${planetName}/Exception/${tightPlanetName}Exception" )




See more details in the [Light](https://github.com/lingtalfi/Light) documentation.





