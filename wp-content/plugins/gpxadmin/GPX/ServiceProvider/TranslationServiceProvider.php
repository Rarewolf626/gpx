<?php

namespace GPX\ServiceProvider;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use  \Illuminate\Contracts\Translation\Translator as TranslatorContract;
use League\Container\ServiceProvider\AbstractServiceProvider;

class TranslationServiceProvider extends AbstractServiceProvider
{
    public function provides( string $id ): bool {
        return in_array( $id, [
            Translator::class,
            TranslatorContract::class
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            Translator::class, function () {
            $loader = new FileLoader(new Filesystem, GPXADMIN_DIR . '/messages');
            // Specify the translation namespace
            $loader->addNamespace('lang', GPXADMIN_DIR . '/messages');
            // This is used to create the path to your validation.php file
            $loader->load($lang = 'en', $group = 'validation', $namespace = 'lang');
            return new Translator($loader, 'en');
        }
        );

        $this->getContainer()->add(
            TranslatorContract::class, function () {
            return $this->getContainer()->get(Translator::class);
        }
        );
    }
}
