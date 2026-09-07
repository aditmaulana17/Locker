#!/bin/sh

set -e

echo ""
echo "=============================================="
echo "          LOCKER APPLICATION STARTUP"
echo "=============================================="
echo ""

# =========================================================
# 1. DATABASE CONNECTION
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

    new PDO(
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
        echo ""
        echo "ERROR: Database connection timeout."
        echo ""
        exit 1
    fi

    echo "Database belum siap."
    echo "Retry $RETRY_COUNT/$MAX_RETRIES..."

    sleep 2

done

echo "Database connection successful."

# =========================================================
# 2. CLEAR OLD CACHE
# =========================================================

echo ""
echo "[2/6] Clearing Laravel cache..."

php artisan optimize:clear

echo "Laravel cache cleared."

# =========================================================
# 3. PACKAGE DISCOVERY
# =========================================================

echo ""
echo "[3/6] Discovering Laravel packages..."

php artisan package:discover --ansi

echo "Package discovery completed."

# =========================================================
# 4. DATABASE MIGRATION
# =========================================================

echo ""
echo "[4/6] Running database migrations..."

php artisan migrate --force

echo "Database migrations completed."

# =========================================================
# 5. STORAGE & PRODUCTION CACHE
# =========================================================

echo ""
echo "[5/6] Preparing Laravel storage..."

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

echo ""
echo "Building Laravel production cache..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Production cache generated."

# =========================================================
# 6. START PHP-FPM
# =========================================================

echo ""
echo "[6/6] Starting PHP-FPM..."

echo ""
echo "=============================================="
echo "             LOCKER IS READY"
echo "=============================================="
echo ""

exec "$@"