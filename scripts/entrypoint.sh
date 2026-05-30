#!/bin/sh
set -e

# Configure GitHub auth if token is provided
if [ -n "$GITHUB_TOKEN" ]; then
    composer config --global github-oauth.github.com "$GITHUB_TOKEN"
fi

# Run initial build (non-fatal — container starts even if build fails)
echo "Running initial Satis build..."
php /satis/bin/satis build /satis/satis.json /output || echo "Initial build failed — will retry via webhook"

# Ensure www-data can write to all directories for webhook-triggered rebuilds
chown -R www-data:www-data /output /var/www/.composer

exec "$@"
