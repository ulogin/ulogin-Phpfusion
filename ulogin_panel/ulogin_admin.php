<?php

require_once "../../maincore.php";
require_once THEMES."templates/admin_header.php";

include INFUSIONS."ulogin_panel/infusion_db.php";

// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."ulogin_panel/locale/English.php";
}

if (!checkrights("S") || !defined("iAUTH") || $_GET['aid'] != iAUTH) { redirect("../../index.php"); }

if (!isset($_GET['page']) || $_GET['page'] != "settings") {
	include INCLUDES."infusions_include.php";
	if (isset($_POST['sb_settings'])) {
		if (isset($_POST['uloginid1'])) {
			$setting = set_setting("uloginid1", $_POST['uloginid1'], "ulogin_panel");
		}
		if (isset($_POST['uloginid2'])) {
			$setting = set_setting("uloginid2", $_POST['uloginid2'], "ulogin_panel");
		}
		if (isset($_POST['uloginid2']) && ($_POST['uloginid2'] == 1 || $_POST['uloginid2'] == 0)) {
			$setting = set_setting("uloginid2", $_POST['uloginid2'], "ulogin_panel");
		}
		redirect(FUSION_SELF.$aidlink."&amp;status=update_ok");
	}

	if (isset($_GET['status'])) {
		if ($_GET['status'] == "update_ok") {
			$message = $locale['SB_update_ok'];
		}
	}
	if (isset($message) && $message != "") {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }

	$inf_settings = get_settings("ulogin_panel");
	opentable($locale['SB_settings']);
	echo "<form method='post' action='".FUSION_SELF.$aidlink."'>\n";
	echo "<table cellpadding='0' cellspacing='0' align='center' class='tbl-border' style='width:300px; margin-top:20px;'>\n";
	echo "<tr>\n";
	echo "<td class='tbl1'>".$locale['SB_uloginid1']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='uloginid1' class='textbox' value='".$inf_settings['uloginid1']."' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1'>".$locale['SB_uloginid2']."</td>\n";
	echo "<td class='tbl1'><input type='text' name='uloginid2' class='textbox' value='".$inf_settings['uloginid2']."' /></td>\n";
	echo "</tr>\n<tr>\n";
//	echo "<td class='tbl1'>".$locale['SB_ulogin_mail']."</td>\n";
//	echo "<td class='tbl1'><select name='ulogin_mail' size='1' class='textbox'>";
//	echo "<option value='1' ".($inf_settings['ulogin_mail'] == 1 ? "selected='selected'" : "").">".$locale['SB_yes']."</option>\n";
//	echo "<option value='0'".($inf_settings['ulogin_mail'] == 0 ? "selected='selected'" : "").">".$locale['SB_no']."</option>\n";
//	echo "</select></td>\n";
//	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1' colspan='2' style='text-align:center;'><input type='submit' name='sb_settings' value='".$locale['SB_submit']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n";
	echo "</form>\n";
	closetable();
}
require_once THEMES."templates/footer.php";
?>