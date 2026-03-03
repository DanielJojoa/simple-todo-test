# Simple Todo App (PHP + PostgreSQL)

## Requirements
- PHP 8.0+
- PostgreSQL
- PHP `pdo_pgsql` extension enabled

## Setup
1. Create a PostgreSQL database:
   - Example: `todo_app`
2. Update `.env` values if needed.
3. Run schema:
   - `psql -U postgres -d todo_app -f database/init.sql`

## Run
```bash
php -S localhost:8000 -t public
```

Open `http://localhost:8000`.

