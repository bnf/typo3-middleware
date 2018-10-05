<?php
namespace Bnf\Typo3Middleware;

/**
 * ExtbaseMiddleware
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ExtbaseMiddleware extends \TYPO3\CMS\Extbase\Core\Bootstrap
{
    protected $cleanup;

    protected $configuration = [
        'objects' => [
            \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class => [
                'className' => \Bnf\Typo3Middleware\ExtbaseMiddleware\UriBuilder::class,
            ],
        ],
        'features' => [
            'skipDefaultArguments' => 0,
            'ignoreAllEnableFieldsInBe' => 0,
        ],
        'mvc' => [
            'throwPageNotFoundExceptionIfActionCantBeResolved' => 0,
            'requestHandlers' => [
                \TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler::class => \TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler::class,
                \TYPO3\CMS\Extbase\Mvc\Web\BackendRequestHandler::class => \TYPO3\CMS\Extbase\Mvc\Web\BackendRequestHandler::class,
                \TYPO3\CMS\Extbase\Mvc\Cli\RequestHandler::class => \TYPO3\CMS\Extbase\Mvc\Cli\RequestHandler::class,
                \TYPO3\CMS\Fluid\Core\Widget\WidgetRequestHandler::class => \TYPO3\CMS\Fluid\Core\Widget\WidgetRequestHandler::class,
            ],
        ],
        'persistence' => [
            'enableAutomaticCacheClearing' => 1,
            'updateReferenceIndex' => 0,
            'useQueryCache' => true,
            'classes' => [
                \TYPO3\CMS\Extbase\Domain\Model\FileMount::class => [
                    'mapping' => [
                        'tableName' => 'sys_filemounts',
                        'columns' => [
                            'title' => [ 'mapOnProperty' => 'title' ],
                            'path' => [
                                'mapOnProperty' => 'path',
                            ],
                            'base' => [
                                'mapOnProperty' => 'isAbsolutePath',
                            ],
                        ],
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\FileReference::class => [
                    'mapping' => [
                        'tableName' => 'sys_file_reference',
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\File::class => [
                    'mapping' => [
                        'tableName' => 'sys_file',
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\BackendUser::class => [
                    'mapping' => [
                        'tableName' => 'be_users',
                        'columns' => [
                            'username' => [
                                'mapOnProperty' => 'userName',
                            ],
                            'admin' => [
                                'mapOnProperty' => 'isAdministrator',
                            ],
                            'disable' => [
                                'mapOnProperty' => 'isDisabled',
                            ],
                            'realName' => [
                                'mapOnProperty' => 'realName',
                            ],
                            'starttime' => [
                                'mapOnProperty' => 'startDateAndTime',
                            ],
                            'endtime' => [
                                'mapOnProperty' => 'endDateAndTime',
                            ],
                            'disableIPlock' => [
                                'mapOnProperty' => 'ipLockIsDisabled',
                            ],
                            'lastlogin' => [
                                'mapOnProperty' => 'lastLoginDateAndTime',
                            ],
                        ],
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup::class => [
                    'mapping' => [
                        'tableName' => 'be_groups',
                        'columns' => [
                            'subgroup' => [
                                'mapOnProperty' => 'subGroups',
                            ],
                            'groupMods' => [
                                'mapOnProperty' => 'modules',
                            ],
                            'tables_select' => [
                                'mapOnProperty' => 'tablesListening',
                            ],
                            'tables_modify' => [
                                'mapOnProperty' => 'tablesModify',
                            ],
                            'pagetypes_select' => [
                                'mapOnProperty' => 'pageTypes',
                            ],
                            'non_exclude_fields' => [
                                'mapOnProperty' => 'allowedExcludeFields',
                            ],
                            'explicit_allowdeny' => [
                                'mapOnProperty' => 'explicitlyAllowAndDeny',
                            ],
                            'allowed_languages' => [
                                'mapOnProperty' => 'allowedLanguages',
                            ],
                            'workspace_perms' => [
                                'mapOnProperty' => 'workspacePermission',
                            ],
                            'db_mountpoints' => [
                                'mapOnProperty' => 'databaseMounts',
                            ],
                            'file_permissions' => [
                                'mapOnProperty' => 'fileOperationPermissions',
                            ],
                            'lockToDomain' => [
                                'mapOnProperty' => 'lockToDomain',
                            ],
                            'hide_in_lists' => [
                                'mapOnProperty' => 'hideInList',
                            ],
                            'TSconfig' => [
                                'mapOnProperty' => 'tsConfig',
                            ],
                        ],
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\FrontendUser::class => [
                    'mapping' => [
                        'tableName' => 'fe_users',
                        'columns' => [
                            'lockToDomain' => [
                                'mapOnProperty' => 'lockToDomain',
                            ],
                        ],
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup::class => [
                    'mapping' => [
                        'tableName' => 'fe_groups',
                        'columns' => [
                            'lockToDomain' => [
                                'mapOnProperty' => 'lockToDomain',
                            ],
                        ],
                    ],
                ],
                \TYPO3\CMS\Extbase\Domain\Model\Category::class => [
                    'mapping' => [
                        'tableName' => 'sys_category',
                    ],
                ],
                \TYPO3\CMS\Beuser\Domain\Model\BackendUser::class => [
                    'mapping' => [
                        'tableName' => 'be_users',
                        'columns' => [
                            'allowed_languages' => [
                                'mapOnProperty' => 'allowedLanguages',
                            ],
                            'file_mountpoints' => [
                                'mapOnProperty' => 'fileMountPoints',
                            ],
                            'db_mountpoints' => [
                                'mapOnProperty' => 'dbMountPoints',
                            ],
                            'usergroup' => [
                                'mapOnProperty' => 'backendUserGroups',
                            ],
                        ],
                    ],
                ],
                \TYPO3\CMS\Beuser\Domain\Model\BackendUserGroup::class => [
                    'mapping' => [
                        'tableName' => 'be_groups',
                        'columns' => [
                            'subgroup' => [
                                'mapOnProperty' => 'subGroups',
                            ],
                        ],
                    ],
                ],
                // TODO: only for v8
                \TYPO3\CMS\SysNote\Domain\Model\SysNote::class => [
                    'mapping' => [
                        'tableName' => 'sys_note',
                        'recordType' => '',
                        'columns' => [
                            'crdate' => [
                                'mapOnProperty' => 'creationDate',
                            ],
                            'tstamp' => [
                                'mapOnProperty' => 'modificationDate',
                            ],
                            'cruser' => [
                                'mapOnProperty' => 'author',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @return void
     */
    public function __construct($configuration = [], $cleanup = true)
    {
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($this->configuration, $configuration);
        $this->cleanup = $cleanup;
    }

    public function __invoke($request, $response, $next)
    {
        //!isset($GLOBALS['TCA']) && \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
        !isset($GLOBALS['TCA']) && \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadBaseTca();

        $backupSlots = $this->disableIncompatibleSignalSlots();
        $this->initialize($this->configuration);

        /* HEADS UP! psr7Request is not an official property! */
        $this->configurationManager->psr7Request = $request;

        $response = $next($request, $response);

        $this->resetSingletons();
        $this->objectManager->get(\TYPO3\CMS\Extbase\Service\CacheService::class)->clearCachesOfRegisteredPageIds();

        if ($this->cleanup) {
            unset($this->configurationManager->psr7Request);
            $this->resetSlots($backupSlots);
            /* TODO: unset extbase environment? */
        }

        return $response;
    }

    /**
     * @return void
     */
    protected function disableIncompatibleSignalSlots()
    {
        $signalSlot = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $slots = $signalSlot->getSlots(\TYPO3\CMS\Core\Resource\Index\MetaDataRepository::class, 'recordPostRetrieval');
        $backup = $slots;
        $prop = null;

        if (!empty($slots)) {
            $prop = new \ReflectionProperty(get_class($signalSlot), 'slots');
            $prop->setAccessible(true);

            $all = $prop->getValue($signalSlot);
            $all[\TYPO3\CMS\Core\Resource\Index\MetaDataRepository::class]['recordPostRetrieval'] = array_filter($slots, function ($slot) {
                return $slot['class'] !== \TYPO3\CMS\Frontend\Aspect\FileMetadataOverlayAspect::class;
            });
            $prop->setValue($signalSlot, $all);
            // return backup
            return $slots;
        }

        return [];
    }

    /**
     * @param  array $backup
     * @return void
     */
    protected function resetSlots($backup)
    {
        $signalSlot = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $prop = new \ReflectionProperty(get_class($signalSlot), 'slots');
        $prop->setAccessible(true);
        $prop->setValue($signalSlot, $backup);
    }

    /**
     * @return void
     */
    public function initialize($configuration)
    {
        $this->initializeObjectManager();
        $this->initializeConfiguration($configuration);
        $this->configureObjectManager();
        is_callable([$this, 'initializeCache']) && $this->initializeCache();
        is_callable([$this, 'initializeReflection']) && $this->initializeReflection();
        $this->initializePersistence();
    }

    /**
     * Initializes the Object framework.
     *
     * @param array $configuration
     * @return void
     * @see initialize()
     */
    public function initializeConfiguration($configuration)
    {
        //parent::initializeConfiguration($configuration);
        $this->configurationManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class);
        $this->configurationManager->setConfiguration($configuration);
    }
}
