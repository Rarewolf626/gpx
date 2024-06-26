<?php

namespace GPX\ServiceProvider;

use Illuminate\Support\HtmlString;
use Illuminate\View\DynamicComponent;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\ViewFinderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
use League\Container\Argument\Literal;

class ViewServiceProvider extends AbstractServiceProvider {
    public function provides(string $id): bool {
        return in_array($id, [
            'view',
            FileViewFinder::class,
            ViewFinderInterface::class,
            FactoryContract::class,
            ViewFactory::class,
            EngineResolver::class,
            CompilerEngine::class,
            BladeCompiler::class,
            PhpEngine::class,
            FileEngine::class,
        ]);
    }

    public function register(): void {
        $this->getContainer()->addShared(
            ViewFactory::class, fn() => new ViewFactory(
            $this->getContainer()->get(EngineResolver::class),
            $this->getContainer()->get(ViewFinderInterface::class),
            $this->getContainer()->get(Dispatcher::class),
        ));
        $this->getContainer()->add(FactoryContract::class, fn() => $this->getContainer()->get(ViewFactory::class));
        $this->getContainer()->add('view', fn() => $this->getContainer()->get(FactoryContract::class));

        $this->getContainer()->addShared(FileViewFinder::class)
             ->addArgument(Filesystem::class)
             ->addArgument(new Literal\ArrayArgument([GPXADMIN_PLUGIN_DIR . '/templates']))
             ->addArgument(new Literal\ArrayArgument(['blade.php', 'php', 'css', 'html']))
             ->addMethodCall('addNamespace', ['pagination', GPXADMIN_PLUGIN_DIR . '/templates/pagination'])
             ->addMethodCall('addNamespace', ['admin', GPXADMIN_PLUGIN_DIR . '/templates/admin'])
             ->addMethodCall('addNamespace', ['theme', GPXADMIN_THEME_DIR . '/templates'])
             ->addMethodCall('addNamespace', ['partial', GPXADMIN_THEME_DIR . '/template-parts']);

        $this->getContainer()->add(ViewFinderInterface::class, fn() => $this->getContainer()->get(FileViewFinder::class));


        $this->getContainer()->addShared(
            EngineResolver::class, function () {
            $resolver = new EngineResolver();
            $resolver->register('file', fn() => $this->getContainer()->get(FileEngine::class));
            $resolver->register('php', fn() => $this->getContainer()->get(PhpEngine::class));
            $resolver->register('blade', fn() => $this->getContainer()->get(CompilerEngine::class));

            return $resolver;
        });

        $this->getContainer()->addShared(
            CompilerEngine::class, fn() => new CompilerEngine(
            $this->getContainer()->get(BladeCompiler::class),
            $this->getContainer()->get(Filesystem::class),
        ));

        $this->getContainer()->addShared(
            BladeCompiler::class, function () {
            $blade = new BladeCompiler(
                $this->getContainer()->get(Filesystem::class),
                WP_CONTENT_DIR . '/gpx-cache/view',
                '', true, 'php'
            );
            $blade->setEchoFormat('esc_html(%s)');
            $blade->component('dynamic-component', DynamicComponent::class);
            $blade->directive('attr', function ($expression) {
                return new HtmlString("<?php echo esc_attr( $expression ); ?>");
            });

            return $blade;
        });

        $this->getContainer()->addShared(PhpEngine::class, fn() => new PhpEngine($this->getContainer()->get(Filesystem::class)));
        $this->getContainer()->addShared(FileEngine::class, fn() => new FileEngine($this->getContainer()->get(Filesystem::class)));
    }
}
