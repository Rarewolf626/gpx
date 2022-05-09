<?php

namespace GPX\ServiceProvider;

use Illuminate\Events\Dispatcher;
use League\Container\ServiceProvider\AbstractServiceProvider;

class EventServiceProvider extends AbstractServiceProvider
{
    /**
     * Event listeners to register
     *
     * @var array
     */
    protected $events = [

    ];

    public function provides( string $id ): bool {
        return in_array( $id, [
            'event',
            'Illuminate\Events\Dispatcher',
            'Illuminate\Contracts\Events\Dispatcher',
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            'Illuminate\Events\Dispatcher',
            function () {
                $container  = $this->getContainer()->get('Illuminate\Container\Container');
                $dispatcher = new Dispatcher($container);
                foreach ($this->events as $event => $listeners) {
                    if (is_array($listeners)) {
                        foreach ($listeners as $listener) {
                            $dispatcher->listen($event, $listener);
                        }
                    } else {
                        $dispatcher->listen($event, $listeners);
                    }
                }

                return $dispatcher;
            }
        );

        $this->getContainer()->add(
            'Illuminate\Contracts\Events\Dispatcher',
            function () {
                return $this->getContainer()->get('Illuminate\Events\Dispatcher');
            }
        );

        $this->getContainer()->add(
            'event',
            function () {
                return $this->getContainer()->get('Illuminate\Events\Dispatcher');
            }
        );
    }
}
