<?PHP
  require "./common.php";
  $file=basename($file);
  if(empty($file)) Header("Location: $forum_page.$ext?$GetVars");
  $id=$i;

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
  // Check that we are running as a module
  if(function_exists("apache_note")){
    header("Content-Type: $mime");
    header("Content-Disposition: filename=\"$file\"");
  }
  readfile($uploadDir.'/'.$ForumTableName.'/'.$file);
  exit();
?>