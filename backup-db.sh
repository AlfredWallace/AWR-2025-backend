#!/bin/bash
# Backup PostgreSQL database
docker-compose exec -T postgres pg_dump -U symfony symfony > backup_$(date +%Y-%m-%d_%H-%M-%S).sql