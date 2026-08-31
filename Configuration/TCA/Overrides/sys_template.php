<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

call_user_func(static function () {
    ExtensionManagementUtility::addStaticFile(
        'hcaptcha',
        'Configuration/TypoScript',
        'hCaptcha Configuration'
    );
});
