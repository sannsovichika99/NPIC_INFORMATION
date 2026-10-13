RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

php artisan migrate --force

php artisan storage:link --force

composer i

npm i

npm run dev &

apache2-foreground
