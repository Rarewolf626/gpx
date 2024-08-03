<?php

use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use League\Container\Container;
use GPX\Container\LaravelContainer;
use Illuminate\Http\RedirectResponse;

/**
 * @param ?string $key
 * @param array $args
 *
 * @return Container|array|mixed|object
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function gpx(string $key = null, array $args = []) {
    static $container;
    if (!$container) {
        global $wpdb;
        $container = new League\Container\Container();
        $laravel_container = new LaravelContainer($container);
        Illuminate\Container\Container::setInstance($laravel_container);
        $container->delegate(
            new League\Container\ReflectionContainer()
        );
        $container->add('League\Container\Container', $container);
        $container->add('Psr\Container\ContainerInterface', $container);
        $container->add('Illuminate\Container\Container', $laravel_container);
        $container->add('Illuminate\Contracts\Container\Container', $laravel_container);
        $container->add('Illuminate\Contracts\Foundation\Application', $laravel_container);
        $container->add(wpdb::class, $wpdb);
        $container->add('config', new GPX\Container\Config());

        // Add any service providers here
        $container->addServiceProvider(new GPX\ServiceProvider\HttpServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\EventServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\DatabaseServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\ValidationServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\TranslationServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\LoggerServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\GeocodeServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\TripAdvisorServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\SalesforceServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\QueueServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\CommandBusServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\ConsoleServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\FilesystemServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\AdminRouterServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\PaginationServiceProvider());
        $container->addServiceProvider(new GPX\ServiceProvider\ViewServiceProvider());
    }
    if (null === $key) {
        return $container;
    }

    return $container->get($key, $args);
}

/**
 * @param ?string $key
 * @param mixed $default
 *
 * @return mixed|Request
 */
function gpx_request(string $key = null, $default = null): mixed {
    /** @var Request $request */
    $request = gpx(Request::class);
    if (is_null($key)) {
        return $request;
    }

    return $request->get($key, $default);
}

function gpx_response(?string $content = '', int $status = 200, array $headers = [], bool $send = true): ?BaseResponse {
    $response = new Response($content, $status, $headers);
    if ($send) {
        gpx_send_response($response);
        return null;
    }

    return $response;
}

function gpx_redirect(string $url, int $status = 302, array $headers = []) {
    return gpx_send_response(new RedirectResponse($url, $status, $headers));
}

function gpx_send_response(BaseResponse $response, bool $exit = true) {
    $response->prepare(gpx_request());
    $response->send();
    if ($exit) {
        exit;
    }

    return $response;
}

function gpx_db(): Connection {
    return gpx(Connection::class);
}

/**
 * @param string|object $event
 * @param mixed $payload
 * @param bool $halt
 *
 * @return array|\Illuminate\Events\Dispatcher|null
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function gpx_event($event = null, $payload = [], bool $halt = false) {
    /** @var Illuminate\Events\Dispatcher $dispatcher */
    $dispatcher = gpx('Illuminate\Events\Dispatcher');
    if (null === $event) {
        return $dispatcher;
    }

    return $dispatcher->dispatch($event, $payload, $halt);
}

/**
 * Executes the given command and optionally returns a value
 *
 * @param object|string $command
 *
 * @return mixed
 */
function gpx_dispatch($command) {
    /** @var \Illuminate\Bus\Dispatcher $dispatcher */
    $dispatcher = gpx('Illuminate\Bus\Dispatcher');
    if (is_string($command)) {
        $command = gpx($command);
    }
    try {
        $response = $dispatcher->dispatch($command);
    } catch (Exception $e) {
        return null;
    }
    return $response;
}

function gpx_logger(): LoggerInterface {
    static $logger;
    if (!$logger) {
        $logger = gpx('logger');
    }

    return $logger;
}

/**
 * @param string|array|\Symfony\Component\Console\Input\InputInterface $input The command to run, or array with
 *                                                                             command and arguments/options, or input
 *                                                                             instance
 * @param bool|\Symfony\Component\Console\Output\OutputInterface $output boolean true to return output as string,
 *                                                                             otherwise returns exit code, also can be
 *                                                                             output instance
 * @param bool $html if true returned output will be
 *                                                                             converted to html
 *
 * @return int|string
 */
function gpx_run_command(
    $input,
    $output = false,
    bool $html = false
) {
    /** @var \Symfony\Component\Console\Application $application */
    $application = gpx(\Symfony\Component\Console\Application::class);
    $application->setAutoExit(false);
    if (is_string($input)) {
        $input = ['command' => $input];
    }
    if (is_array($input)) {
        if (!array_key_exists('command', $input)) {
            throw new InvalidArgumentException('Command name not specified');
        }
        $input = new \Symfony\Component\Console\Input\ArrayInput($input);
    }
    if (!($input instanceof \Symfony\Component\Console\Input\InputInterface)) {
        throw new InvalidArgumentException('Invalid input');
    }
    if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
        $out = $output;
    } elseif ($output) {
        $out = new \Symfony\Component\Console\Output\BufferedOutput(
            \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL,
            $html
        );
    } else {
        $out = new \Symfony\Component\Console\Output\NullOutput();
    }
    $dir = getcwd();
    chdir(ABSPATH);
    $status = $application->run($input, $out);
    chdir($dir);
    if ($output !== true) {
        return $status;
    }
    $content = $out->fetch();
    if (!$html) {
        return $content;
    }
    $converter = new \SensioLabs\AnsiConverter\AnsiToHtmlConverter();

    return $converter->convert($content);
}

/**
 * @return Illuminate\Contracts\Validation\Factory
 */
function gpx_validator(): \Illuminate\Contracts\Validation\Factory {
    /** @var Illuminate\Contracts\Validation\Factory $dispatcher */
    return gpx('Illuminate\Contracts\Validation\Factory');
}
