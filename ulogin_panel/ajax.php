<?php
require_once dirname(__FILE__)."../../../maincore.php";
include INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php";
global $locale;
if (isset($_POST['identity']))
{
	if (dbquery("DELETE FROM ".DB_PREFIX."ulogin WHERE identity='".mysql_real_escape_string($_POST['identity'])."'")) die(json_encode(array('msg' => $locale['SB_error12'], 'answerType' => 'ok')));
	else die(json_encode(array('msg' => $locale['SB_ups'], 'answerType' => 'error')));
}
else die(json_encode(array('msg' => $locale['SB_ups'], 'answerType' => 'error')));

?>


