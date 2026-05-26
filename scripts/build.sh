#!/bin/bash
set -e

# Configure GitHub auth if token is provided
if [ -n "$GITHUB_TOKEN" ]; then
    composer config --global github-oauth.github.com "$GITHUB_TOKEN"
fi

exec php /satis/bin/satis build /satis/satis.json /output
