<?php

  function format_body($body){
    global $ForumAllowHTML, $plugins, $lQuote;

    // get rid of moderator HTML tags
    $body = str_replace("<HTML>", "", $body);
    $body = str_replace("</HTML>", "", $body);

    // replace all tag starts and ends
    $body=str_replace("<", "&lt;", $body);
    $body=str_replace(">", "&gt;", $body);

    if(function_exists("preg_replace")){
        // handle old legacy <> links by converting them into BB tags
        $body=preg_replace("/&lt;((http|https|ftp):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),]+?)&gt;/i", "<a href=\"$1\">$1</a>", $body);
        $body=preg_replace("/&lt;mailto:([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)&gt;/i", "<a href=\"mailto:$1\">$1</a>", $body);
    }

    if(function_exists("preg_replace")){

        if($ForumAllowHTML==1){
            // replace url/link items
            $body=preg_replace("/\[img\]((http|https|ftp):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),]+?)\[\/img\]/i", "<img src=\"$1\" />", $body);
            $body=preg_replace("/\[url\]((http|https|ftp|mailto):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),]+?)\[\/url\]/i", "<a href=\"$1\">$1</a>", $body);
            $body=preg_replace("/\[url=((http|https|ftp|mailto):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),]+?)\](.+?)\[\/url\]/i", "<a href=\"$1\">$3</a>", $body);
            $body=preg_replace("/\[email\]([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/i", "<a href=\"mailto:$1\">$1</a>", $body);



            // replace simple tag replacements
            $search=array(
                          "/\[(b)\]/",
                          "/\[\/(b)\]/",
                          "/\[(u)\]/",
                          "/\[\/(u)\]/",
                          "/\[(i)\]/",
                          "/\[\/(i)\]/",
                          "/\[(center)\]/",
                          "/\[\/(center)\]/",
                          "/\[(quote)\]/",
                          "/\[\/(quote)\]/"
                      );

            $replace=array(
                        "<b>",
                        "</b>",
                        "<u>",
                        "</u>",
                        "<i>",
                        "</i>",
                        "<center>",
                        "</center>",
                        "<blockquote>$lQuote:<br />\n",
                        "</blockquote>"
                     );

            $body=preg_replace($search, $replace, $body);
        }



        // clean up badly formed tags or if not allowed

        $body=preg_replace("/\[url=.*?\]/", "", $body);


        $search_clean=array(
                        "/\[url\]/",
                        "/\[\/url\]/",
                        "/\[img\]/",
                        "/\[\/img\]/",
                        "/\[email\]/",
                        "/\[email\]/",
                        "/\[\/email\]/"
                      );

        $body=preg_replace($search_clean, "", $body);

    }

    // exec all read plugins
    @reset($plugins["read_body"]);
    while(list($key,$val) = each($plugins["read_body"])) {
      $body = $val($body);
    }

    $body=nl2br($body);

    return $body;

  }

?>