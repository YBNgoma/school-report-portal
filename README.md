# School Report Portal

A web portal for a school to manage academic years, classes, students, guardians, marks and term reports, with a parent portal for published reports.

## Running with Docker

1. Copy the environment file and start the containers:

   ```bash
   cp .env.example .env
   docker compose up -d
   ```

2. Run the migrations and demo seeders:

   ```bash
   docker compose exec app php artisan migrate --seed
   ```

3. Open http://localhost:8080 (change the port with `APP_PORT` in `.env`).

## Demo logins

The seeder creates one user per role. All of them use the password `password`.

| Role     | Email                 | What they can do                                    |
|----------|-----------------------|-----------------------------------------------------|
| Admin    | `admin@example.com`   | Everything, including setup, audit log and exports  |
| Teacher  | `teacher@example.com` | Enter marks, view marks, compile reports            |
| Guardian | `guardian@example.com`| View published reports for linked children          |
| Student  | `student@example.com` | View published reports (no link to a student record yet) |

The seeder also adds demo setup data: academic year 2026/2027 with three terms, two classes (Grade 7A and Grade 8A) and four subjects.

## Running tests

```bash
docker compose exec -T app composer test:docker
```

Use this command rather than `php artisan test` inside the container. The Docker environment variables would otherwise override the test database settings in `phpunit.xml`.

## Backups

The app runs `php artisan backup:database` every day, using `pg_dump`. Dumps go to `storage/app/backups`. Restore one with `pg_restore`. Dumps stay on the server, so copy them off it yourself.
