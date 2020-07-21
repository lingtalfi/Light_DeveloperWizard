Task details
=============
2020-07-09 -> 2020-07-20



- [Add logDebug method](#add-logdebug-method)
- [Add standard permissions](#add-standard-permissions)
- [Create service process](#create-service-process)
- [Generate breeze api](#generate-breeze-api)
- [Generate Light_Kit_Admin plugin](#generate-light_kit_admin-plugin)
- [Synchronize db](#synchronize-db)




Add logDebug method
-----------
2020-07-20


This task implements the [logDebug method convention](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#logdebug-method).


- add a **logDebug** method to the service class if it doesn't have it already.

    The service class must be a [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service)
    otherwiser this task won't work properly. In particular, make sure the service has the options property and setOptions method before
    you run this task.
    
- add the **useDebug** option in the service config file if it's not already defined. It uses the service's setOptions method call to achieve that    
- add the hook to the [logger service](https://github.com/lingtalfi/Light_Logger), with a channel of **$serviceName.debug**, 
    and which writes to the file **$appDir/log/$serviceName_debug.txt**    






Add standard permissions
-----------
2020-07-09

Adds [light standard permissions](https://github.com/lingtalfi/TheBar/blob/master/discussions/light-standard-permissions.md) for the given planet.




Create service process
----------
2020-07-09



We create a [basic service](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conventions.md#basic-service) structure.
The files are only created if they don't exist.




Generate breeze api
----------
2020-07-09


We create the breeze api for the given planet.
The generator used is the [LingBreezeGenerator 2](https://github.com/lingtalfi/Light_BreezeGenerator/blob/master/doc/pages/ling-breeze-generator-2.md).

All the classes will be generated in the **Api** directory at the root of your planet directory.



Generate Light_Kit_Admin plugin
----------
2020-07-09


We create a light kit admin plugin for your planet, using the [Light_Kit_Admin_Generator](https://github.com/lingtalfi/Light_Kit_Admin_Generator) and our own tools.

This only works if you have a [create file](https://github.com/lingtalfi/TheBar/blob/master/discussions/create-file.md).


If your [planet identifier](https://github.com/lingtalfi/UniverseTools/blob/master/doc/pages/nomenclature.md#planet-identifier) is **Ling/Light_MyPlanet**, and you have only one table named **mpl_bottles**, then the following will be created:


```txt
- $appDir/
----- config/
--------- data/
------------- Light_Kit_Admin_MyPlanet/
----------------- bmenu/
--------------------- generated/
------------------------- kit_admin_my_planet.admin_mainmenu_1.byml
----------------- kit/
--------------------- zeroadmin/
------------------------- generated/
----------------------------- mpl_bottles_form.byml
----------------------------- mpl_bottles_list.byml
----------------- Light_ChloroformExtension/
--------------------- generated/
------------------------- kit_admin_my_planet.table_list.byml
----------------- Light_Kit_Admin/
--------------------- lka-options.generated.byml
----------------- Light_Kit_Admin_Generator/
--------------------- kit_admin_my_planet.generated.byml
----------------- Light_MicroPermission/
--------------------- kit_admin_my_planet.profile.generated.byml
----------------- Light_RealForm/
--------------------- generated/
------------------------- mpl_bottles.byml
----------------- Light_Realist/
--------------------- generated/
------------------------- mpl_bottles.byml
--------- services/
------------- Light_Kit_Admin_MyPlanet.byml
----- universe/
--------- Ling/
------------- Light_Kit_Admin_MyPlanet/
----------------- Controller/
--------------------- Generated/
------------------------- Base/
----------------------------- RealGenController.php
------------------------- MplBottlesController.php
----------------- ControllerHub/
--------------------- Generated/
------------------------- LightKitAdminMyPlanetControllerHub.php
----------------- LightKitAdminPlugin/
--------------------- Generated/
------------------------- LightKitAdminMyPlanetLkaPlugin.php
```



Synchronize db 
--------
2020-07-09


We synchronize the current db with your [create file](https://github.com/lingtalfi/TheBar/blob/master/discussions/create-file.md), using the
[Light_DbSynchronizer](https://github.com/lingtalfi/Light_DbSynchronizer/) plugin under the hood.


