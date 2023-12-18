<?php
defined('TYPO3_MODE') or die();

// Plugin "uri".
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['dfgviewer_uri'] = 'layout,select_key,pages,recursive';
// Plugin "TESTTEST".
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['TESTTEST'] = 'layout,select_key,pages,recursive';
// $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['TESTTEST'] = 'pi_flexform';
// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('TESTTEST', 'FILE:EXT:' . 'dlf/Configuration/FlexForms/TESTTEST.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Slub.Dfgviewer',
    'Uri',
    'LLL:EXT:dlf/Resources/Private/Language/locallang_be.xlf:plugins.search.title',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Slub.Dfgviewer',
    'SRU',
    'LLL:EXT:dfgviewer/Resources/Private/Language/locallang_be.xlf:plugins.sru.title',
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Slub.Dfgviewer',
    'TESTTEST',
    'TESTTEST',
    'EXT:dfgviewer/Resources/Public/Images/PageOCR-white.svg'
);
