<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'hcaptcha' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:hcaptcha/Resources/Public/Icons/Extension.svg',
    ],
];
