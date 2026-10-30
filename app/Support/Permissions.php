<?php

namespace App\Support;

/**
 * Canonical permission names and the role -> permission matrix
 * for the School Report Portal.
 */
final class Permissions
{
    // School setup (years, terms, classes, subjects)
    public const SETUP_MANAGE = 'setup.manage';

    // Users & roles
    public const USERS_MANAGE = 'users.manage';

    // People
    public const STUDENTS_MANAGE = 'students.manage';

    // Teachers may add and edit students in the classes they teach
    public const STUDENTS_ENROL = 'students.enrol';

    public const GUARDIANS_MANAGE = 'guardians.manage';

    // Marks
    public const MARKS_ENTER = 'marks.enter';

    public const MARKS_VIEW = 'marks.view';

    // Report compilation & workflow
    public const REPORTS_COMPILE = 'reports.compile';

    public const REPORTS_APPROVE = 'reports.approve';

    public const REPORTS_PUBLISH = 'reports.publish';

    // Portals
    public const PORTAL_REPORTS_VIEW = 'portal.reports.view';

    // Admin utilities
    public const DASHBOARDS_ADMIN = 'dashboards.admin';

    public const IMPORT_EXPORT = 'import.export';

    public const AUDIT_LOG = 'audit.log';

    public const BACKUPS_MANAGE = 'backups.manage';

    /** @var list<string> */
    public const ALL = [
        self::SETUP_MANAGE,
        self::USERS_MANAGE,
        self::STUDENTS_MANAGE,
        self::STUDENTS_ENROL,
        self::GUARDIANS_MANAGE,
        self::MARKS_ENTER,
        self::MARKS_VIEW,
        self::REPORTS_COMPILE,
        self::REPORTS_APPROVE,
        self::REPORTS_PUBLISH,
        self::PORTAL_REPORTS_VIEW,
        self::DASHBOARDS_ADMIN,
        self::IMPORT_EXPORT,
        self::AUDIT_LOG,
        self::BACKUPS_MANAGE,
    ];

    /**
     * Role => permissions the role holds by default.
     *
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        return [
            Roles::ADMIN => self::ALL,

            Roles::TEACHER => [
                self::MARKS_ENTER,
                self::MARKS_VIEW,
                self::STUDENTS_ENROL,
                self::REPORTS_COMPILE,
            ],

            Roles::GUARDIAN => [
                self::PORTAL_REPORTS_VIEW,
            ],

            Roles::STUDENT => [
                self::PORTAL_REPORTS_VIEW,
            ],
        ];
    }
}
