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

namespace GAYA\Hcaptcha\Validation;

use GAYA\Hcaptcha\Event\TranslateErrorMessageEvent;
use GAYA\Hcaptcha\Service\ConfigurationService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class HcaptchaValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    private ?ConfigurationService $configurationService = null;

    private ?RequestFactory $requestFactory = null;

    public function __construct(private readonly EventDispatcher $eventDispatcher) {}

    /**
     * Validate the captcha value from the request and add an error if not valid.
     *
     * @param mixed $value The value
     */
    protected function isValid(mixed $value): void
    {
        $response = $this->validateHcaptcha();

        if ($response === [] || (bool)($response['success'] ?? false) === false) {
            if (empty($response['error-codes'])) {
                $this->addError(
                    $this->translateErrorMessage(
                        'error_hcaptcha_generic',
                        'hcaptcha'
                    ),
                    1637268462
                );
            } else {
                foreach ((array)$response['error-codes'] as $errorCode) {
                    assert(is_string($errorCode));
                    $this->addError(
                        $this->translateErrorMessage(
                            'error_hcaptcha_' . $errorCode,
                            'hcaptcha'
                        ),
                        1566209403
                    );
                }
            }
        }
    }

    private function validateHcaptcha(): array
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        /** @var array $parsedBody */
        $parsedBody = $request->getParsedBody();
        $hcaptchaFormFieldValue = $parsedBody['h-captcha-response'] ?? null;
        if ($hcaptchaFormFieldValue === null) {
            return ['success' => false, 'error-codes' => ['invalid-post-form']];
        }

        $ip = '';
        $normalizedParams = $request->getAttribute('normalizedParams');
        if ($normalizedParams) {
            $ip = $normalizedParams->getRemoteAddress();
        }

        $url = HttpUtility::buildUrl(
            [
                'host' => $this->getConfigurationService()->getVerificationServer(),
                'query' => http_build_query(
                    [
                        'secret' => $this->getConfigurationService()->getPrivateKey(),
                        'response' => $hcaptchaFormFieldValue,
                        'remoteip' => $ip,
                    ]
                ),
            ]
        );

        $response = $this->getRequestFactory()->request($url, 'POST');

        $body = (string)$response->getBody();
        $responseArray = json_decode($body, true);
        return is_array($responseArray) ? $responseArray : [];
    }

    /**
     * @codeCoverageIgnore
     */
    protected function translateErrorMessage(string $translateKey, string $extensionName = '', array $arguments = []): string
    {
        $event = new TranslateErrorMessageEvent($translateKey);
        $this->eventDispatcher->dispatch($event);

        $message = $event->getMessage();
        if ($message !== '' && $message !== '0') {
            return $message;
        }

        return LocalizationUtility::translate(
            $translateKey,
            $extensionName,
            $arguments
        ) ?? 'Validating the captcha failed.';
    }

    private function getConfigurationService(): ConfigurationService
    {
        if (!($this->configurationService instanceof ConfigurationService)) {
            $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        }

        return $this->configurationService;
    }

    private function getRequestFactory(): RequestFactory
    {
        if (!($this->requestFactory instanceof RequestFactory)) {
            $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        }

        return $this->requestFactory;
    }
}
