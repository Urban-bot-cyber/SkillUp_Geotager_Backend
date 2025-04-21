#!/bin/sh
# entrypoint.sh

echo "Clearing Laravel Caches..."
php artisan package:discover --force
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear      # Clear event cache
php artisan optimize:clear   # General command to clear all compiled files (config, route, view etc.)

# Optional: Regenerate autoloader just in case (usually not needed if build was correct)
# echo "Generating optimized autoload files..."
# composer dump-autoload --optimize --no-dev

echo "Running database migrations..."
php artisan migrate --force --no-interaction # Added --no-interaction

# Any other startup tasks (e.g., queue worker setup if needed)

echo "Starting Apache..."
# Execute the CMD from the Dockerfile (apache2-foreground)
exec "$@"