<?php
use Illuminate\Support\Str;

function gpx_db_placeholders( array $data = [], string $placeholder = '%s' ): string {
    return implode( ',', array_fill( 0, count( $data ), $placeholder ) );
}

/**
 * escape a string to be used as a mysql table or column name
 *
 * @param string $value
 *
 * @return string
 */
function gpx_esc_table( string $value ): string {
    if ( Str::contains( $value, '.' ) ) {
        // this is table.column or schema.table.column or schema.table
        // each section needs to be escaped separately
        return implode( '.', array_map( 'gpx_esc_table', explode( '.', $value ) ) );
    }

    return '`' . preg_replace( "/[^a-z0-9\\-_\$]/i", '', $value ) . '`';
}

function gpx_esc_orderby( $value ): string {
    if ( ! in_array( mb_strtolower( $value ), [ 'asc', 'desc' ] ) ) {
        return 'ASC';
    }

    return mb_strtoupper( $value );
}

function gpx_esc_like( $value ): string {
    global $wpdb;

    return $wpdb->esc_like( $value );
}

function gpx_is_administrator(): bool {
    return check_user_role( [ 'gpx_admin', 'gpx_call_center', 'administrator', 'administrator_plus' ] );
}

function gpx_show_debug( bool $admin_only = false ): bool {
    if ( ! defined( 'GPX_SHOW_DEBUG' ) || ! GPX_SHOW_DEBUG ) {
        return false;
    }
    if ( ! $admin_only ) {
        return true;
    }

    return gpx_is_administrator();
}

function gpx_format_phone( ?string $value = null, int $format = \libphonenumber\PhoneNumberFormat::NATIONAL ): ?string {
    if ( empty( $value ) ) {
        return null;
    }

    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try {
        $number = $phoneUtil->parse( $value, "US" );

        return $phoneUtil->format( $number, $format );
    } catch ( \libphonenumber\NumberParseException $e ) {
        return $value;
    }
}