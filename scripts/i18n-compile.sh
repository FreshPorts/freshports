#!/bin/sh -e

# Compile each locale/<locale>/LC_MESSAGES/freshports.po into freshports.mo
# The website offers a language only once its .mo file exists.
# Run this when deploying; php-fpm caches .mo files, so reload it afterwards (service php_fpm reload).
# See https://github.com/FreshPorts/freshports/issues/678
#
# When a translation changes, the cached pages for that language still hold the
# old text, so they are removed. English is never affected.
# Set FRESHPORTS_CACHE_DIRECTORY if the cache is not in the default location
# (CACHE_DIRECTORY in include/constants.php).
#
# Requires devel/gettext-tools

cache=${FRESHPORTS_CACHE_DIRECTORY:-/var/db/freshports/cache}

top=$(cd $(dirname $0)/.. && pwd)
cd $top

for po in locale/*/LC_MESSAGES/freshports.po
do
  [ -f "$po" ] || continue
  mo="${po%.po}.mo"
  locale=$(basename $(dirname $(dirname "$po")))

  msgfmt --check --output-file="$mo.new" "$po"
  if [ -f "$mo" ] && cmp -s "$mo" "$mo.new"; then
    rm "$mo.new"
    continue
  fi
  mv "$mo.new" "$mo"
  echo "$locale: translation changed"

  if [ -d "$cache" ]; then
    # see freshports_i18n_cache_suffix() and Cache::_LocalizedKey():
    #   most entries end in .zh_CN.html, date.php entries end in .zh_CN,
    #   and CacheNews cleans its names, so those end in -zh-cn-html
    cleaned=$(echo "$locale" | tr 'A-Z_' 'a-z-')
    find "$cache" -type f \( -name "*.$locale.html" -o -name "*.$locale" -o -name "*-$cleaned-html" \) -print -delete | \
      sed -e "s/^/$locale: removed cached /"
  else
    echo "$locale: no cache directory at $cache, so no cached pages were removed"
  fi
done
