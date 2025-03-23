# Symfony Docker Demo Project

A demonstration project showcasing PHP 8.4, Symfony 7.x, and PostgreSQL 15.4 in a Docker environment using the Symfony CLI.

## Technologies & Versions

This project uses the following specific versions to ensure consistency across environments:

- **PHP**: 8.4-fpm
- **Composer**: 2.7.1
- **PostgreSQL**: 15.4
- **Nginx**: 1.25.3
- **Symfony CLI**: Latest stable version
- **Symfony**: 7.x (installed via Symfony CLI)

## Setup Instructions

### Development Environment

1. Clone this repository
2. Run `docker-compose up -d`
3. Create Symfony project using the Symfony CLI:
   ```bash
   docker-compose exec php symfony new --webapp .