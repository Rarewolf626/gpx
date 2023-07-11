<?php

namespace GPX\GPXAdmin\Router;

use function mb_strtoupper;

/**
 * @property-read string $page
 * @property-read ?string $method
 * @property-read mixed $callable
 * @property-read string[] $params
 */
class AdminRoute {
    private string $page;
    private ?string $method;
    private mixed $callable;
    /** @var string[]  */
    private array $params;

    public function __construct( string $page, mixed $callable, array $params = [], string $method = null ) {
        $this->page     = $page;
        $this->callable = $callable;
        $this->params = $params;
        $this->method   = $method ? mb_strtoupper($method) : null;
    }

    public static function create( string $page, mixed $callable, array $params = [], string $method = null ): static {
        return new static( $page, $callable, $params, $method );
    }

    public function page(): string {
        return $this->page;
    }

    public function isPage( string $page ): bool {
        return $this->page === $page;
    }

    public function method(): ?string {
        return $this->method;
    }

    public function methodMatches( string $method = null ): bool {
        if(null === $this->method) return true;
        return $this->method === mb_strtoupper($method);
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
