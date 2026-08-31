<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

call_user_func(static function () {
    ExtensionManagementUtility::addTypoScript(
        'hcaptcha',
        'setup',
        'module.tx_form {
          settings {
            yamlConfigurations {
              158329071148 = EXT:hcaptcha/Configuration/Form/Yaml/BaseSetup.yaml
            }
          }
        }'
    );
});
