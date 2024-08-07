<?php header('Content-Type: application/opensearchdescription+xml; charset=utf-8'); ?>
<?xml version="1.0"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
  <ShortName>FreshPorts</ShortName>
  <Image width="16" height="16" type="image/x-icon"><?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/favicon.ico' ?></Image>
  <Url type="text/html" template="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/search.php?query={searchTerms}' ?>" />
</OpenSearchDescription>
