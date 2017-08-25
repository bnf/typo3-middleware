# TYPO3 Middleware

## ExtbaseMiddleware

Middleware that prepares an extbase environment – by design without `$GLOBALS['TSFE']`.
If you need `$GLOBALS['TSFE']` have a look at the `TypoScriptRenderingMiddleware`.

```sh
composer require bnf/typo3-middleware bnf/slim-typo3
```

ext_localconf.php:

```php
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bnf\SlimTypo3\AppRegistry::class)
    ->push(function($app) {
        $pimple = $app->getContainer()->get('pimple');

        $pimple['objectManager'] = function ($c) {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        };
        $pimple['whateverRepository'] = function ($c) {
            return $c['objectManager']->get(\Vendor\Extension\Domain\Repository\WhateverRepository::class);
        };
        $pimple['exportService'] = function ($c) {
            return $c['objectManager']->get(\Vendor\Extension\Service\ExportService::class);
        };

        $app->get('/export-whatever[/]', function ($request, $response) {
            $objects = $this->get('whateverRepository')->findAll();
            $xml = $this->get('exportService')->whateverToXml($objects);
            $response->getBody()->write($xml->saveXML());

            return $response;
        })->add(new \Bnf\Typo3Middleware\ExtbaseMiddleware([
            'persistence' => [
                'storagePid' => 78,
		/* TypoScript is not evaluated (by design), you need to provide _all_
                 * required persistence configuration here. Include stuff here that you
                 * or your dependencies write into config.extbase.persistence */
                //\Vendor\Whathever\Domain\Model\Whatever::class => [ 'mapping' => [ 'tableName' => 'custom_table' ] ],
            ],
            /* You can provide plugin context here (will be used in the mocked UriBuilder) */
            //'extensionName' => 'Whatever',
            //'pluginName' => 'Pi1',
            //'vendorName' => 'Vendor',
        ]));
```


## TypoScriptRenderingMiddleware

```sh
composer require bnf/typo3-middleware bnf/slim-typo3 helhum/typoscript-rendering
```

ext_localconf.php:

```php
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bnf\SlimTypo3\AppRegistry::class)
    ->push(function($app) {
        /* Simple Example, no (content element) context, only call extbase plugin */
        $app->get('/some-extbase-api', function ($request, $response) {
            $handler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bnf\Typo3Middleware\TypoScriptRenderingMiddleware::class, [
                'signature' => 'extensionname_pluginname',
            ]);

            return $handler->process($request);
        });

        /* Execute the same plugin as above (now specified using 'extension' and 'plugin')
         * in the context of page '213' and with flexform settings from tt_content record '3284'.
         * Also (optionally) pass the argument 'foo' to the extbase controller.
         */
        $app->get('/some-custom-extbase-action[/{foo}]', function ($request, $response, $args) {
            $handler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bnf\Typo3Middleware\TypoScriptRenderingMiddleware::class, [
                'extension' => 'VendorName.ExtensionName',
                'plugin' => 'PluginName',
                'page' => '213',
                'record' => 'tt_content_3284',
                'arguments' => [
                    'foo' => $args['foo'] ?? null,
                ],
                'page' => '649',
            ]);

            /* Add a custom header to the request, that's returned by extbase/TYPO3 core */
            return $handler->process($request)->withHeader('Content-Type', 'application/xml');
        });
    });
```
