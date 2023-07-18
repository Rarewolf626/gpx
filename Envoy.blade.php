@servers(['live' => 'gpx@45.79.87.155', 'staging' => 'gpx@104.237.156.135'])

@task('deploy', ['on' => 'live'])
cd /home/gpx/gpxvacations.com/www/html
git pull
@if($full)
    php composer.phar self-update
    php composer.phar install --no-dev --optimize-autoloader
@endif
@endtask

@task('stage', ['on' => 'staging'])
cd /home/gpx/my-gpx.com/www/html
git pull
@if($full)
    php composer.phar self-update
    php composer.phar install
@endif
@endtask


@task('php8', ['on' => 'staging'])
cd /home/gpx/my-gpx.com/www/gpx8
git pull
@if($full)
    php8.0 composer.phar self-update
    php8.0 composer.phar install
@endif
@endtask