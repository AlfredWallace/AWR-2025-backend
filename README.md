# Alfred Wallace Rugby (AWR) - Backend

A Symfony-based API for simulating rugby union national team matches and tournaments.

## Project Overview

AWR allows users to:
- Create and manage rugby match simulations
- Track team performance across simulations
- Simulate matches with various conditions (neutral ground, World Cup, etc.)
- View team rankings and statistics

## Technologies

- **PHP**: 8.4-fpm-alpine
- **Symfony**: 7.x
- **PostgreSQL**: 15-alpine
- **Nginx**: Alpine
- **JWT Authentication**: For secure API access
- **Docker & Docker Compose**: For containerized deployment

## Requirements

- Docker and Docker Compose
- Git
- Make (optional, but recommended)

## Quick Start

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd awr
   ```

2. Run the installation script:
   ```bash
   make install
   ```
   This will 
   - Start the PHP, Nginx, and PostgreSQL containers (up -d)
   - Install the application dependencies (composer install)
   - Create the database and tables (run migrations)
   - Generate JWT keys (lexik bundle)
   - Create an admin user (credentials: admin/admin)

3. Access the application at http://localhost:8888 and the doc at http://localhost:8888/api/doc

## Docker Setup

The project uses Docker Compose with the following services:

- **Web Server**: Nginx running on port 8888
- **PHP**: PHP-FPM 8.4 with essential extensions for Symfony
- **Database**: PostgreSQL 15 running on port 5432

## Available Commands

The project includes a Makefile with the following commands:

- `make up`: Start the Docker containers
- `make down`: Stop the Docker containers
- `make test`: Run the PHPUnit tests
- `make validate-schema`: Validate the Doctrine schema
- `make backup-db`: Create a database backup
- `make migration`: Create a new migration
- `make migrate`: Run migrations
- `make install`: Run the installation script`

## Database Configuration

The database is configured with the following settings:

- **Host**: postgre (within Docker network) or localhost (from host)
- **Port**: 5432
- **Username**: postgre_user
- **Password**: postgre_pass
- **Database**: postgre_dev (for development) or postgre_test (for testing)

## Testing

The project uses PHPUnit for testing. Tests are organized into:

- **Unit Tests**: Testing the ranking algorithm alone
- **Smoke Tests**: Checking 200 on GET routes and 401 on protected routes
- **End 2 End Tests**: Testing a complete process, from user login to simulation results 
- **Controller Tests**: Testing user registration

Run tests with:
```bash
make test
```

## API Authentication

The API uses JWT authentication. JWT keys are included in the repository at `symfony/config/jwt/`.

To authenticate:
1. Send a POST request to `/api/login_check` with username and password
2. Use the returned JWT token in the Authorization header for subsequent requests:
   ```
   Authorization: Bearer <token>
   ```

## External Integrations

The application integrates with the World Rugby API to fetch team rankings:
- API URL: https://api.wr-rims-prod.pulselive.com/rugby/v3/rankings/mru

## Development Notes

- Environment variables are configured in `.env` and can be overridden in `.env.local`
- The development environment uses PHP's development configuration

## Troubleshooting

- If you encounter database connection issues, ensure the PostgreSQL container is running and healthy
- For permission issues with the `var` directory, the container automatically sets permissions on startup
- JWT authentication issues may require regenerating the JWT keys
