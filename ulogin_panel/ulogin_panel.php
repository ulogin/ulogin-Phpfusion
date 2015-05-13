<?php

if (!defined("IN_FUSION"))
{
	die("Access Denied");
}
if (iGUEST)
{
	include_once INFUSIONS."ulogin_panel/ulogin.php";
}
?>