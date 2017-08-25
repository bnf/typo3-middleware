# TYPO3 Middleware

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

