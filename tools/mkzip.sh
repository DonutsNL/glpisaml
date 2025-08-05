#!/bin/bash

# Update the versions in headers and files
sed -i 's/'1.1.6'/'1.1.12'/g' /var/www/html/glpi/plugins/glpisaml/*.php
sed -i 's/*  @version    1.1.6/*  @version    1.1.12/g' /var/www/html/glpi/plugins/glpisaml/src/*.php
sed -i 's/*  @version    1.1.6/*  @version    1.1.12/g' /var/www/html/glpi/plugins/glpisaml/src/Config/*.php
sed -i 's/*  @version    1.1.6/*  @version    1.1.12/g' /var/www/html/glpi/plugins/glpisaml/src/LoginFlow/*.php

# Remove old zipfiles
rm -y /var/www/html/glpi/marketplace/*.zip

# Create new zipfiles
zip -r ./glpisaml.zip ./glpisaml -x "glpisaml/tools/*" "glpisaml/.gitignore" "glpisaml/.github/*" "glpisaml/.git/*" "glpisaml/releases/*" "glpisaml/composer.lock" "/glpisaml/vendor/bin/*" "/glpisaml/vendor/myclabs/*" "/glpisaml/vendor/nikic/*" "/glpisaml/vendor/phar-io/*" "/glpisaml/vendor/phpunit/*" "/glpisaml/vendor/sebastian/*" "/glpisaml/vendor/theseer/*"

# Make www-data the owner
chown www-data:www-data /var/www/html/glpi/plugins/glpisaml/tools/*.zip

#done
