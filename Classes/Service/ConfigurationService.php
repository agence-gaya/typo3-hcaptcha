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

namespace GAYA\Hcaptcha\Service;

use GAYA\Hcaptcha\Exception\MissingKeyException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class ConfigurationService
{
    private array $settings;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'hcaptcha'
        );
    }

    /**
     * @throws MissingKeyException
     */
    public function getPublicKey(): string
    {
        $publicKey = empty($this->settings['publicKey'])
            ? getenv('HCAPTCHA_PUBLIC_KEY')
            : $this->settings['publicKey'];

        if (empty($publicKey)) {
            throw new MissingKeyException(
                'hCaptcha public key not defined',
                1603034266
            );
        }

        if (!is_string($publicKey)) {
            throw new MissingKeyException(
                'hCaptcha public key not a string',
                1788214942
            );
        }

        return $publicKey;
    }

    /**
     * @throws MissingKeyException
     */
    public function getPrivateKey(): string
    {
        $privateKey = empty($this->settings['privateKey'])
            ? getenv('HCAPTCHA_PRIVATE_KEY')
            : $this->settings['privateKey'];

        if (empty($privateKey)) {
            throw new MissingKeyException(
                'hCaptcha private key not defined',
                1603034285
            );
        }

        if (!is_string($privateKey)) {
            throw new MissingKeyException(
                'hCaptcha private key not a string',
                1788214943
            );
        }

        return $privateKey;
    }

    /**
     * @throws MissingKeyException
     */
    public function getVerificationServer(): string
    {
        $verificationServer = empty($this->settings['verificationServer'])
            ? getenv('HCAPTCHA_VERIFICATION_SERVER')
            : $this->settings['verificationServer'];

        if (empty($verificationServer)) {
            throw new MissingKeyException(
                'hCaptcha verification server address key not defined',
                1603034313
            );
        }

        if (!is_string($verificationServer)) {
            throw new MissingKeyException(
                'hCaptcha verification server address key not a string',
                1788214944
            );
        }

        return $verificationServer;
    }

    /**
     * @throws MissingKeyException
     */
    public function getApiScript(): string
    {
        $apiScript = empty($this->settings['apiScript'])
            ? getenv('HCAPTCHA_API_SCRIPT')
            : $this->settings['apiScript'];

        if (empty($apiScript)) {
            throw new MissingKeyException(
                'hCaptcha api script not defined',
                1603034329
            );
        }

        if (!is_string($apiScript)) {
            throw new MissingKeyException(
                'hCaptcha api script not a string',
                1788214945
            );
        }

        return $this->appendSiteLanguage($apiScript);
    }

    private function appendSiteLanguage(string $apiScript): string
    {
        // @codeCoverageIgnoreStart
        try {
            $uri = new Uri($apiScript);
        } catch (\Exception) {
            return $apiScript;
        }

        // @codeCoverageIgnoreEnd

        parse_str($uri->getQuery(), $apiScriptQueryParts);

        if (isset($apiScriptQueryParts['hl'])) {
            return $apiScript;
        }

        $request = $this->getServerRequest();
        $siteLanguage = $request->getAttribute('language');

        // @codeCoverageIgnoreStart
        if (!$siteLanguage instanceof SiteLanguage) {
            return $apiScript;
        }

        // @codeCoverageIgnoreEnd

        if (method_exists($siteLanguage, 'getTwoLetterIsoCode')) {
            $apiScriptQueryParts['hl'] = $siteLanguage->getTwoLetterIsoCode();
        } else {
            $apiScriptQueryParts['hl'] = $siteLanguage->getLocale()->getLanguageCode();
        }

        $uri = $uri->withQuery(http_build_query($apiScriptQueryParts));

        return (string)$uri;
    }

    private function getServerRequest(): ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();

        // @codeCoverageIgnoreStart
        if (!($request instanceof ServerRequestInterface)) {
            throw new \InvalidArgumentException(sprintf('Request must implement "%s"', ServerRequestInterface::class), 1674637738);
        }

        // @codeCoverageIgnoreEnd

        return $request;
    }
}
