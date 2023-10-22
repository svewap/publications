<?php

use In2code\Publications\Controller\PublicationController;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

call_user_func(
    function () {

        /**
         * Include Frontend Plugins
         */
        ExtensionUtility::configurePlugin(
            'publications',
            'Publication',
            [
                PublicationController::class => 'list,resetList,downloadBibtex,downloadXml'
            ],
            [
                PublicationController::class => 'list,resetList,downloadBibtex,downloadXml'
            ],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        /**
         * PageTSConfig
         */
        ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:publications/Configuration/TsConfig/Page.tsconfig">'
        );

        /**
         * Logging
         */
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['In2code']['Publications'] = [
            'writerConfiguration' => [
                LogLevel::DEBUG => [
                    FileWriter::class => [
                        'logFile' => 'typo3temp/logs/tx_publications.log'
                    ]
                ]
            ]
        ];

        /**
         * Fluid Namespace
         */
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['publications'][]
            = 'In2code\Publications\ViewHelpers';
    }
);
