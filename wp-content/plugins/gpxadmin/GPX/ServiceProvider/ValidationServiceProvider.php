<?php

namespace GPX\ServiceProvider;

use GPX\Container\LaravelContainer;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Contracts\Validation\Factory as ValidatorContract;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ValidationServiceProvider extends AbstractServiceProvider
{
    public function provides( string $id ): bool {
        return in_array( $id, [
            'validator',
            Validator::class,
            ValidatorContract::class
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            Validator::class, function () {
            $factory = new Validator(
                $this->getContainer()->get(Translator::class),
                $this->getContainer()->get(LaravelContainer::class)
            );
            $factory->setPresenceVerifier($this->getContainer()->get(DatabasePresenceVerifier::class));
            return $factory;
        }
        );

        $this->getContainer()->add(
            ValidatorContract::class, function () {
            return $this->getContainer()->get(Validator::class);
        }
        );

        $this->getContainer()->add(
            'validator', function () {
            return $this->getContainer()->get(Validator::class);
        }
        );
    }
}
