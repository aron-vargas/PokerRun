#!/usr/bin/env bash

set -e

# Navigate to the root of the app
APP_ROOT="/var/www/html"

# Clear the cache to avoid stale deployments
cd $APP_ROOT
php bin/console cache:clear --no-warmup --env=prod

# Set ACL permissions for web server and console users
HTTPDUSER=webapp

# For Amazon Linux 2/3 (using setfacl for safe and recommended permissions)
setfacl -dR -m u:$HTTPDUSER:rwX -m u:root:rwX $APP_ROOT/var/cache $APP_ROOT/var/log
setfacl -R -m u:$HTTPDUSER:rwX -m u:root:rwX $APP_ROOT/var/cache $APP_ROOT/var/log