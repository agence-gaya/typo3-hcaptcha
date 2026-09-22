[![TYPO3 14](https://img.shields.io/badge/TYPO3-14-orange.svg?style=flat-square)](https://get.typo3.org/version/14)
[![ci](https://github.com/agence-gaya/typo3-hcaptcha/actions/workflows/ci.yml/badge.svg)](https://github.com/agence-gaya/typo3-hcaptcha/actions/workflows/ci.yml)
[![License](https://poser.pugx.org/gaya/typo3-hcaptcha/license)](https://packagist.org/packages/gaya/typo3-hcaptcha)

# TYPO3 Extension to add hCaptcha to EXT:form - The privacy friendly captcha alternative.


Provides [hCaptcha](https://www.hcaptcha.com) integration for TYPO3 EXT:form.

hCaptcha is a free to use alternative to Google reCaptcha with a bigger focus on privacy. It supports initiatives like [PrivacyPass](https://www.hcaptcha.com/privacy-pass).

For more information, see [the hCaptcha website](https://www.hcaptcha.com).

Additionally, hCaptcha provides earnings for solved captchas - that can be donated to
the Wikimedia foundation automatically (which is the case for the default settings of this extension).

This extension is a fork of [dreistromland/typo3-hcaptcha](https://github.com/dreistromland/typo3-hcaptcha) and previously [waldhacker/hcaptcha](https://github.com/waldhacker/ext-hcaptcha).

## Quick Setup

hCaptcha is configured with a default key to make the setup as easy as possible.
It is recommended to create a custom account for your site with hCaptcha and add your own keys.

- Install the extension and activate it
- Include the static template in TypoScript
- Add a `hCaptcha` element to a form

### TypoScript Constants

Set the following constants if you are using your own account:

```typo3_typoscript
plugin.tx_hcaptcha {
  settings {
    publicKey = <your-public-key>
    privateKey = <your-private-key>
  }
}
```

### Environment variables
As an alternative to the TypoScript configuration, you can also use environment variables:
* `HCAPTCHA_PUBLIC_KEY`
* `HCAPTCHA_PRIVATE_KEY`

### Content Security Policy

If you are using CSP, make sure to adjust them accordingly:

* script-src should include `https://hcaptcha.com, https://*.hcaptcha.com`
* frame-src should include `https://hcaptcha.com, https://*.hcaptcha.com`
* style-src should include `https://hcaptcha.com, https://*`

## Privacy

Make sure to inform your users of your usage of hCaptcha and what that means - especially if you
are using the invisible Enterprise version.

For more info see: https://docs.hcaptcha.com/faq

### Help & Support

* Issues: [https://github.com/agence-gaya/typo3-hcaptcha/issues](https://github.com/agence-gaya/typo3-hcaptcha/issues)
