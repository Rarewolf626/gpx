<?php

namespace GPX\ServiceProvider;

use GPX\Api\TripAdvisor\TripAdvisor;
use League\Container\ServiceProvider\AbstractServiceProvider;

class TripAdvisorServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, [
            TripAdvisor::class,
            'tripadvisor',
        ]);
    }

    public function register(): void
    {
        $this->getContainer()->addShared(
            TripAdvisor::class, function () {
            return new TripAdvisor(GPX_TRIP_ADVISOR_API_KEY);
        });

        $this->getContainer()->add('tripadvisor', function () {
            return $this->getContainer()->get(TripAdvisor::class);
        });
    }
}
