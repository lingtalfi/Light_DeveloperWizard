Light_DeveloperWizard
===========
2020-06-30



A tool to speed up your development with the [light framework](https://github.com/lingtalfi/Light).


This is a [Light plugin](https://github.com/lingtalfi/Light/blob/master/doc/pages/plugin.md).

This is part of the [universe framework](https://github.com/karayabin/universe-snapshot).


Install
==========
Using the [uni](https://github.com/lingtalfi/universe-naive-importer) command.
```bash
uni import Ling/Light_DeveloperWizard
```

Or just download it and place it where you want otherwise.






Summary
===========
- [Light_DeveloperWizard api](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/api/Ling/Light_DeveloperWizard.md) (generated with [DocTools](https://github.com/lingtalfi/DocTools))
- [Services](#services)
- Pages
    - [Conception notes](https://github.com/lingtalfi/Light_DeveloperWizard/blob/master/doc/pages/conception-notes.md)






Services
=========


Here is an example of the service configuration:

```yaml
developer_wizard:
    instance: Ling\Light_DeveloperWizard\Service\LightDeveloperWizardService
    methods:
        setContainer:
            container: @container()



```



History Log
=============

- 1.0.0 -- 2020-06-30

    - initial commit