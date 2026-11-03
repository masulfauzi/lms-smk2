#!/usr/bin/env bash
# Setup LMS PJJ Sekolah untuk Linux / macOS. Pastikan MySQL menyala dan database "lms_pjj" sudah dibuat.
set -e
cd "$(dirname "$0")"
if [ ! -f vendor/autoload.php ]; then
  command -v composer >/dev/null || { echo "Composer belum terpasang: https://getcomposer.org"; exit 1; }
  composer install --no-dev --optimize-autoloader --no-interaction
fi
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/app/private storage/logs bootstrap/cache
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
php artisan migrate:fresh --seed --force
php artisan storage:link || true
php artisan optimize:clear
chmod -R ug+rw storage bootstrap/cache
echo "Selesai. Jalankan: php artisan serve  lalu buka http://127.0.0.1:8000"
echo "Login admin: admin / admin123   guru: guru / guru123   siswa: siswa / siswa123"
