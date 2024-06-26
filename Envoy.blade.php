@servers(['live' => 'gpx@45.79.87.155', 'staging' => 'gpx@104.237.156.135'])

@task('deploy', ['on' => 'live'])
cd /home/gpx/gpxvacations.com/www/html
git pull
@if($full)
    php8.0 composer.phar self-update
    php8.0 composer.phar install --no-dev --optimize-autoloader
@endif
php8.0 console cache:clear:view
@endtask

@task('stage', ['on' => 'staging'])
cd /home/gpx/my-gpx.com/www/html
git pull
@if($full)
    php8.0 composer.phar self-update
    php8.0 composer.phar install
@endif
php8.0 console cache:clear:view
@endtask


@task('dev', ['on' => 'staging'])
cd /home/gpx/my-gpx.com/www/gpxtest
git pull
@if($full)
    php8.0 composer.phar self-update
    php8.0 composer.phar install
@endif
php8.0 console cache:clear:view
@endtask
