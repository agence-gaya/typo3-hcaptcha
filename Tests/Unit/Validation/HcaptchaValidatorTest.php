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

namespace GAYA\Hcaptcha\Tests\Unit\Validation;

use GAYA\Hcaptcha\Service\ConfigurationService;
use GAYA\Hcaptcha\Validation\HcaptchaValidator;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[CoversClass(HcaptchaValidator::class)]
#[CoversMethod(HcaptchaValidator::class, '__construct')]
#[CoversMethod(HcaptchaValidator::class, 'isValid')]
#[CoversMethod(HcaptchaValidator::class, 'validateHcaptcha')]
#[CoversMethod(HcaptchaValidator::class, 'getConfigurationService')]
#[CoversMethod(HcaptchaValidator::class, 'getRequestFactory')]
#[BackupGlobals(true)]
class HcaptchaValidatorTest extends TestCase
{
    private ServerRequestInterface&Stub $typo3request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->typo3request = self::createStub(ServerRequestInterface::class);
        $GLOBALS['TYPO3_REQUEST'] = $this->typo3request;
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    #[Test]
    public function validateReturnsErrorIfPostResponseFieldIsEmpty(): void
    {
        $subject = $this->getMockBuilder(HcaptchaValidator::class)
            ->setConstructorArgs([self::createStub(EventDispatcher::class)])
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
        $subject->expects($this->once())->method('translateErrorMessage')->willReturn('Translated error');

        $result = $subject->validate(1);
        $errors = $result->getErrors();

        self::assertCount(1, $errors);
        self::assertSame(1566209403, $errors[0]->getCode());
    }

    public static function validateReturnsErrorIfVerificationRequestReturnsErrorDataProvider(): \Generator
    {
        yield 'Unsuccessful response with error codes' => [
            'responseData' => [
                'success' => false,
                'error-codes' => ['invalid-input-secret'],
            ],
            'expectedErrorCode' => 1566209403,
        ];

        yield 'Unsuccessful response with empty error codes' => [
            'responseData' => [
                'success' => false,
                'error-codes' => [],
            ],
            'expectedErrorCode' => 1637268462,
        ];

        yield 'Unsuccessful response with missing error codes' => [
            'responseData' => [
                'success' => false,
            ],
            'expectedErrorCode' => 1637268462,
        ];

        yield 'Empty response' => [
            'responseData' => [],
            'expectedErrorCode' => 1637268462,
        ];
    }

    #[Test]
    #[DataProvider('validateReturnsErrorIfVerificationRequestReturnsErrorDataProvider')]
    public function validateReturnsErrorIfVerificationRequestReturnsError(
        array $responseData,
        int $expectedErrorCode
    ): void {
        $subject = $this->getMockBuilder(HcaptchaValidator::class)
            ->setConstructorArgs([self::createStub(EventDispatcher::class)])
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
        $subject->expects($this->once())->method('translateErrorMessage')->willReturn('Translated error');

        $requestFactory = $this->createMock(RequestFactory::class);
        GeneralUtility::addInstance(RequestFactory::class, $requestFactory);
        $normalizedParams = self::createStub(NormalizedParams::class);
        $configurationService = self::createStub(ConfigurationService::class);
        GeneralUtility::addInstance(ConfigurationService::class, $configurationService);
        $this->typo3request->method('getAttribute')->willReturnMap([
            ['normalizedParams', null, $normalizedParams],
        ]);

        $normalizedParams->method('getRemoteAddress')->willReturn('127.0.0.1');
        $this->typo3request->method('getParsedBody')->willReturn([
            'h-captcha-response' => 'verification-key-response',
        ]);

        $configurationService->method('getVerificationServer')->willReturn('https://example.com/siteverify');
        $configurationService->method('getPrivateKey')->willReturn('my_superb_key');

        $requestFactory->expects($this->once())
            ->method('request')
            ->with('https://example.com/siteverify?secret=my_superb_key&response=verification-key-response&remoteip=127.0.0.1', 'POST')
            ->willReturn(new Response(200, [], json_encode($responseData)));

        $result = $subject->validate(1);
        $errors = $result->getErrors();

        self::assertCount(1, $errors);
        self::assertSame($expectedErrorCode, $errors[0]->getCode());
    }
}
