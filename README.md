# Sludio/HelperBundle

[![Latest Stable Version](https://poser.pugx.org/sludio/helper-bundle/v/stable)](https://packagist.org/packages/sludio/helper-bundle) [![Latest Unstable Version](https://poser.pugx.org/sludio/helper-bundle/v/unstable)](https://packagist.org/packages/sludio/helper-bundle) [![License](https://poser.pugx.org/sludio/helper-bundle/license)](https://packagist.org/packages/sludio/helper-bundle) [![Total Downloads](https://poser.pugx.org/sludio/helper-bundle/downloads)](https://packagist.org/packages/sludio/helper-bundle) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ozo2003/HelperBundle/build-status/master)

## Installation ##
Add the `sludio/helper-bundle` package to your `require` section in the `composer.json` file.

``` bash
$ composer require sludio/helper-bundle dev-master
```

Add the SludioHelperBundle to your application's kernel. Preferably after bundles overridden by this bundle:

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
        guzzle:
            profiler:
                enabled: true|false
                max_body_size: 0x10000
            logger:
                enabled: true|false
                service: ~
                format: clf|debug|short
                level: emergency|alert|critical|error|warning|notice|info|debug
            cache:
                enabled: true|false
                adapter: sludio_helper.guzzle.cache_adapter.redis
            clients:
                acme:
                    class: GuzzleHttp\Client
                    lazy: true|false
                    config: ~
                    middleware: ~
                    alias: ~
            mock:
                enabled: true|false
                storage_path: ~
                mode: replay
                request_headers_blacklist: ~
                response_headers_blacklist: ~
        lexik:
            enabled: true|false
            default_domain: messages
            empty_prefixes:
                - '__'
                - 'new_'
                - ''
            default_selections:
                non_translated_only: true|false
            editable:
                type: textarea
                emptytext: Empty
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
            guzzle: session
        entity:
            manager: default
        locale: en
```

