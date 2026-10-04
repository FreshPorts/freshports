# Translating FreshPorts

See https://github.com/FreshPorts/freshports/issues/678

`freshports.pot` holds every translatable string on the website. Each
translation lives at `<locale>/LC_MESSAGES/freshports.po`, for example
`zh_CN/LC_MESSAGES/freshports.po`.

## Starting a new translation

    mkdir -p locale/zh_CN/LC_MESSAGES
    msginit --input=locale/freshports.pot --locale=zh_CN.UTF-8 \
      --output-file=locale/zh_CN/LC_MESSAGES/freshports.po

Then edit the `.po` file (Poedit works well) and open a pull request.

Check the `Plural-Forms` line in the header. Some versions of `msginit` leave
it as `nplurals=INTEGER; plural=EXPRESSION;`, which will not compile. For
Simplified Chinese it should be:

    "Plural-Forms: nplurals=1; plural=0;\n"

Please also add the language's own name to `freshports_i18n_native_name()` in
`include/i18n.php`, so the language picker shows it.

## Notes for translators

- Text such as `%s` or `%1$s` is replaced with a link or a value. Keep it in
  the translation; numbered ones may be reordered.
- Some strings contain HTML, such as `<a href="%s">photos</a>`. Translate the
  words, and leave the tags as they are.
- Port names, descriptions, and commit messages come from the FreeBSD ports
  tree and are not translated.

## For developers

Wrap new strings with `_()`, or `ngettext()` for plurals, then run:

    scripts/i18n-extract.sh   # updates freshports.pot and merges into each .po
    scripts/i18n-compile.sh   # builds the .mo files the website uses

The `.mo` files are not committed; build them when deploying. A language
appears on the website only once its `.mo` exists and the operating system has
the matching UTF-8 locale.
