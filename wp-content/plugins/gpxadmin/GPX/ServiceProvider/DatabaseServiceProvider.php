<?php

namespace GPX\ServiceProvider;

use mysqli;
use ReflectionObject;
use GPX\Database\WpdbDriver;
use Doctrine\DBAL\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Database\Capsule\Manager as Capsule;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class DatabaseServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

    public function provides( string $id ): bool {
        return in_array( $id, [
            Connection::class,
            'dbh',
            mysqli::class,
            Capsule::class,
            DatabaseManager::class,
            DatabasePresenceVerifier::class,
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared( mysqli::class, function () {
            global $wpdb;
            $r = new ReflectionObject( $wpdb );
            $p = $r->getProperty( 'dbh' );
            $p->setAccessible( true );

            return $p->getValue( $wpdb );
        } );

        $this->getContainer()->addShared( Connection::class, function () {
            $connectionParams = [
                'dbname' => DB_NAME,
                'user' => DB_USER,
                'password' => DB_PASSWORD,
                'host' => DB_HOST,
                'driverClass' => WpdbDriver::class,
            ];
            return \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        } );
        $this->getContainer()->add( 'dbh', fn() => $this->getContainer()->get(Connection::class) );

        $this->getContainer()->addShared(
            Capsule::class,
            function () {
                $capsule = new Capsule;
                $capsule->addConnection(
                    [
                        'driver'    => 'mysql',
                        'host'      => DB_HOST,
                        'database'  => DB_NAME,
                        'username'  => DB_USER,
                        'password'  => DB_PASSWORD,
                        'charset'   => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix'    => '',
                        'strict' => false,
                        'modes' => ['NO_ZERO_IN_DATE','ERROR_FOR_DIVISION_BY_ZERO','NO_ENGINE_SUBSTITUTION'],
                    ],
                    'default'
                );
                // Register Database Event Listeners
                $capsule->setEventDispatcher($this->getContainer()->get('Illuminate\Events\Dispatcher'));

                // Make this Capsule instance available globally via static methods... (optional)
                $capsule->setAsGlobal();

                // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
                $capsule->bootEloquent();

                return $capsule;
            }
        );

        $this->getContainer()->addShared(
            DatabaseManager::class, function () {
            return $this->getContainer()->get(Capsule::class)->getDatabaseManager();
        }
        );

        $this->getContainer()->addShared(
            DatabasePresenceVerifier::class, function () {
            return new DatabasePresenceVerifier($this->getContainer()->get(DatabaseManager::class));
        }
        );
    }

    public function boot(): void {
        class_alias(Capsule::class, 'DB');
    }
}
