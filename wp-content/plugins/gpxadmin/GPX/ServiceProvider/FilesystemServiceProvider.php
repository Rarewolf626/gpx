<?php

namespace GPX\ServiceProvider;

use Illuminate\Filesystem\Filesystem;
use League\Container\ServiceProvider\AbstractServiceProvider;

class FilesystemServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            'filesystem',
            Filesystem::class
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared( Filesystem::class, fn() => new Filesystem());
        $this->getContainer()->add('filesystem', fn() => $this->getContainer()->get(Filesystem::class));
    }
}
