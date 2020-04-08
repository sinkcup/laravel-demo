#!/usr/bin/env bash
set -e
STARTED_AT=$(date +%s)

php artisan migrate:fresh
php artisan migrate:refresh

./vendor/bin/phpunit --stop-on-defect --coverage-text tests/

# generate coverage badge for private repo or some git provider can not use codecov.io
file='storage/app/public/coverage/index.html'
if [[ -f $file ]]; then
    percentage=$(grep -m 1 'progressbar' $file | awk -Fvaluenow '{print $2}' | awk -F\" '{print $2}')
    if sed --version 2>&1 | grep 'GNU sed'; then
        sed -i "s/>[0-9]\+\.\?[0-9]\+%/>$percentage%/g" coverage.svg
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' -E "s/>[0-9]+\.?[0-9]+%/>$percentage%/g" coverage.svg
    fi
    if [[ -n $(command -v rsvg-convert) ]]; then
        rsvg-convert coverage.svg > coverage.png
    else
        echo "WARNING: Command 'rsvg-convert' not found, but can be installed with:"

        if [[ "$OSTYPE" == "darwin"* ]]; then
            echo "brew install librsvg"
        else
            echo "sudo apt install librsvg2-bin"
        fi
    fi
fi

FINISHED_AT=$(date +%s)
echo 'Time taken: '$((FINISHED_AT - STARTED_AT))' seconds'
