#!/usr/bin/env bash
#
# Factory Hook: db-update
#
# Run cohesion commands after executing

SITEGROUP="$1"
ENVIRONMENT="$2"
DB_ROLE="$3"
DOMAIN="$4"
ATTEMPT="$5"

echo "Sitegroup: $SITEGROUP"
echo "Environment: $ENVIRONMENT"
echo "DB role: $DB_ROLE"
echo "Domain: $DOMAIN"
echo "Attempt: $ATTEMPT"

# Drush executable:
drush="/mnt/www/html/$SITEGROUP.$ENVIRONMENT/vendor/bin/drush"

# Create and set Drush cache to unique local temporary storage per site.
# This approach isolates drush processes to completely avoid race conditions
# that persist after initial attempts at addressing in BLT: https://github.com/acquia/blt/pull/2922
# This line will need to be changed for applications that do not have the cache.php script.
cache_dir=`/usr/bin/env php /mnt/www/html/$SITEGROUP.$ENVIRONMENT/vendor/acquia/blt/scripts/blt/drush/cache.php $SITEGROUP $ENVIRONMENT $DOMAIN`

echo "Generated temporary Drush cache directory: $cache_dir."

echo "Initializing Acquia Site Studio tasks on $DOMAIN domain in $ENVIRONMENT environment on the $SITEGROUP application."

# Import cohesion (edit as needed).
DRUSH_PATHS_CACHE_DIRECTORY="$cache_dir" /usr/local/php7.4/bin/php -d memory_limit=1024M $drush -r /mnt/www/html/$SITEGROUP.$ENVIRONMENT/docroot -l $DOMAIN cohesion:import
result=$?

# Import cohesion package.
DRUSH_PATHS_CACHE_DIRECTORY="$cache_dir" /usr/local/php7.4/bin/php -d memory_limit=1024M $drush -r /mnt/www/html/$SITEGROUP.$ENVIRONMENT/docroot -l $DOMAIN sync:import --overwrite-all --force

# Rebuild cohesion.
DRUSH_PATHS_CACHE_DIRECTORY="$cache_dir" $drush -r /mnt/www/html/$SITEGROUP.$ENVIRONMENT/docroot -l $DOMAIN cohesion:rebuild
# Clear the cache.
DRUSH_PATHS_CACHE_DIRECTORY="$cache_dir" /usr/local/php7.4/bin/php -d memory_limit=1024M $drush -r /mnt/www/html/$SITEGROUP.$ENVIRONMENT/docroot -l $DOMAIN cache:rebuild

# Clean up the drush cache directory.
echo "Removing temporary drush cache files."
rm -rf "$cache_dir"

set +v

# If a failure (non-zero) exit code is returned here, Site Factory will retry
# up to 3 times before treating this script as failed.  This behaviour can be
# overridden by returning exit code 131 to signal that this script has failed
# and no further retries should be attempted.
exit $result
