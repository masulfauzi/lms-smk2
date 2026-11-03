@echo off
REM Setup LMS PJJ Sekolah untuk Windows (XAMPP / Laragon). Jalankan dari folder project.
REM Pastikan MySQL sudah menyala dan database "lms_pjj" sudah dibuat di phpMyAdmin.
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 (
  if exist "C:\xampp\php\php.exe" (set PHP=C:\xampp\php\php.exe) else (echo PHP tidak ditemukan. Jalankan dari "Shell" XAMPP. & pause & exit /b 1)
) else (set PHP=php)
if not exist "vendor\autoload.php" (
  echo Menginstal dependency dengan Composer...
  call composer install --no-dev --optimize-autoloader --no-interaction
  if errorlevel 1 (echo Composer gagal / belum terpasang. Unduh di https://getcomposer.org & pause & exit /b 1)
)
for %%D in (storage\framework\sessions storage\framework\views storage\framework\cache\data storage\app\private storage\logs bootstrap\cache) do if not exist "%%D" mkdir "%%D"
if not exist ".env" copy ".env.example" ".env" >nul
%PHP% artisan key:generate --force
%PHP% artisan migrate:fresh --seed --force
%PHP% artisan storage:link
%PHP% artisan optimize:clear
echo.
echo Selesai. Buka http://localhost/lms-pjj
echo Login admin: admin / admin123   guru: guru / guru123   siswa: siswa / siswa123
pause
