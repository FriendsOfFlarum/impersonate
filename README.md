# Impersonate by ![Flagrow logo](https://avatars0.githubusercontent.com/u/16413865?v=3&s=20) [Flagrow](https://discuss.flarum.org/d/1832-flagrow-extension-developer-group), a project of [Gravure](https://gravure.io/)

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/flagrow/impersonate/blob/master/LICENSE.md) [![Latest Stable Version](https://img.shields.io/packagist/v/flagrow/impersonate.svg)](https://packagist.org/packages/flagrow/impersonate) [![Total Downloads](https://img.shields.io/packagist/dt/flagrow/impersonate.svg)](https://packagist.org/packages/flagrow/impersonate) [![Donate](https://img.shields.io/badge/patreon-support-yellow.svg)](https://www.patreon.com/flagrow) [![Join our Discord server](https://discordapp.com/api/guilds/240489109041315840/embed.png)](https://flagrow.io/join-discord)

This extension adds a "Log in as user" button on user profiles for administrators.

## Installation

Use [Bazaar](https://discuss.flarum.org/d/5151-flagrow-bazaar-the-extension-marketplace) or install manually:

```bash
composer require flagrow/impersonate
```

## Updating

```bash
composer update flagrow/impersonate
php flarum migrate
php flarum cache:clear
```

## Configuration

You can configure which groups can impersonate users by going to *Permissions > Login as other users*.
Use with caution, as anybody with that permission can easily access the private data of every user on the forum **and change any admin setting / permission / group by logging in as an administrator !**

## Support our work

We prefer to keep our work available to everyone.
In order to do so we rely on voluntary contributions on [Patreon](https://www.patreon.com/flagrow).

## Security

If you discover a security vulnerability within Impersonate, please send an email to the Gravure team at security@gravure.io. All security vulnerabilities will be promptly addressed.

Please include as many details as possible. You can use `php flarum info` to get the PHP, Flarum and extension versions installed.

## Links

- [Flarum Discuss post](https://discuss.flarum.org/d/9868-flagrow-impersonate-login-as-other-users)
- [Source code on GitHub](https://github.com/flagrow/impersonate)
- [Report an issue](https://github.com/flagrow/impersonate/issues)
- [Download via Packagist](https://packagist.org/packages/flagrow/impersonate)

An extension by [Flagrow](https://flagrow.io/), a project of [Gravure](https://gravure.io/).
