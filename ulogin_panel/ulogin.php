<?php

if (!defined("IN_FUSION"))
{
	die("Access Denied");
}
include_once INFUSIONS."ulogin_panel/infusion_db.php";
include_once INCLUDES."infusions_include.php";
// Check if locale file is available matching the current site locale setting.
if (file_exists(INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php"))
{
	// Load the locale file matching the current site locale setting.
	include INFUSIONS."ulogin_panel/locale/".$settings['locale'].".php";
}
else
{
	// Load the infusion's default locale file.
	include INFUSIONS."ulogin_panel/locale/English.php";
}

global $locale;

$shout_settings = get_settings("ulogin_panel");
$link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
$sep = stristr($link, "?") ? "&amp;" : "?";
$shout_link = "";
$shout_message = "";
uloginParseRequest();
if (iGUEST)
{
	openside($locale['SB_title']);
	echo '<script src="http://ulogin.ru/js/ulogin.js"></script>';
	echo getPanelCode(0);
	closeside();
}
else
{
	echo '<link type="text/css" rel="stylesheet" href="https://ulogin.ru/css/providers.css">';
	echo '<script src="http://ulogin.ru/js/ulogin.js"></script>';
	echo '<div class="profile_category_name tbl2" style="max-width: 500px;margin: 0 auto;"><strong>'.$locale["SB_sync_title"].'</strong></div>';
	echo '<div style="text-align:center; margin-bottom: 10px;">'.$locale['SB_sync_title_desc'].'</div>';
	echo '<div style="padding: 0 0 10px 0;">'.getPanelCode(0).'</div>';
	echo '<div class="profile_category_name tbl2" style="max-width: 500px;margin: 0 auto;"><strong>'.$locale['SB_sync_acc_title'].'</strong></div>';
	echo '<div class="ulogin_synchronisation" style="max-width: 500px;margin: 0 auto;  text-align: center;">'.getSyncPanel().'</div>';
	global $userdata;
	$current_user = isset($userdata['user_id']) ? $userdata['user_id'] : 0;
	$member_b = dbquery("SELECT * FROM ".DB_ulogin." where user_id=".$current_user);
	if (dbrows($member_b) > 0)
	{
		echo '<div style="text-align:center; margin-bottom: 10px;">'.$locale['SB_sync_acc_delete'].'</div>';
	}
	else echo '<div style="text-align:center; margin-bottom: 10px;">'.$locale['SB_sync_acc_none'].'</div>';
	echo "<script type='text/javascript'>
jQuery(document).ready(function () {
    var uloginNetwork = jQuery('.ulogin_accounts').find('.ulogin_network');
    uloginNetwork.click(function () {
        var network = jQuery(this).attr('data-ulogin-network');
        var identity = jQuery(this).attr('data-ulogin-identity');
        uloginDeleteAccount(network,identity);
    });
});

function uloginDeleteAccount(network,identity) {
    var query = $.ajax({
        type: 'POST',
        url: '/infusions/ulogin_panel/ajax.php',
        data: {
            identity: identity,
            network: network
        },
        dataType: 'json',
        error: function (data) {
            alert('".$locale['SB_sync_query_error']."');
        },
        success: function (data) {
            if (data.answerType == 'error') {
                alert(data.msg);
            }
            if (data.answerType == 'ok') {
                var accounts = jQuery('.ulogin_accounts'),
                    nw = accounts.find('[data-ulogin-network=' + network + ']');
                if (nw.length > 0) nw.hide();
                alert(data.msg);
            }
        }
    });
    return false;
}
</script>";
}
function uloginParseRequest()
{
	global $locale;
	if (!isset($_POST['token'])) return false; // не был получен токен uLogin
	$s = uloginGetUserFromToken($_POST['token']);
	if (!$s)
	{
		var_dump($s);
		exit;
	}
	$u_user = json_decode($s, true);
	$u_user['nickname'] = isset($u_user['nickname']) ? $u_user['nickname'] : $u_user['nickname'] = '';
	$check = uloginCheckTokenError($u_user);
	if (!$check)
	{
		die("<div id='close-message'><div class='admin-message'>".$locale['SB_error11']."</div></div>\n"._get_back_url());
	}
	$user_id = getUserIdByIdentity($u_user['identity']);
	if ($user_id)
	{
		$pf_user = getPhpFusionUser($user_id);
		if ($user_id > 0 && $pf_user > 0)
		{
			uloginCheckUserId($user_id);
		}
		else
		{
			$user_id = uloginRegistrationUser($u_user, 1);
		}
	}
	else
	{
		$user_id = uloginRegistrationUser($u_user);
	}
	if ($user_id > 0)
	{
		loginUser($u_user, $user_id);
	}
	else
	{
		echo "<div id='close-message'><div class='admin-message'>".$locale['SB_ups']."</div></div>\n";
		return false;
	}
	return true;
}

/*
 *
 */
function uloginGetUserFromToken($token = false)
{
	$response = false;
	if ($token)
	{
		global $settings;
		$data = array('cms' => 'php-fusion', 'version' => $settings['version']);
		$request = 'http://ulogin.ru/token.php?token='.$token.'&host='.$_SERVER['HTTP_HOST'].'&data='.base64_encode(json_encode($data));
		if (function_exists('curl_init'))
		{
			if (in_array('curl', get_loaded_extensions()))
			{
				$c = curl_init($request);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($c);
				curl_close($c);
			}
			elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')) $response = file_get_contents($request);
		}
	}
	return $response;
}

/**
 * Проверка пользовательских данных, полученных по токену
 *
 * @param $u_user - пользовательские данные
 *
 * @return bool
 */
function uloginCheckTokenError($u_user)
{
	global $locale;
	if (!is_array($u_user))
	{
		echo "<div id='close-message'><div class='admin-message'>".$locale['SB_error10']."</div></div>\n";
		return false;
	}
	if (isset($u_user['error']))
	{
		$strpos = strpos($u_user['error'], 'host is not');
		if ($strpos)
		{
			echo "<div id='close-message'><div class='admin-message'>".$locale['SB_error9']."</div></div>\n";
			return false;
		}
		switch ($u_user['error'])
		{
			case 'token expired':
				echo "<div id='close-message'><div class='admin-message'>".$locale['SB_error8']."</div></div>\n";
				return false;
				break;
			case 'invalid token':
				echo "<div id='close-message'><div class='admin-message'>".$locale['SB_error7']."</div></div>\n";
				return false;
				break;
			default:
				echo "<div id='close-message'><div class='admin-message'>".$locale['SB_error6']."</div></div>\n";
				return false;
				break;
		}
	}
	if (!isset($u_user['identity']))
	{
		echo "<div id='close-message'><div class='admin-message'>".$locale['SB_error5']."</div></div>\n";
		return false;
	}
	return true;
}

function getUserIdByIdentity($identity)
{
	$result = dbquery("SELECT user_id FROM ".DB_ulogin." WHERE identity = '".mysql_real_escape_string($identity)."'");
	$user_id['user_id'] = '';
	if (dbrows($result))
	{
		$user_id = dbarray($result);
	}
	return $user_id['user_id'];
}

function getUserByEmail($email)
{
	$result = dbquery("SELECT user_id FROM ".DB_PREFIX."users WHERE user_email = '".$email."'");
	if (dbrows($result))
	{
		$user_id = dbarray($result);
	}
	return $user_id['user_id'];
}

function getIdentityByUserId($user_id)
{
	$result = dbquery("SELECT * FROM ".DB_ulogin." WHERE user_id = '".$user_id."'");
	if (dbrows($result))
	{
		$user = dbarray($result);
	}
	return $user;
}

/**
 * Регистрация на сайте и в таблице uLogin
 *
 * @param Array $u_user - данные о пользователе, полученные от uLogin
 * @param int $in_db - при значении 1 необходимо переписать данные в таблице uLogin
 *
 * @return bool|int|Error
 */
function uloginRegistrationUser($u_user, $in_db = 0)
{
	global $locale;
	if (!isset($u_user['email']))
	{
		die($locale['SB_error4']._get_back_url());
	}
	$u_user['network'] = isset($u_user['network']) ? $u_user['network'] : '';
	// данные о пользователе есть в ulogin_table, но отсутствуют в WP
	if ($in_db == 1) dbquery("delete from ".DB_PREFIX."ulogin where identity = '".mysql_real_escape_string($u_user['identity'])."'");
	$user_id = getUserByEmail($u_user['email']);
	// $check_m_user == true -> есть пользователь с таким email
	$check_m_user = $user_id > 0 ? true : false;
	global $userdata;
	$current_user = isset($userdata['user_id']) ? $userdata['user_id'] : 0;
	// $is_logged_in == true -> ползователь онлайн
	$is_logged_in = $current_user > 0 ? true : false;
	if (($check_m_user == false) && !$is_logged_in)
	{
		if (isset($u_user['bdate']))
		{
			$bdate = explode('.', $u_user['bdate']);
			$u_user['bdate'] = $bdate[2].'-'.$bdate[1].'-'.$bdate[0];
		}
		else $u_user['bdate'] = '';
		global $settings;
		$user_login = ulogin_generateNickname($u_user['first_name'], $u_user['last_name'], $u_user['nickname'], $u_user['bdate']);
//		$settings['reg_group_ulogin'] = intval($settings['reg_group_ulogin']) ? intval($settings['reg_group_ulogin']) : '';
		require_once CLASSES."PasswordAuth.class.php";
		$seed = PasswordAuth::getNewRandomSalt();
		$alg = !empty($settings['password_algorithm']) ? $settings['password_algorithm'] : 'sha256';
		$password = PasswordAuth::getNewPassword();
		if ($alg == 'sha256')
		{
			$password = hash_hmac('sha256', $password, $seed);
		}
		else
		{
			$password = md5(md5($password));
		}
		$_IP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
		$user_location = $u_user['city'];
		$time = time();
		dbquery("INSERT INTO ".DB_PREFIX."users (user_name, user_salt, user_password, user_email, user_joined, user_lastvisit, user_rights, user_groups, user_level, user_threads, user_ip, user_sig, user_birthdate, user_location, user_web)
										VALUES ('$user_login', '$seed', '$password', '".$u_user['email']."', '$time', '$time', '', '', '101', '', '".$_IP."', '', '".$u_user['bdate']."', '$user_location','".$u_user['profile']."')");
		$u_id = dbquery("SELECT user_id FROM ".DB_PREFIX."users where user_name='".$user_login."'");
		$arr_id = dbarray($u_id);
		$user_id = $arr_id['user_id'];
		if ($user_id)
		{
			dbquery("INSERT INTO ".DB_ulogin." (user_id, identity, network) values ($user_id, '".$u_user['identity']."','".$u_user['network']."')");
			if (isset($u_user['photo'])) $u_user['photo'] = $u_user['photo'] === "https://ulogin.ru/img/photo.png" ? '' : $u_user['photo'];
			if (isset($u_user['photo_big'])) $u_user['photo'] = $u_user['photo_big'] === "https://ulogin.ru/img/photo_big.png" ? '' : $u_user['photo_big'];
			$photo_url = (isset($u_user['photo_big']) and !empty($u_user['photo_big'])) ? $u_user['photo_big'] : (isset($u_user['photo']) and !empty($u_user['photo'])) ? $u_user['photo'] : '';
			_uploadPhoto($photo_url, $user_id);
		}
		else
		{
			var_dump('2');
			var_dump($user_id);
			exit;
		}
		return $user_id;
	}
	else
	{
		// существует пользователь с таким email или это текущий пользователь
		if (!isset($u_user["verified_email"]) || intval($u_user["verified_email"]) != 1)
		{
			die('<script src="//ulogin.ru/js/ulogin.js"  type="text/javascript"></script><script type="text/javascript">uLogin.mergeAccounts("'.$_POST['token'].'")</script>'.$locale['SB_error3']._get_back_url());
		}
		if (intval($u_user["verified_email"]) == 1)
		{
			$user_id = $is_logged_in ? $current_user : $user_id;
			$other_u = getIdentityByUserId($user_id);
			if ($other_u)
			{
				if (!$is_logged_in && !isset($u_user['merge_account']))
				{
					die('<script src="//ulogin.ru/js/ulogin.js"  type="text/javascript"></script><script type="text/javascript">uLogin.mergeAccounts("'.$_POST['token'].'","'.$other_u['identity'].'")</script>'.$locale['SB_error2']._get_back_url());
				}
			}
			dbquery("INSERT INTO ".DB_ulogin." (user_id, identity, network) values ($user_id, '".$u_user['identity']."','".$u_user['network']."')");
			return $user_id;
		}
	}
	return false;
}

/**
 * Гнерация логина пользователя
 * в случае успешного выполнения возвращает уникальный логин пользователя
 *
 * @param $first_name
 * @param string $last_name
 * @param string $nickname
 * @param string $bdate
 * @param array $delimiters
 *
 * @return string
 */
function ulogin_generateNickname($first_name, $last_name = "", $nickname = "", $bdate = "", $delimiters = array('.', '_'))
{
	$delim = array_shift($delimiters);
	$first_name = ulogin_translitIt($first_name);
	$first_name_s = substr($first_name, 0, 1);
	$variants = array();
	if (!empty($nickname))
	{
		$variants[] = $nickname;
	}
	$variants[] = $first_name;
	if (!empty($last_name))
	{
		$last_name = ulogin_translitIt($last_name);
		$variants[] = $first_name.$delim.$last_name;
		$variants[] = $last_name.$delim.$first_name;
		$variants[] = $first_name_s.$delim.$last_name;
		$variants[] = $first_name_s.$last_name;
		$variants[] = $last_name.$delim.$first_name_s;
		$variants[] = $last_name.$first_name_s;
	}
	if (!empty($bdate))
	{
		$date = explode('.', $bdate);
		$variants[] = $first_name.$date[2];
		$variants[] = $first_name.$delim.$date[2];
		$variants[] = $first_name.$date[0].$date[1];
		$variants[] = $first_name.$delim.$date[0].$date[1];
		$variants[] = $first_name.$delim.$last_name.$date[2];
		$variants[] = $first_name.$delim.$last_name.$delim.$date[2];
		$variants[] = $first_name.$delim.$last_name.$date[0].$date[1];
		$variants[] = $first_name.$delim.$last_name.$delim.$date[0].$date[1];
		$variants[] = $last_name.$delim.$first_name.$date[2];
		$variants[] = $last_name.$delim.$first_name.$delim.$date[2];
		$variants[] = $last_name.$delim.$first_name.$date[0].$date[1];
		$variants[] = $last_name.$delim.$first_name.$delim.$date[0].$date[1];
		$variants[] = $first_name_s.$delim.$last_name.$date[2];
		$variants[] = $first_name_s.$delim.$last_name.$delim.$date[2];
		$variants[] = $first_name_s.$delim.$last_name.$date[0].$date[1];
		$variants[] = $first_name_s.$delim.$last_name.$delim.$date[0].$date[1];
		$variants[] = $last_name.$delim.$first_name_s.$date[2];
		$variants[] = $last_name.$delim.$first_name_s.$delim.$date[2];
		$variants[] = $last_name.$delim.$first_name_s.$date[0].$date[1];
		$variants[] = $last_name.$delim.$first_name_s.$delim.$date[0].$date[1];
		$variants[] = $first_name_s.$last_name.$date[2];
		$variants[] = $first_name_s.$last_name.$delim.$date[2];
		$variants[] = $first_name_s.$last_name.$date[0].$date[1];
		$variants[] = $first_name_s.$last_name.$delim.$date[0].$date[1];
		$variants[] = $last_name.$first_name_s.$date[2];
		$variants[] = $last_name.$first_name_s.$delim.$date[2];
		$variants[] = $last_name.$first_name_s.$date[0].$date[1];
		$variants[] = $last_name.$first_name_s.$delim.$date[0].$date[1];
	}
	$i = 0;
	$exist = true;
	while (true)
	{
		if ($exist = ulogin_userExist($variants[$i]))
		{
			foreach ($delimiters as $del)
			{
				$replaced = str_replace($delim, $del, $variants[$i]);
				if ($replaced !== $variants[$i])
				{
					$variants[$i] = $replaced;
					if (!$exist = ulogin_userExist($variants[$i])) break;
				}
			}
		}
		if ($i >= count($variants) - 1 || !$exist) break;
		$i++;
	}
	if ($exist)
	{
		while ($exist)
		{
			$nickname = $first_name.mt_rand(1, 100000);
			$exist = ulogin_userExist($nickname);
		}
		return $nickname;
	}
	else
		return $variants[$i];
}

/**
 * Транслит
 */
function ulogin_translitIt($str)
{
	$tr = array("А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ж" => "j", "З" => "z", "И" => "i", "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y", "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya");
	if (preg_match('/[^A-Za-z0-9\_\-]/', $str))
	{
		$str = strtr($str, $tr);
		$str = preg_replace('/[^A-Za-z0-9\_\-\.]/', '', $str);
	}
	return $str;
}

/**
 * Проверка существует ли пользователь с заданным логином
 */
function ulogin_userExist($login)
{
	$check = dbquery("SELECT user_id FROM ".DB_PREFIX."users where user_name='".$login."'");
	if (dbrows($check) == 0)
	{
		return false;
	}
	return true;
}

/*
 *
 */
function loginUser($u_user, $user_id)
{
	$member_id = dbquery("SELECT * FROM ".DB_PREFIX."users where user_id=".$user_id);
	$member = dbarray($member_id);
	$ulogin_pass = dbquery("SELECT * FROM ".DB_ulogin." where user_id=".$user_id." ORDER by id ASC");
	$member_pass = dbarray($ulogin_pass);
	if (!empty($u_user['bdate']) && isset($u_user['bdate']) && empty($member['user_birthdate']))
	{
		$bdate = explode('.', $u_user['bdate']);
		$u_user['bdate'] = $bdate[2].'-'.$bdate[1].'-'.$bdate[0];
		dbquery("UPDATE ".DB_PREFIX."users set user_birthdate='".$u_user['bdate']."' where user_id = $user_id");
	}
	if (empty($member['user_avatar']))
	{
		if (isset($u_user['photo'])) $u_user['photo'] = $u_user['photo'] === "https://ulogin.ru/img/photo.png" ? '' : $u_user['photo'];
		if (isset($u_user['photo_big'])) $u_user['photo'] = $u_user['photo_big'] === "https://ulogin.ru/img/photo_big.png" ? '' : $u_user['photo_big'];
		$photo_url = (isset($u_user['photo_big']) and !empty($u_user['photo_big'])) ? $u_user['photo_big'] : (isset($u_user['photo']) and !empty($u_user['photo'])) ? $u_user['photo'] : '';
		_uploadPhoto($photo_url, $user_id);
	}
	Authenticate::setUserCookie($user_id, $member['user_salt'], $member['user_algo'], false, true);
	unset($member);
	redirect(ulogin_get_current_page_url());
}

function getPhpFusionUser($user_id)
{
	$member_id = dbquery("SELECT user_id FROM ".DB_PREFIX."users where user_id=".$user_id);
	return dbarray($member_id);
}

/**
 * @param $user_id
 *
 * @return bool
 */
function uloginCheckUserId($user_id)
{
	global $userdata;
	global $locale;
	$current_user = isset($userdata['user_id']) ? $userdata['user_id'] : 0;
	if (($current_user > 0) && ($user_id > 0) && ($current_user != $user_id))
	{
		die($locale['SB_error1']._get_back_url());
	}
	return true;
}

/*
 *
 *
 */
function _uploadPhoto($url, $user_id)
{
	global $settings;
	$ext = strtolower(substr($url, -3));
	if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp'))) $ext = 'jpg';
	$tmpName = md5(rand(8,8)).'.'.$ext;
	$res = @copy($url, IMAGES."avatars/".$tmpName);
	if ($res)
	{
		require_once INCLUDES."photo_functions_include.php";
		$imagefile = getimagesize(IMAGES."avatars/".$tmpName);
		$foto_name = "foto_".$user_id.".".$ext;
		if ($settings['avatar_ratio'] == 0)
		{
			createthumbnail($imagefile[2], IMAGES."avatars/".$tmpName, IMAGES."avatars/".$foto_name, $settings['avatar_width'], $settings['avatar_height']);
		}
		else
		{
			createsquarethumbnail($imagefile[2], IMAGES."avatars/".$tmpName, IMAGES."avatars/".$foto_name, $settings['avatar_width']);
		}
		@unlink(IMAGES.'avatars/'.$tmpName);
		dbquery("UPDATE ".DB_PREFIX."users set user_avatar='$foto_name' where user_id=$user_id");
	}
}

/**
 * @param int $place
 *
 * @return string
 */
function getPanelCode($place = 0)
{
	/*
	 * Выводит в форму html для генерации виджета
	 */
	$ulogin_default_options = array();
	$ulogin_default_options['display'] = 'small';
	$ulogin_default_options['providers'] = 'vkontakte,odnoklassniki,mailru,facebook';
	$ulogin_default_options['fields'] = 'first_name,last_name,email,photo,photo_big';
	$ulogin_default_options['optional'] = 'phone';
	$ulogin_default_options['hidden'] = 'other';
	$inf_settings = get_settings("ulogin_panel");
	$ulogin_options = array();
	$ulogin_options['ulogin_id1'] = $inf_settings['uloginid1'];
	$ulogin_options['ulogin_id2'] = $inf_settings['uloginid2'];
	$default_panel = false;
	$redirect_uri = ulogin_get_current_page_url();
	switch ($place)
	{
		case 0:
			$ulogin_id = $ulogin_options['ulogin_id1'];
			break;
		case 1:
			$ulogin_id = $ulogin_options['ulogin_id2'];
			break;
		default:
			$ulogin_id = $ulogin_options['ulogin_id1'];
	}
	if (empty($ulogin_id))
	{
		$ul_options = $ulogin_default_options;
		$default_panel = true;
	}
	$panel = '';
	$panel .= '<div class="ulogin_panel"';
	if ($default_panel)
	{
		$ul_options['redirect_uri'] = urlencode($redirect_uri);
		$x_ulogin_params = '';
		foreach ($ul_options as $key => $value) $x_ulogin_params .= $key.'='.$value.';';
		if ($ul_options['display'] != 'window') $panel .= ' data-ulogin="'.$x_ulogin_params.'"></div>';
		else
			$panel .= ' data-ulogin="'.$x_ulogin_params.'" href="#"><img src="https://ulogin.ru/img/button.png" width=187 height=30 alt="МультиВход"/></div>';
	}
	else
		$panel .= ' data-uloginid="'.$ulogin_id.'" data-ulogin="redirect_uri='.urlencode($redirect_uri).'"></div>';
	$panel = '<div class="ulogin_block place'.$place.'" style="text-align: center;">'.$panel.'</div><div style="clear:both"></div>';
	return $panel;
}

/**
 * Возвращает текущий url
 */
function ulogin_get_current_page_url()
{
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]))
	{
		if ($_SERVER["HTTPS"] == "on")
		{
			$pageURL .= "s";
		}
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

/**
 * Вывод списка аккаунтов пользователя
 *
 * @param int $user_id - ID пользователя (если не задан - текущий пользователь)
 *
 * @return string
 */
function getSyncPanel($user_id = 0)
{
	global $userdata;
	$current_user = isset($userdata['user_id']) ? $userdata['user_id'] : 0;
	$user_id = empty($user_id) ? $current_user : $user_id;
	if (empty($user_id)) return '';
	$networks = getMultiByUserId($user_id);
	$output = '';
	if ($networks)
	{
		$output .= '<div class="ulogin_accounts">';
		foreach ($networks as $network)
		{
			$output .= "<div data-ulogin-network='{$network['network']}' style='display:inline-block; margin:10px 5px 10px 0px;' data-ulogin-identity='{$network['identity']}' class='ulogin_network big_provider {$network['network']}_big'></div>";
		}
		$output .= '</div><div style="clear: both"></div>';
	}
	return $output;
}

function getMultiByUserId($user_id)
{
	$result = array();
	$member_b = dbquery("SELECT * FROM ".DB_ulogin." where user_id=".$user_id);
	for ($i = dbrows($member_b), $j = 0;$i > $j;$i--)
	{
		$result[] = dbarray($member_b, $i);
	}
	return $result;
}

/**
 * Возвращает Back url в html формате
 */
function _get_back_url()
{
	global $locale;
	$back = base64_decode($_GET['back']);
	$back = isset($back) ? $back : ulogin_get_current_page_url();
	$backURL = '<br/><a href="'.$back.'">'.$locale['SB_ulogin_back'].'</a>';
	return $backURL;
}

?>