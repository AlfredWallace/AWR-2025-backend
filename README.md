# Alfred Wallace Rugby - backend

Simulator for rugby union national teams

## Technologies & Versions

This project uses the following technologies:

- **PHP**: 8.4-fpm-alpine
- **Composer**: Latest stable version
- **PostgreSQL**: 15-alpine
- **Nginx**: Alpine
- **Symfony**: 7.x

## Docker Setup

This project uses a simplified Docker setup for local development:

- **Web Server**: Nginx running on port 8888
- **PHP**: PHP-FPM 8.4 with essential extensions for Symfony
- **Database**: PostgreSQL 15 running on port 5432

## Setup Instructions

### Development Environment

1. Clone this repository
2. Run `./up.sh` to start the Docker containers
3. Initiate the database with `docker-compose exec php-fpm php bin/console doctrine:migrations:migrate`
4. Access the application at http://localhost:8888
5. Run tests with `./test-the-app.sh`
6. Create a database backup with `./backup-db.sh`
7. Stop the containers with `./down.sh`

## Database Connection

The database is accessible at:
- Host: localhost
- Port: 5432
- Username: app
- Password: app
- Database: app
