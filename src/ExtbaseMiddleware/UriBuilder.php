<?php
namespace Bnf\Typo3Middleware\ExtbaseMiddleware;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

/**
 * UriBuilder
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UriBuilder extends \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
{
    /**
     * @var RequestBuilder
     */
    protected $requestBuilder;

    /**
     * @param  RequestBuilder $requestBuilder
     * @return void
     */
    public function injectRequestBuilder(RequestBuilder $requestBuilder)
    {
        $this->requestBuilder = $requestBuilder;
    }

    public function initializeObject()
    {
        $this->psr7Request = $this->configurationManager->psr7Request;
        $this->request = $this->requestBuilder->build();
    }

    public function buildFrontendUri()
    {
        $arguments = [];
        $query = $this->psr7Request->getQueryParams();

        if ($this->addQueryString === true) {
            if ($this->addQueryStringMethod) {
                switch ($this->addQueryStringMethod) {
                    case 'GET':
                        $arguments = $query;
                        break;
                    case 'POST':
                        $arguments = $this->psr7Request->getParsedBody();
                        break;
                    case 'GET,POST':
                        $arguments = array_replace_recursive($query, $this->psr7Request->getParsedBody());
                        break;
                    case 'POST,GET':
                        $arguments = array_replace_recursive($this->psr7Request->getParsedBody(), $query);
                        break;
                    default:
                        $arguments = GeneralUtility::explodeUrl2Array($this->psr7Request->getServerParams()['QUERY_STRING'], true);
                }
            } else {
                $arguments = $query;
            }
            foreach ($this->argumentsToBeExcludedFromQueryString as $argumentToBeExcluded) {
                $argumentToBeExcluded = GeneralUtility::explodeUrl2Array($argumentToBeExcluded, true);
                $arguments = ArrayUtility::arrayDiffAssocRecursive($arguments, $argumentToBeExcluded);
            }
        }
        ArrayUtility::mergeRecursiveWithOverrule($arguments, $this->arguments);
        $arguments = $this->convertDomainObjectsToIdentityArrays($arguments);

        if ($this->targetPageUid !== null) {
            $arguments['id'] = $this->targetPageUid;
        } elseif (isset($GLOBALS['TSFE']->id)) {
            $arguments['id'] = $GLOBALS['TSFE']->id;
        }

        if ($this->targetPageType !== 0) {
            $arguments['type'] = $this->targetPageType;
        } elseif ($this->format !== '') {
            $arguments['type'] = $this->extensionService->getTargetPageTypeByFormat($this->request->getControllerExtensionName(), $this->format);
        }
        $this->lastArguments = $arguments;

        if ($this->noCache === true) {
            $arguments['no_cache'] = 1;
        } elseif ($this->useCacheHash) {
            $cacheHash = GeneralUtility::makeInstance(CacheHashCalculator::class);
            $cHash_array = $cacheHash->getRelevantParameters(GeneralUtility::implodeArrayForUrl('', $arguments));
            $cHash_calc = $cacheHash->calculateCacheHash($cHash_array);
            $arguments['cHash'] = $cHash_calc;
        }

        $path = '/';
        $query_string = ltrim(GeneralUtility::implodeArrayForUrl('', $arguments), '&');

        if ($this->createAbsoluteUri) {
            return (string) ($this->psr7Request->getUri()->withPath($path)->withQuery($query_string)->withFragment($this->section ?? ''));
        }

        return $path . ($query_string ? '?' . $query_string : '') . ($this->section !== '' ? '#' . $this->section : '');
    }
}
