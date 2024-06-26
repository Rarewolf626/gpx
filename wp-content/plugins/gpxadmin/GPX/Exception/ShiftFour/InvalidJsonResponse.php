<?php

namespace GPX\Exception\ShiftFour;

class InvalidJsonResponse extends \JsonException {

    protected ?string $response = null;
    private int|float $duration = 0;

    public function setResponse( string $response = null ): static {
        $this->response = $response;

        return $this;
    }

    public function response(): ?string {
        return $this->response;
    }

    public function duration(  ): int|float {
        return $this->duration;
    }

    public function setDuration( float|int $duration = 0 ): static {
        $this->duration = $duration;
    }
}
