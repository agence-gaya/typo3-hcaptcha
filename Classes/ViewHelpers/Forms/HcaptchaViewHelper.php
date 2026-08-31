<?php

declare(strict_types=1);

/*
 * This file is part of the hcaptcha extension for TYPO3
 * - (c) 2026 GAYA
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace GAYA\Hcaptcha\ViewHelpers\Forms;

use GAYA\Hcaptcha\Service\ConfigurationService;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @codeCoverageIgnore maybe test with an acceptance test at a later point
 */
class HcaptchaViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(private readonly ConfigurationService $configurationService, private readonly AssetCollector $assetCollector) {}

    public function render(): string
    {
        if (!$this->renderingContext instanceof RenderingContextInterface) {
            return '';
        }

        /** @var FormRuntime|null $formRuntime */
        $formRuntime = $this->renderingContext
            ->getViewHelperVariableContainer()
            ->get(RenderRenderableViewHelper::class, 'formRuntime');

        if ($formRuntime instanceof FormRuntime) {
            /**
             * @psalm-suppress InternalMethod
             */
            $renderingOptions = $formRuntime->getRenderingOptions();
            if (isset($renderingOptions['previewMode']) && $renderingOptions['previewMode'] === true) {
                return '';
            }
        }

        $this->assetCollector->addJavaScript(
            'hcaptcha',
            $this->configurationService->getApiScript(),
            ['async' => '', 'defer' => '']
        );
        return '<div class="h-captcha" data-sitekey="' . $this->configurationService->getPublicKey() . '"></div>';
    }
}
