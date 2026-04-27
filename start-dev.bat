@echo off

echo Starting Laravel backend...

start "Laravel Backend" cmd /k "php artisan migrate && php artisan migrate:fresh --seed && php artisan serve"

echo Starting Frontend...

start "Frontend" cmd /k "npm run dev"

echo Done.