<?PHP
  // Written by: Brian Moon
  // Phorum Version: 3.1
  
  $lForumDown       = ""; // english: Our forums are down
  $lForumDownNotice = ""; // english: Our Forum is currently down for maintenance.  It will be available again shortly.<p>We are sorry for the inconvenience.
  $lNoAuthor        = ""; // english: You must supply an author.
  $lNoSubject       = ""; // english: You must supply a subject.
  $lNoBody          = ""; // english: You must supply a message.
  $lNoEmail         = ""; // english: You did not enter a valid email address.  An email address is not required.<br>If you do not wish to leave your email address please leave the field blank.
  $lNoEmailReply    = ""; // english: When requesting to be emailed replies, you must supply a valid email address.
  $lReplyMessage    = ""; // english: Reply To This Message
  $lReplyThread     = ""; // english: Reply To This Topic
  $lWrote           = ""; // english: wrote
  $lQuote           = ""; // english: Quote
  $lFormName        = ""; // english: Your Name
  $lFormEmail       = ""; // english: Your Email
  $lFormSubject     = ""; // english: Subject
  $lFormPost        = ""; // english: Post
  $lFormImage       = ""; // english: Image
  $lAvailableForums = ""; // english: Available Forums
  $lNoActiveForums  = ""; // english: There are no active forums.
  $lCollapseThreads = ""; // english: Collapse Threads
  $lViewThreads     = ""; // english: View Threads
  $lReadFlat        = ""; // english: Flat View
  $lReadThreads     = ""; // english: Threaded View
  $lForumList       = ""; // english: Forum List
  $lUpLevel         = ""; // english: Up One Level
  $lGoToTop         = ""; // english: Go to Top
  $lStartTopic      = ""; // english: New Topic
  $lSearch          = ""; // english: Search
  $lForum           = ""; // english: forum
  $lNewerMessages   = ""; // english: Newer Messages
  $lOlderMessages   = ""; // english: Older Messages
  $lNew             = ""; // english: new
  $lTopics          = ""; // english: Topics
  $lAuthor          = ""; // english: Author
  $lDate            = ""; // english: Date
  $lLatest          = ""; // english: Latest Reply
  $lReplies         = ""; // english: Replies
  $lGoToTopic       = ""; // english: Go to Topic
  $lPreviousMessage = ""; // english: Previous Message
  $lNextMessage     = ""; // english: Next Message
  $lPreviousTopic   = ""; // english: Previous Topic
  $lNextTopic       = ""; // english: Next Topic
  $lSearchResults   = ""; // english: Search Results
  $lSearchTips      = ""; // english: Search Tips
  $lTheSearchTips   = ""; // english: AND is the default. That is, a search for <B>dog</B> and <B>cat</B> returns all messages that contain those words anywhere.<p>QUOTES (\") allow searches for phrases. That is, a search for <B>\"dog cat\"</B> returns all messages that contain that exact phrase, with space.<p>MINUS (-) eliminates words. That is, a seach for <B>dog</B> and <B>-cat</B> returns all messages that contain <b>dog</b> but not <b>cat</b>. You can MINUS a phrase in QUOTES, like <B>dog -\"siamese cat\"</B>.<p>The engine is not case-sensitive and searches the title, body, and author.
  $lNoMatches       = ""; // english: No matches found :(
  $lMessageBodies   = ""; // english: Message Bodies (slower)
  $lMoreMatches     = ""; // english: More Matches
  $lPrevMatches     = ""; // english: Previous Matches
  $lLastPostDate    = ""; // english: Last Post
  $lNumPosts        = ""; // english: Posts
  $lForumFolder     = ""; // english: Forum Folder
  $lEmailMe         = ""; // english: Email replies to this thread, to the address above.
  $lEmailAlert      = ""; // english: You must enter a valid e-mail address if you want replies emailed to you.
  $lViolationTitle  = ""; // english: Sorry...    
  $lViolation       = ""; // english: Posting is not available because of your IP Address, the name you entered, or the email you entered.  This may not be because of you.  Try another name and/or email.  If you still cannot post, contact <a href=\"mailto:$ForumModEmail\">$ForumModEmail</a> for an explanation.
  $lNotFound        = ""; // english: The message you requested could not be found.  For assist contact <a href=\"mailto:$ForumModEmail\">$ForumModEmail</a>
  
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