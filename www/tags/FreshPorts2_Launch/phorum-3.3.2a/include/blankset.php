<?php

// Blank out potentially dangerous per-forum values

$aryUnset = array(
              'ForumId',
              'ForumActive',
              'ForumName',
              'ForumDescription',
              'ForumConfigSuffix',
              'ForumFolder',
              'ForumParent',
              'ForumLang',
              'ForumDisplay',
              'ForumTableName',
              'ForumModeration',
              'ForumModEmail',
              'ForumModPass',
              'ForumEmailList',
              'ForumEmailReturnList',
              'ForumEmailTag',
              'ForumCheckDup',
              'ForumMultiLevel',
              'ForumCollapse',
              'ForumFlat',
              'ForumStaffHost',
              'ForumAllowHTML',
              'ForumAllowUploads',
              'ForumTableBodyColor2',
              'ForumTableBodyFontColor2',
              'ForumTableWidth',
              'ForumNavColor',
              'ForumNavFontColor',
              'ForumTableHeaderColor',
              'ForumTableHeaderFontColor',
              'ForumTableBodyColor1',
              'ForumTableBodyFontColor1'
);

reset($aryUnset);
while (list($key, $value) = each($aryUnset)) {
  if(isset($$value)) {
    unset($$value);
  }
}

initvar("ForumConfigSuffix");
initvar("ForumLang");
initvar("ForumModEmail");
initvar("ForumName");
initvar("ForumParent", 0);

?>