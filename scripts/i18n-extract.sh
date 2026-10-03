#!/bin/sh -e

# Regenerate locale/freshports.pot from the strings wrapped in _() and ngettext(),
# then merge any new strings into the existing translations.
# See https://github.com/FreshPorts/freshports/issues/678
#
# Run from anywhere; requires devel/gettext-tools

top=$(cd $(dirname $0)/.. && pwd)
cd $top

find classes include www -type f -name '*.php' | sort | \
  xgettext --language=PHP --from-code=UTF-8 \
    --package-name=FreshPorts \
    --msgid-bugs-address=https://github.com/FreshPorts/freshports/issues \
    --add-comments=TRANSLATORS: \
    --sort-by-file \
    --files-from=- \
    --output=locale/freshports.pot

for po in locale/*/LC_MESSAGES/freshports.po
do
  [ -f "$po" ] || continue
  msgmerge --quiet --update --backup=none "$po" locale/freshports.pot
done
