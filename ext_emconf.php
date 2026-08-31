<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'hCaptcha for EXT:form',
    'description'      => 'TYPO3 Extension to add hCaptcha to EXT:form - The privacy friendly captcha alternative.',
    'category'         => 'frontend',
    'author'           => 'GAYA',
    'author_email'     => 'tech@gaya.fr',
    'author_company'   => 'GAYA',
    'state'            => 'stable',
    'uploadfolder'     => '0',
    'clearCacheOnLoad' => 1,
    'version'          => '2.3.0',
    'constraints'      => [
        'depends' => [
            'extbase' => '10.4.0-13.4.99',
            'fluid' => '10.4.0-13.4.99',
            'form' => '10.4.0-13.4.99',
            'typo3' => '10.4.0-13.4.99',
        ],
    ],
];
