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
echo "Using PHP: $phpbin"
$phpbin -v

if test -f "$DIR/composer.phar"; then
    $phpbin composer.phar self-update
else
    curl https://getcomposer.org/installer | $phpbin
    chmod +x composer.phar
fi

git pull
$phpbin composer.phar install --no-interaction --no-dev --optimize-autoloader
