<?php
  // /12,34,2/grad_logo.gif
  if(empty($PATH_INFO))  $PATH_INFO=$HTTP_SERVER_VARS["PATH_INFO"];
  $file=basename($PATH_INFO);
  list($f, $id, $fileid)=explode(",", basename(dirname($PATH_INFO)));

  include "./common.php";

  if(empty($PATH_INFO)){
    Header("Location: $forum_url/$forum_page.$ext?$GetVars");
    echo "Location: $forum_page.$ext?$GetVars";
    exit();
  }

  $filename="$AttachmentDir/$ForumTableName/$id"."_$fileid".strtolower(strrchr($file, "."));

  // Mime Types for Attachments

  $mime_types["default"]="text/plain";
  $mime_types["pdf"]="application/pdf";
  $mime_types["doc"]="application/msword";
  $mime_types["xls"]="application/vnd.ms-excel";
  $mime_types["gif"]="image/gif";
  $mime_types["png"]="image/png";
  $mime_types["jpg"]="image/jpeg";
  $mime_types["jpeg"]="image/jpeg";
  $mime_types["jpe"]="image/jpeg";
  $mime_types["tiff"]="image/tiff";
  $mime_types["tif"]="image/tiff";
  $mime_types["xml"]="text/xml";
  $mime_types["mpeg"]="video/mpeg";
  $mime_types["mpg"]="video/mpeg";
  $mime_types["mpe"]="video/mpeg";
  $mime_types["qt"]="video/quicktime";
  $mime_types["mov"]="video/quicktime";
  $mime_types["avi"]="video/x-msvideo";

  $type=substr($file, strrpos($file, ".")+1);
  if(isset($mime_types[$type])){
    $mime=$mime_types[$type];
  }
  else{
    $mime=$mime_types["default"];
  }

  header("Content-Type: $mime");
  header("Content-Disposition: filename=\"$file\"");

  if ( strstr($mime, "text") ){
     $file_handle = fopen("$filename","r");
  }
  else{
     $file_handle = fopen("$filename","rb");
  }

  fpassthru($file_handle);

  exit();
?>