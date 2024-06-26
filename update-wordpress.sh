#!/usr/bin/env bash

DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)
cd "$DIR" || exit 1

if test -f "/usr/bin/php"; then
    phpbin="/usr/bin/php"
else
    phpbin="php"
fi
phpversions=("5.6" "7.0" "7.1" "7.2" "7.3" "7.4" "8.0" "8.1" "8.2" "8.3")
if [[ -f "$DIR/.php-version" ]]; then
    phpver=$(<"$DIR/.php-version")
    for version in "${phpversions[@]}"; do
        if [[ $phpver == "$version" ]]; then
            if test -f "/usr/bin/php$phpver"; then
                phpbin="/usr/bin/php$phpver"
            fi
        fi
    done
fi

if test -f "$DIR/wp-cli.phar"; then
    $phpbin wp-cli.phar cli update
else
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod +x wp-cli.phar
fi

$phpbin wp-cli.phar core update
$phpbin wp-cli.phar theme update --all
$phpbin wp-cli.phar plugin update --all
$phpbin wp-cli.phar core update-db
