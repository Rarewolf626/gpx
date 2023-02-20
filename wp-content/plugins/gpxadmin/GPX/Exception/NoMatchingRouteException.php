<?php

namespace GPX\Exception;

use Throwable;
use Illuminate\Http\Request;

class NoMatchingRouteException extends \InvalidArgumentException {
    public Request $request;

    public function __construct(
        string $message = "No route matched",
        int $code = 404,
        ?Throwable $previous = null
    ) {
        parent::__construct( $message, $code, $previous );
        $this->setRequest();
    }

    public function setRequest( Request $request = null ): self {
        $this->request = $request ?? gpx_request();
        return $this;
    }
}
