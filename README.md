# Impersonate by FriendsOfFlarum

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/FriendsOfFlarum/impersonate/blob/master/LICENSE.md) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/impersonate.svg)](https://packagist.org/packages/fof/impersonate) [![Total Downloads](https://img.shields.io/packagist/dt/fof/impersonate.svg)](https://packagist.org/packages/fof/impersonate) [![OpenCollective](https://img.shields.io/badge/opencollective-fof-blue.svg)](https://opencollective.com/fof/donate)

This extension adds a "Log in as user" button on user profiles for administrators and others who are granted the permission — and a way back: while impersonating, a notice is shown with a **Return to {username}** button that switches you back to your own account.

## Features

- **Impersonate users** from their user profile (moderation controls) or from the admin panel's user list.
- **Return to your own account** without logging out: a persistent notice (and an entry in the user menu) offers "Return to {username}" for the whole impersonation session. If you logged in with "remember me", that is restored when you return.
- **Require a reason** (optional setting): impersonation can require a reason, which is recorded in the audit log ([flarum/audit](https://github.com/flarum/audit)) and/or as moderator notes on both accounts ([fof/moderator-notes](https://github.com/FriendsOfFlarum/moderator-notes)), whichever is enabled.
- **Audit logging**: with flarum/audit enabled, both starting and ending an impersonation are written to the audit log.
- **No activity traces**: browsing as an impersonated user does not update their "last seen" time and never shows them as online. Their own devices are unaffected.

## Installation

Install manually:

```bash
composer require fof/impersonate:"*"
```

## Updating

```bash
composer update fof/impersonate
php flarum migrate
php flarum cache:clear
```

## Configuration

You can configure which groups can impersonate users by going to *Permissions > Login as other users*.
Use with caution, as anybody with that permission can easily access the private data of every user on the forum.

The *require a reason* setting appears once flarum/audit or fof/moderator-notes is enabled, since one of them is needed to record it.

## Security model

- **Non-admins cannot impersonate an admin account**, and nobody can impersonate themselves.
- Permissions are checked against your **real current identity**: an impersonated session never inherits the impersonator's ability to impersonate.
- Returning is bound to the session and to the exact account that was impersonated. It stops working when you log out, when the session expires (one hour of inactivity), or after logging into a different account — from then on, only credentials get you back in.
- The impersonated session never carries a "remember me" cookie, so an idle browser cannot silently re-elevate itself.

## For developers

Two events are dispatched, which other extensions can listen to:

- `FoF\Impersonate\Events\Impersonated` — when impersonation starts (`$actor`, `$user`, `$switchReason`).
- `FoF\Impersonate\Events\ImpersonationEnded` — when the impersonator returns (`$user`, `$originalUser`).

## Links

- [![OpenCollective](https://img.shields.io/badge/donate-friendsofflarum-44AEE5?style=for-the-badge&logo=open-collective)](https://opencollective.com/fof/donate)
- [Flarum Discuss post](https://discuss.flarum.org/d/9868)
- [Source code on GitHub](https://github.com/FriendsOfFlarum/impersonate)
- [Report an issue](https://github.com/FriendsOfFlarum/issues)
- [Download via Packagist](https://packagist.org/packages/fof/impersonate)
