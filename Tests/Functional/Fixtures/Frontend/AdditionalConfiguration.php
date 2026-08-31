<?php

declare(strict_types=1);

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive(
    $GLOBALS['TYPO3_CONF_VARS'],
    [
        'MAIL' => [
            'defaultMailFromAddress' => 'tech@gaya.fr',
            'defaultMailFromName' => 'GAYA',
            'transport' => 'mbox',
            'transport_spool_type' => 'file',
            'transport_spool_filepath' => \GAYA\Hcaptcha\Tests\Functional\FunctionalTestCase::MAIL_SPOOL_FOLDER,
        ],
    ]
);
