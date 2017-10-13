# Sludio/HelperBundle

[![Latest Stable Version](https://poser.pugx.org/sludio/helper-bundle/v/stable)](https://packagist.org/packages/sludio/helper-bundle) [![Latest Unstable Version](https://poser.pugx.org/sludio/helper-bundle/v/unstable)](https://packagist.org/packages/sludio/helper-bundle) [![License](https://poser.pugx.org/sludio/helper-bundle/license)](https://packagist.org/packages/sludio/helper-bundle) [![Total Downloads](https://poser.pugx.org/sludio/helper-bundle/downloads)](https://packagist.org/packages/sludio/helper-bundle) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/build-status/master)

## Installation ##
Add the `sludio/helper-bundle` package to your `require` section in the `composer.json` file.

``` bash
$ composer require sludio/helper-bundle dev-master
```

Add the SludioHelperBundle to your application's kernel. Preferably after bundles required by this bundle but before Doctrine bundle:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Sludio\HelperBundle\SludioHelperBundle(),
        // ...
    );
    ...
}
```

## Usage ##

Configure the `sludio_helper` client(s) in your `config.yml`:
``` yaml
sludio_helper:
    extensions:
        captcha:
            enabled: true|false
            clients:
                ...
        oauth:
            enabled: true|false
            clients:
                ...
            custom_providers:
                ...
        openid:
            enabled: true|false
            clients:
                ...
        openidconnect:
            enabled: true|false
            clients:
                ...
        pagination:
            enabled: true|false
            behaviour:
                - small: 7
        position:
            enabled: true|false
            field:
                default: positon
                entities:
                    - AppBundle
        script:
            enabled: true|false
            short_functions: true|false
        translatable:
            enabled: true|false
            locales:
                - en
                - lv
            default_locale: en
            template: SludioHelperBundle:Translatable:translations.html.twig
            table: sludio_helper_translation
            manager: default
    other:
        logger:
            class: Sludio\HelperBundle\Logger\SludioLogger
        redis:
            translation: session
        entity:
            manager: default
        locale: en
```

