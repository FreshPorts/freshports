<?php

    require "$include_path/delete_message.php";

    delete_messages($id);

    QueMessage("Message(s) $id and all children were deleted!<br>");

?>