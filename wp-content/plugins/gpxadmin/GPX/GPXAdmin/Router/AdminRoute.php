<?php

namespace GPX\GPXAdmin\Router;

use function mb_strtoupper;

/**
 * @property-read string $page
 * @property-read mixed $callable
 * @property-read string[] $params
 */
class AdminRoute {
    private string $page;
    private bool $api = false;
    private mixed $callable;
    /** @var string[]  */
    private array $params;

    public function __construct( string $page, mixed $callable, array $params = [], bool $api = false ) {
        $this->page     = $page;
        $this->callable = $callable;
        $this->params = $params;
        $this->api = $api;
    }

    public static function create( string $page, mixed $callable, array $params = [], bool $api = false ): static {
        return new static( $page, $callable, $params, $api );
    }

    public function page(): string {
        return $this->page;
    }

    public function isPage( string $page ): bool {
        return $this->page === $page;
    }

    public function isApi(): bool {
        return $this->api;
    }

    public function callable(): mixed {
        return $this->callable;
    }

    /**
     * @return string[]
     */
    public function params(): array {
        return $this->params;
    }

    public function __get( string $name ) {
        if ( ! in_array( $name, [ 'page', 'method', 'callable', 'params' ] ) ) {
            throw new \InvalidArgumentException( 'Not a valid property' );
        }

        return $this->$name;
    }
}
