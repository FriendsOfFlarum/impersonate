<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate;

final class Impersonation
{
    /**
     * Session key holding the id of the user who started impersonating.
     *
     * Lives in the session because logIn() regenerates the session but keeps its
     * attributes, while a real logout invalidates (flushes) them — so the marker
     * survives impersonation and is destroyed by logout without extra handling.
     */
    public const ORIGINAL_USER_SESSION_KEY = 'fof_impersonate_original_user_id';

    /**
     * Session key holding the id of the user currently being impersonated.
     *
     * Returning is only honored while the session's actor matches this id: core's
     * POST /login preserves session attributes, so without this check a credentialed
     * login into an unrelated account mid-impersonation would inherit the return path.
     */
    public const IMPERSONATED_USER_SESSION_KEY = 'fof_impersonate_impersonated_user_id';

    /**
     * Session key remembering whether the original user was logged in with a
     * remember token, so returning can restore an equivalent session.
     */
    public const ORIGINAL_REMEMBER_SESSION_KEY = 'fof_impersonate_original_remember';

    /**
     * Id of the user being impersonated by the current request's session, if any.
     *
     * Request-scoped: set by the DetectImpersonation middleware before
     * authentication runs, so the last_seen_at guard can tell impersonated
     * activity apart from the user's own.
     */
    public static ?int $impersonatedUserId = null;
}
