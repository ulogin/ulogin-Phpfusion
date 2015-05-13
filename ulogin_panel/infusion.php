<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include INFUSIONS."ulogin_panel/infusion_db.php";

// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php")) {
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php";
} else {
	// Load the infusion's default locale file.
	include INFUSIONS."ulogin_panel/locale/English.php";
}

// Infusion general information
$inf_title = $locale['SB_title'];
$inf_description = $locale['SB_desc'];
$inf_version = "2.0";
$inf_developer = "uLogin Team";
$inf_email = "team@ulogin.ru";
$inf_weburl = "https://ulogin.ru";

$inf_folder = "ulogin_panel"; // The folder in which the infusion resides.

// Delete any items not required below.
$inf_newtable[1] = DB_ulogin." (
id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
user_id int(10) unsigned NOT NULL,
identity VARCHAR(200) NOT NULL,
network VARCHAR(200) NOT NULL,
PRIMARY KEY (id)
) ENGINE=MyISAM;";

$inf_insertdbrow[1] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status) VALUES('".$locale['SB_title']."', 'ulogin_panel', '', '4', '3', 'file', '0', '0', '1')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('uloginid1', '', '".$inf_folder."')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('uloginid2', '', '".$inf_folder."')";

//$inf_droptable[1] = DB_ulogin;

$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[2] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";

$inf_adminpanel[1] = array(
	"title" => $locale['SB_admin1'],
	"image" => "shout.gif",
	"panel" => "ulogin_admin.php",
	"rights" => "S"
);
?>