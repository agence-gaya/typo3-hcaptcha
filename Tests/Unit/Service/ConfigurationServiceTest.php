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

namespace GAYA\Hcaptcha\Tests\Unit\Service;

use GAYA\Hcaptcha\Exception\MissingKeyException;
use GAYA\Hcaptcha\Service\ConfigurationService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

#[CoversClass(ConfigurationService::class)]
#[CoversMethod(ConfigurationService::class, '__construct')]
#[CoversMethod(ConfigurationService::class, 'getPublicKey')]
#[CoversMethod(ConfigurationService::class, 'getPrivateKey')]
#[CoversMethod(ConfigurationService::class, 'getVerificationServer')]
#[CoversMethod(ConfigurationService::class, 'getApiScript')]
#[CoversMethod(ConfigurationService::class, 'appendSiteLanguage')]
#[CoversMethod(ConfigurationService::class, 'getServerRequest')]
class ConfigurationServiceTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ConfigurationManager|ObjectProphecy
     */
    private $configurationManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationManager = $this->prophesize(ConfigurationManager::class);
        $this->configurationManager->getConfiguration((string)Argument::cetera())->willReturn([]);
    }

    #[Test]
    public function getPublicKeyThrowsExceptionIfKeyNotSet(): void
    {
        putenv('HCAPTCHA_PUBLIC_KEY');

        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getPublicKey();
    }

    #[Test]
    public function getPublicKeyReturnsKeyFromSettings(): void
    {
        $expectedKey = 'my_superb_key';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'hcaptcha')
            ->willReturn(['publicKey' => $expectedKey]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $publicKey = $subject->getPublicKey();

        self::assertSame($expectedKey, $publicKey);
    }

    #[Test]
    public function getPublicKeyReturnsKeyFromEnv(): void
    {
        $expectedKey = 'my_superb_key';
        putenv('HCAPTCHA_PUBLIC_KEY=' . $expectedKey);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $publicKey = $subject->getPublicKey();

        self::assertSame($expectedKey, $publicKey);
    }

    #[Test]
    public function getPrivateKeyThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getPrivateKey();
    }

    #[Test]
    public function getPrivateKeyReturnsKeyFromSettings(): void
    {
        $expectedKey = 'my_superb_key';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'hcaptcha')
            ->willReturn(['privateKey' => $expectedKey]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $privateKey = $subject->getPrivateKey();

        self::assertSame($expectedKey, $privateKey);
    }

    #[Test]
    public function getPrivateKeyReturnsKeyFromEnv(): void
    {
        $expectedKey = 'my_superb_key';
        putenv('HCAPTCHA_PRIVATE_KEY=' . $expectedKey);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $privateKey = $subject->getPrivateKey();

        self::assertSame($expectedKey, $privateKey);
    }

    #[Test]
    public function getVerificationServerThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getVerificationServer();
    }

    #[Test]
    public function getVerificationServerReturnsKeyFromSettings(): void
    {
        $expectedServer = 'https://example.com';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'hcaptcha')
            ->willReturn(['verificationServer' => $expectedServer]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $verificationServer = $subject->getVerificationServer();

        self::assertSame($expectedServer, $verificationServer);
    }

    #[Test]
    public function getVerificationServerReturnsKeyFromEnv(): void
    {
        $expectedServer = 'https://example.com';
        putenv('HCAPTCHA_VERIFICATION_SERVER=' . $expectedServer);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $verificationServer = $subject->getVerificationServer();

        self::assertSame($expectedServer, $verificationServer);
    }

    #[Test]
    public function getApiScriptThrowsExceptionIfKeyNotSet(): void
    {
        $this->expectException(MissingKeyException::class);
        $subject = new ConfigurationService($this->configurationManager->reveal());
        $subject->getApiScript();
    }

    #[Test]
    public function getApiScriptReturnsKeyFromSettingsWithLanguage(): void
    {
        $expectedScript = 'https://hcaptcha.com/1/api.js';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'hcaptcha')
            ->willReturn(['apiScript' => $expectedScript]);

        $siteLanguageProphecy = $this->prophesize(SiteLanguage::class);

        if (method_exists(SiteLanguage::class, 'getTwoLetterIsoCode')) {
            $siteLanguageProphecy->getTwoLetterIsoCode()->willReturn('en');
        } else {
            $localeProphecy = $this->prophesize(Locale::class);
            $siteLanguageProphecy->getLocale()->willReturn($localeProphecy);
            $localeProphecy->getLanguageCode()->willReturn('en');
        }

        $serverRequestInterfaceProphecy = $this->prophesize(ServerRequestInterface::class);
        $serverRequestInterfaceProphecy->getAttribute('language')->willReturn($siteLanguageProphecy);
        $GLOBALS['TYPO3_REQUEST'] = $serverRequestInterfaceProphecy->reveal();

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $apiScript = $subject->getApiScript();

        self::assertSame($expectedScript . '?hl=en', $apiScript);
    }

    #[Test]
    public function getApiScriptReturnsKeyFromSettingsWithoutLanguage(): void
    {
        $expectedScript = 'https://hcaptcha.com/1/api.js?hl=de';
        $this->configurationManager
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'hcaptcha')
            ->willReturn(['apiScript' => $expectedScript]);

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $apiScript = $subject->getApiScript();

        self::assertSame($expectedScript, $apiScript);
    }

    #[Test]
    public function getApiScriptReturnsKeyFromEnv(): void
    {
        $expectedScript = 'https://hcaptcha.com/1/api.js';
        putenv('HCAPTCHA_API_SCRIPT=' . $expectedScript);

        $siteLanguageProphecy = $this->prophesize(SiteLanguage::class);

        if (method_exists(SiteLanguage::class, 'getTwoLetterIsoCode')) {
            $siteLanguageProphecy->getTwoLetterIsoCode()->willReturn('en');
        } else {
            $localeProphecy = $this->prophesize(Locale::class);
            $siteLanguageProphecy->getLocale()->willReturn($localeProphecy);
            $localeProphecy->getLanguageCode()->willReturn('en');
        }

        $serverRequestInterfaceProphecy = $this->prophesize(ServerRequestInterface::class);
        $serverRequestInterfaceProphecy->getAttribute('language')->willReturn($siteLanguageProphecy);
        $GLOBALS['TYPO3_REQUEST'] = $serverRequestInterfaceProphecy->reveal();

        $subject = new ConfigurationService($this->configurationManager->reveal());
        $apiScript = $subject->getApiScript();

        self::assertSame($expectedScript . '?hl=en', $apiScript);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('HCAPTCHA_PUBLIC_KEY');
        putenv('HCAPTCHA_PRIVATE_KEY');
        putenv('HCAPTCHA_VERIFICATION_SERVER');
        putenv('HCAPTCHA_API_SCRIPT');
    }
}
