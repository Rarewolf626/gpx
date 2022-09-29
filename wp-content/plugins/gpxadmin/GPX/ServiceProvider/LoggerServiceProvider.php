<?php

namespace GPX\ServiceProvider;

use Illuminate\Log\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Logger as Monolog;
use Illuminate\Events\Dispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LoggerServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            Monolog::class,
            Logger::class,
            LoggerInterface::class,
            'logger',
            'logger.output'
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            Monolog::class, function () {
            $monolog = new Monolog( 'GPX Vacations' );
            $handler = new StreamHandler(
                WP_CONTENT_DIR  . '/logs/gpx.log',
                Monolog::DEBUG
            );
            $formatter = new LineFormatter( null, null, false, true );
            $formatter->includeStacktraces( true );
            $handler->setFormatter( $formatter );
            $monolog->pushHandler( $handler );

            $webprocessor = new WebProcessor();
            $monolog->pushProcessor( $webprocessor );

            $introspectionProcessor = new IntrospectionProcessor( Monolog::ERROR, [ 'Illuminate\\', 'Monolog\\', 'Symfony\\' ] );
            $monolog->pushProcessor( $introspectionProcessor );

            $monolog->pushProcessor(
                function ( $record ) {
                    $cid = get_current_user_id();
                    if ( $cid ) {
                        $record['extra']['user_id'] = $cid;
                    }
                    $as = gpx_get_switch_user_cookie();
                    if ( $as != $cid ) {
                        $record['extra']['acting_as'] = $as;
                    }

                    return $record;
                }
            );

            return $monolog;
        }
        );

        $this->getContainer()->add( LoggerInterface::class, function () {
            return $this->getContainer()->get( Logger::class );
        } );

        $this->getContainer()->addShared( Logger::class, function () {
            return new Logger(
                $this->getContainer()->get( Monolog::class ),
                $this->getContainer()->get( Dispatcher::class )
            );
        } );

        $this->getContainer()->add( 'logger', function () {
            return $this->getContainer()->get( Logger::class );
        } );


        $this->getContainer()->add( 'logger.output', function () {
            $monolog = new Monolog( 'GPX Vacations' );
            $handler = new StreamHandler( 'php://stdout', Monolog::DEBUG );
            $formatter = new LineFormatter( null, null, true, true );
            $formatter->includeStacktraces( true );
            $handler->setFormatter( $formatter );
            $monolog->pushHandler( $handler );

            return $monolog;
        } );
    }
}
