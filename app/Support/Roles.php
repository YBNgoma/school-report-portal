<?php

namespace App\Support;

/**
 * Canonical list of roles for the School Report Portal.
 * Roles are stored and used via Spatie's roles table, these
 * constants keep the names consistent everywhere.
 */
final class Roles
{
    public const ADMIN = 'admin';

    public const TEACHER = 'teacher';

    public const GUARDIAN = 'guardian';

    public const STUDENT = 'student';

    /** @var list<string> */
    public const ALL = [
        self::ADMIN,
        self::TEACHER,
        self::GUARDIAN,
        self::STUDENT,
    ];
}
