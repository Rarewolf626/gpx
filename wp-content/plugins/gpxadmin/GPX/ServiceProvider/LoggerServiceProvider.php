<?php

namespace GPX\ServiceProvider;

use Illuminate\Log\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Logger as Monolog;
use Illuminate\Events\Dispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\GitProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LoggerServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            Monolog::class,
            Logger::class,
            LoggerInterface::class,
            'logger',
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            Monolog::class, function () {
            $monolog   = new Monolog( 'GPX Vacations' );
            $handler   = new StreamHandler( WP_CONTENT_DIR . '/logs/gpx.log', Monolog::DEBUG );
            $formatter = new LineFormatter();
            $formatter->includeStacktraces( false );
            $formatter->allowInlineLineBreaks( false );
            $formatter->ignoreEmptyContextAndExtra( true );
            $handler->setFormatter( $formatter );
            $monolog->pushHandler( $handler );

            $errorHandler   = new StreamHandler( WP_CONTENT_DIR . '/logs/error.log', Monolog::ERROR, false );
            $errorFormatter = new LineFormatter();
            $errorFormatter->includeStacktraces( true );
            $errorFormatter->allowInlineLineBreaks( true );
            $errorFormatter->ignoreEmptyContextAndExtra( true );
            $errorHandler->setFormatter( $errorFormatter );
            $monolog->pushHandler( $errorHandler );

            $monolog->pushProcessor( new WebProcessor() );
            $monolog->pushProcessor( new MemoryUsageProcessor() );
            $monolog->pushProcessor( new MemoryPeakUsageProcessor() );
            $monolog->pushProcessor( new GitProcessor() );
            $monolog->pushProcessor( new IntrospectionProcessor( Monolog::ERROR, [ 'Illuminate\\', 'Monolog\\', 'Symfony\\' ] ) );

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
    }
}
