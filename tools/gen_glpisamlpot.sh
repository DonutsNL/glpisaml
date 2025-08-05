#!/bin/bash

# depends on gettext package
# in debian install it via apt install gettext first.

dirplugin="/var/www/html/glpi/plugins/glpisaml"

pathGLPIenUSpo="/var/www/html/glpi/locales/"
if [ ! -d "$pathGLPIenUSpo" ];then
    mkdir -p "${pathGLPIenUSpo}"
fi
curl https://raw.githubusercontent.com/glpi-project/glpi/refs/heads/main/locales/en_US.po > "${pathGLPIenUSpo}en_US.po" 

cd $dirplugin
find ./ -type f -name "*.php" | xgettext -f - -o "locales/glpiSaml.pot" -L PHP --exclude-file="${pathGLPIenUSpo}en_US.po"  --from-code=UTF-8 --force-po --keyword=__
