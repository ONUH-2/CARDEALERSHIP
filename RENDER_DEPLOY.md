# Deploying CARDEALERSHIP on Render

The repository is configured as a Docker-based Render web service. Render supports Docker deployments and requires the web process to bind to the `PORT` environment variable on `0.0.0.0`.

## Important database note

This project uses PHP with MySQLi. Render’s managed relational database is PostgreSQL, so the application needs a separate MySQL-compatible provider unless the application is migrated from MySQLi to PostgreSQL. Do not place database passwords in GitHub. Add them as Render environment variables.

## Render deployment

1. Open Render and choose **New → Blueprint**.
2. Select the `ONUH-2/CARDEALERSHIP` repository and the `main` branch.
3. Render will detect `render.yaml` and create the Docker web service named `honus-autos`.
4. Add the MySQL connection values under the service environment variables: `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, and `DB_NAME`.
5. Import `database/schema.sql` into the MySQL database before testing signup and login.
6. Deploy the service and open the generated `onrender.com` URL.

The health check is configured for `/cardealership/login/login.php`. The service listens on Render’s `PORT` value through Apache, while the application reads database credentials from environment variables.

## Required environment variables

| Variable | Example | Purpose |
|---|---|---|
| `DB_HOST` | `your-mysql-host.example.com` | MySQL server hostname |
| `DB_PORT` | `3306` | MySQL server port |
| `DB_USER` | `cardealership_app` | Database username |
| `DB_PASS` | Render secret value | Database password |
| `DB_NAME` | `cardealership` | Database name |

For production, add HTTPS-only cookies, CSRF protection, rate limiting, email verification, and a staff/admin access policy before using the site with real customer data.
