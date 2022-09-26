<?php

namespace GPX\Collection;

use Illuminate\Support\Collection;
use GPX\Repository\RegionRepository;

class MatchesCollection extends Collection {
    private ?array $restricted = null;

    public function restricted() {
        return $this->filter( fn( $week ) => in_array( $week['region_id'], $this->getRestrictedRegions() ) );
    }

    public function notRestricted() {
        return $this->filter( fn( $week ) => in_array( $week['region_id'], $this->getRestrictedRegions() ) );
    }

    public function allRestricted(  ): bool {
        if($this->isEmpty()) return false;
        return $this->notRestricted()->isEmpty();
    }

    public function anyRestricted(): bool {
        if($this->isEmpty()) return false;
        return $this->restricted()->isNotEmpty();
    }

    public function ids(): array {
        return $this->map( fn( $week ) => $week['id'] )
                    ->unique()
                    ->sort( SORT_NUMERIC )
                    ->values()
                    ->toArray();
    }

    protected function getRestrictedRegions(): array {
        if ( null === $this->restricted ) {
            $this->restricted = array_values( RegionRepository::instance()->restricted() );
        }

        return $this->restricted;
    }
}
