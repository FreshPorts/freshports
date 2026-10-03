#!/bin/sh -e

# Compile each locale/<locale>/LC_MESSAGES/freshports.po into freshports.mo
# The website offers a language only once its .mo file exists.
# Run this when deploying; php-fpm caches .mo files, so reload it afterwards.
# See https://github.com/FreshPorts/freshports/issues/678
#
# Requires devel/gettext-tools

top=$(cd $(dirname $0)/.. && pwd)
cd $top

for po in locale/*/LC_MESSAGES/freshports.po
do
  [ -f "$po" ] || continue
  msgfmt --check --output-file="${po%.po}.mo" "$po"
done
