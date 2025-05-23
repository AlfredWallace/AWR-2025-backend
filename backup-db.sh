#!/bin/bash
# Create a database backup
docker compose exec -T database pg_dump -U app app > backup_$(date +%Y-%m-%d_%H-%M-%S).sql
