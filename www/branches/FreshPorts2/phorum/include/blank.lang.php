<?PHP
  // Written by: {your name}
  // Phorum Version: 3.0.6

  $lDownTitle       = ""; //Our forums are down
  $lForumDown       = ""; //Our forums are down
  $lForumDownNotice = ""; //Our Forum is currently down for maintenance.  It will be available again shortly.<p>We are sorry for the inconvenience.
  $lNoAuthor        = ""; //You must supply an author.
  $lNoSubject       = ""; //You must supply a subject.
  $lNoBody          = ""; //You must supply a message.
  $lNoEmail         = ""; //When requesting to be emailed replies, you must supply a valid email address.
  $lReplyMessage    = ""; //Reply To This Message
  $lWrote           = ""; //wrote
  $lQuote           = ""; //Quote
  $lFormName        = ""; //Your Name
  $lFormEmail       = ""; //Your Email
  $lFormSubject     = ""; //Subject
  $lFormPost        = ""; //Post
  $lAvailableForums = ""; //Available Forums
  $lNoActiveForums  = ""; //There are no active forums.
  $lCollapseThreads = ""; //Collapse Threads
  $lViewThreads     = ""; //View Threads
  $lForumList       = ""; //Forum List
  $lGoToTop         = ""; //Go to Top
  $lStartTopic      = ""; //New Topic
  $lSearch          = ""; //Search
  $lForum           = ""; //forum
  $lNewerMessages   = ""; //Newer Messages
  $lOlderMessages   = ""; //Older Messages
  $lNew             = ""; //new
  $lTopics          = ""; //Topics
  $lAuthor          = ""; //Author
  $lDate            = ""; //Date
  $lGoToTopic       = ""; //Go to Topic
  $lPreviousMessage = ""; //Previous Message
  $lNextMessage     = ""; //Next Message
  $lPreviousTopic   = ""; //Previous Topic
  $lNextTopic       = ""; //Next Topic
  $lReply           = ""; //Reply To This Message
  $lSearchResults   = ""; //Search Results
  $lSearchTips      = ""; //Search Tips
  $lTheSearchTips   = ""; //AND is the default. That is, a search for <B>dog</B> and <B>cat</B> returns all messages that contain those words anywhere.<p>QUOTES (\") allow searches for phrases. That is, a search for <B>\"dog cat\"</B> returns all messages that contain that exact phrase, with space.<p>MINUS (-) eliminates words. That is, a seach for <B>dog</B> and <B>-cat</B> returns all messages that contain <b>dog</b> but not <b>cat</b>. You can MINUS a phrase in QUOTES, like <B>dog -\"siamese cat\"</B>.<p>The engine is not case-sensitive and searches the title, body, and author.
  $lNoMatches       = ""; //No matches found :(
  $lLastPostDate    = ""; //Last Post
  $lNumPosts        = ""; //Posts
  $lEmailMe         = ""; //Email replies to this thread, to the address above.
  $lEmailAlert      = ""; //You must enter a valid e-mail address if you want replies emailed to you.
  $lViolationTitle  = ""; //Sorry...    
  $lViolation       = ""; //Posting is not available because of your IP Address, the name you entered, or the email you entered.  This may not be because of you.  Try another name and/or email.  If you still cannot post, contact <a href=\"mailto:$Mod\">$Mod</a> for an explination.
  $lNotFound        = ""; //The message you requested could not be found.  For assist contact <a href=\"mailto:$Mod\">$Mod</a>
    
//This function takes a date string in the format YYYY-MM-DD HH:MM:SS and
//returns a date string appropriately formated for this language.
//The default is for US English, MM-DD-YY HH:MM.  But then again we have no
//standards.
  function date_format($datestamp){
    $sDate = substr($datestamp, 5, 5);
    $sDate = $sDate."-".substr($datestamp, 0, 4);
    $sDate = $sDate.substr($datestamp, 10, 6);
    return $sDate;
  }

?>