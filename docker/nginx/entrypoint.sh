#!/bin/sh

set -e

echo "=============================================="
echo "        LOCKER - LARAVEL STARTUP"
echo "=============================================="

# =========================================================
# WAIT FOR DATABASE
# =========================================================

echo "[1/6] Checking database connection..."

MAX_RETRIES=30
RETRY_COUNT=0

until php -r '
try {
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT") ?: "3306";
    $database = getenv("DB_DATABASE");
    $username = getenv("DB_USERNAME");
    $password = getenv("DB_PASSWORD");

    $dsn = "mysql:host={$host};port={$port};dbname={$database}";

    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    exit(0);

} catch (Throwable $e) {
    exit(1);
}
'; do

    RETRY_COUNT=$((RETRY_COUNT + 1))

    if [ "$RETRY_COUNT" -ge "$MAX_RETRIES" ]; then
        echo "ERROR: Database connection timeout."
        exit 1
    fi

    echo "Database not ready. Retrying... ($RETRY_COUNT/$MAX_RETRIES)"

    sleep 2
done

echo "Database connection successful."


# =========================================================
# DATABASE MIGRATION
# =========================================================

echo "[2/6] Running Laravel migrations..."

php artisan migrate --force

echo "Migration completed."


# =========================================================
# STORAGE DIRECTORIES
# =========================================================

echo "[3/6] Preparing Laravel storage..."

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache

echo "Storage prepared."


# =========================================================
# CLEAR OLD CACHE
# =========================================================

echo "[4/6] Clearing Laravel cache..."

php artisan optimize:clear

echo "Laravel cache cleared."


# =========================================================
# CACHE PRODUCTION CONFIGURATION
# =========================================================

echo "[5/6] Caching Laravel configuration..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Laravel cache generated."


# =========================================================
# START PHP-FPM
# =========================================================

echo "[6/6] Starting PHP-FPM..."

echo "=============================================="
echo "        LOCKER IS READY"
echo "=============================================="

exec "$@"