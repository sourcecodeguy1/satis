#!/bin/sh
set -e

# Configure GitHub auth if token is provided
if [ -n "$GITHUB_TOKEN" ]; then
    composer config --global github-oauth.github.com "$GITHUB_TOKEN"
fi

# Run initial build (non-fatal — container starts even if build fails)
echo "Running initial Satis build..."
satis build /satis/satis.json /output || echo "Initial build failed — will retry via webhook"

exec "$@"
