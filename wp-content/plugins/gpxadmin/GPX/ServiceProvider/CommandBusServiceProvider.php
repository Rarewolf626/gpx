<?php

namespace GPX\ServiceProvider;

use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use League\Container\ServiceProvider\AbstractServiceProvider;

class CommandBusServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            Dispatcher::class,
            QueueingDispatcher::class,
            'Illuminate\Contracts\Bus\Dispatcher',
            'Illuminate\Contracts\Bus\QueueingDispatcher',
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(Dispatcher::class, function () {
            $queue = $this->getContainer()->get('Illuminate\Queue\Capsule\Manager');

            return new Dispatcher($this->getContainer()->get('Illuminate\Contracts\Container\Container'), function ($connection = null) use ($queue) {
                return $queue->connection($connection);
            });
        });

        $this->getContainer()->add('Illuminate\Contracts\Bus\Dispatcher', function () {
            return $this->getContainer()->get(Dispatcher::class);
        });

        $this->getContainer()->add(QueueingDispatcher::class, function () {
            return $this->getContainer()->get(Dispatcher::class);
        });

        $this->getContainer()->add('Illuminate\Contracts\Bus\QueueingDispatcher', function () {
            return $this->getContainer()->get(Dispatcher::class);
        });
    }
}
