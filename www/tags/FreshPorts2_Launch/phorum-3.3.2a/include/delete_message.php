<?php

    function delete_messages($ids)
    {
        GLOBAL $PHORUM, $DB, $q;

        $id_array=explode(",", $ids);

        $SQL="Select id from $PHORUM[ForumTableName] where id in ($ids)";
        $q->query($DB, $SQL);

        while(list($key, $id)=each($id_array)){
            $lists[]=_get_message_tree($id);
        }

        $lists=implode(",", $lists);
        $arr=explode(",", $lists);

        $SQL="Delete from $PHORUM[ForumTableName] where id in ($lists)";
        $q->query($DB, $SQL);

        $SQL="Delete from $PHORUM[ForumTableName]_bodies where id in ($lists)";
        $q->query($DB, $SQL);

        $SQL="Select message_id,id,filename from $PHORUM[ForumTableName]_attachments where message_id in ($lists)";

        $q->query($DB, $SQL);

        while($rec=$q->getrow()){
            $filename="$PHORUM[AttachmentDir]/$PHORUM[ForumTableName]/$rec[message_id]_$rec[id]".strtolower(strrchr($rec["filename"], "."));
            unlink($filename);
        }

        $SQL="Delete from $PHORUM[ForumTableName]_attachments where message_id in ($lists)";
        $q->query($DB, $SQL);

    }

    function _get_message_tree($id)
    {
        global $PHORUM, $DB;
	$q = new query($DB);
        $SQL="Select id from $PHORUM[ForumTableName] where parent=$id";
        $q->query($DB, $SQL);
        $tree="$id";
        while($rec=$q->getrow()){
            $tree.=","._get_message_tree($rec["id"]);
        }
        return $tree;
    }

?>
