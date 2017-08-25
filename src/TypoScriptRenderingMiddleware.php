<?php
namespace Bnf\Typo3Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Http\RequestHandler;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

/**
 * TypoScriptRenderingMiddleware
 *
 * @author Benjamin Franzke <benjaminfranzke@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TypoScriptRenderingMiddleware
{
    /**
     * @param array
     */
    protected $configuration = [
        /* Signature is sth like 'lowercaseextension_lowercasepluginname' */
        'signature' => null,

        /* 'extension' and 'plugin' can be specified instead of 'signature' */
        'extension' => null,
        'plugin' => null,

        /* Optional controller/action/arguments overrides */
        'controller' => null,
        'action' => null,
        'arguments' => [],

        /* Provide content element context. e.g. record='tt_content_135', page='78' */
        'record' => null,
        /* TODO: We need a page id for cHash calculation.
         * Implement a non-cHash based way to authorize our request
         * for helhum/typoscript-rendering, then drop the default 'page'=>'1' setting */
        'page' => '1',

        /* 'path' is only required when using custom plugin typoscript paths */
        'path' => null,
    ];

    /**
     * @param CacheHashCalculator
     */
    protected $cacheHashCalculator = null;

    /**
     * @param RequestHandler
     */
    protected $requestHandler = null;

    /**
     * @param  array               $configuration
     * @param  CacheHashCalculator $cacheHashCalculator
     *
     * @throws \RuntimeException
     * @return void
     */
    public function __construct(
        array $configuration = [],
        CacheHashCalculator $cacheHashCalculator = null,
        RequestHandler $requestHandler = null
    ) {
        $this->mergeConfiguration($configuration);

        $this->cacheHashCalculator = $cacheHashCalculator;
        if ($this->cacheHashCalculator === null) {
            $this->cacheHashCalculator = GeneralUtility::makeInstance(CacheHashCalculator::class);
        }
        $this->requestHandler = $requestHandler;
        if ($this->requestHandler === null) {
            $this->requestHandler = GeneralUtility::makeInstance(RequestHandler::class, Bootstrap::getInstance());
        }
    }

    /**
     * @param  array $configuration
     * @throws \RuntimeException
     * @return void
     */
    protected function mergeConfiguration($configuration)
    {
        $this->configuration = $configuration + $this->configuration;

        if ($this->configuration['signature'] === null) {
            if (($this->configuration['extension'] === null || $this->configuration['plugin'] === null)) {
                throw new \RuntimeException('Invalid configuration: "extension" or "plugin" not set', 1503478917);
            } else {
                $this->configuration['signature'] = $this->buildPluginSignature($this->configuration['extension'], $this->configuration['plugin']);
            }
        }

        /* We do not support plugins that are registered as content element automatically.
         * (ie only tt_content.list.20.extension_plugin, not tt_content.extension_plugin.20)
         * Supporting that would requires TSFE, which we really do not want here.
         * https://github.com/helhum/typoscript_rendering/commit/a7dd7d8fa72e847135033fb5bdd5b59dd3992e71
         * But the user may provide the typoscript path manually.
         * TODO: extend helhum/typoscript-rendering to generate that on demand?
         */
        if ($this->configuration['path'] === null && $this->configuration['signature'] !== null) {
            $this->configuration['path'] = 'tt_content.list.20.' . $this->configuration['signature'];
        }
    }

    /**
     * @return void
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response = null, callable $next = null)
    {
        $response = $this->process($request);

        return $next !== null ? $next($request, $response) : $response;
    }

    /**
     * @return void
     */
    public function process(ServerRequestInterface $request, $handler = null)
    {
        $queryParams = [
            'id' => $this->configuration['page'],
            'tx_typoscriptrendering' => [
                'context' => json_encode([
                    'record' => $this->configuration['record'],
                    'path' => $this->configuration['path'],
                ]),
            ],
            'tx_' . $this->configuration['signature'] => [
                'controller' => $this->configuration['controller'],
                'action' => $this->configuration['action'],
            ] + ($this->configuration['arguments'] ?? []),
        ];

        $queryParams = $this->chash($queryParams);

        /* Provide the original request as globals to be able to lookup the original route/query in extbase.
         * @TODO: Do we really want to do this? */
        $GLOBALS['PSR7_REQUEST'] = $request;

        $uri = $request->getUri()->withPath('/')->withQuery(ltrim(GeneralUtility::implodeArrayForUrl('', $queryParams), '&'));
        $request = $request->withRequestTarget('/')->withQueryParams($queryParams)->withUri($uri);

        $this->rewriteGlobals($request);

        $requestHandler = GeneralUtility::makeInstance(RequestHandler::class, Bootstrap::getInstance());
        $response = $requestHandler->handleRequest($request);

        if ($handler !== null) {
            return $handler->handle($request);
        }

        return $response;
    }

    /**
     * @param  ServerRequestInterface $request
     * @return void
     */
    protected function rewriteGlobals(ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $queryString = ltrim(GeneralUtility::implodeArrayForUrl('', $queryParams), '&');
        $path = $request->getRequestTarget('/');

        $_GET = $queryParams;
        $_SERVER['QUERY_STRING'] = $queryString;
        $_SERVER['REQUEST_URI'] = $path . (!empty($queryString) ? '?' . $queryString : '');
        GeneralUtility::flushInternalRuntimeCaches();
    }

    /**
     * @param  array $queryParams
     * @return array
     */
    protected function chash(array $queryParams): array
    {
        $queryParams['cHash'] = $this->cacheHashCalculator->calculateCacheHash(
            $this->cacheHashCalculator->getRelevantParameters(
                GeneralUtility::implodeArrayForUrl('', $queryParams)
            )
        );

        return $queryParams;
    }

    /**
     * Builds the plugin signature for the tt_content rendering
     *
     * @param string $extensionName
     * @param string $pluginName
     *
     * @return string
     *
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin()
     */
    protected function buildPluginSignature(string $extensionName, string $pluginName): string
    {
        // Check if vendor name is prepended to extensionName in the format {vendorName}.{extensionName}
        $delimiterPosition = strrpos($extensionName, '.');
        if ($delimiterPosition !== false) {
            $extensionName = substr($extensionName, $delimiterPosition + 1);
        }
        $extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionName)));

        return strtolower($extensionName . '_' . $pluginName);
    }
}
