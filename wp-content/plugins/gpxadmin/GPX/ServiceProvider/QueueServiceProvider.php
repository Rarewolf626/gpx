<?php

namespace GPX\ServiceProvider;

use Illuminate\Queue\Worker;
use GPX\Exception\ExceptionHandler;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Illuminate\Queue\Capsule\Manager as Queue;

class QueueServiceProvider extends AbstractServiceProvider {

    public function provides( string $id ): bool {
        return in_array( $id, [
            'Illuminate\Queue\Capsule\Manager',
            'Illuminate\Queue\Worker',
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            'Illuminate\Queue\Capsule\Manager',
            function () {
                $queue = new Queue( $this->getContainer()->get( 'Illuminate\Contracts\Container\Container' ) );
                $queue->addConnection( [ 'driver' => 'sync' ] );
                $queue->setAsGlobal();

                return $queue;
            }
        );

        $this->getContainer()->addShared(
            'Illuminate\Queue\Worker',
            function () {
                /** @var Queue $queue */
                $queue = $this->getContainer()->get( 'Illuminate\Queue\Capsule\Manager' );

                return new Worker(
                    $queue->getQueueManager(),
                    $this->getContainer()->get( 'Illuminate\Contracts\Events\Dispatcher' ),
                    $this->getContainer()->get( ExceptionHandler::class )
                );
            }
        );
    }
}
