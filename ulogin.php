<?php 
//=====================================================
// ULogin PHP-Fusion
//-----------------------------------------------------
// Модуль авторизации и регистрации при помощи uLogin
//-----------------------------------------------------
// http://ulogin.ru/
// team@ulogin.ru
// License GPL3
//-----------------------------------------------------
// Copyright (c) 2011-2012 uLogin
//=====================================================

/* создаем таблицу в БД (если она отсутствует) */
dbquery("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ulogin (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(10) unsigned NOT NULL,
	`ident` char(255) NOT NULL,
	`email` char(255) DEFAULT NULL,
	`seed` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM;");

/* если админ - определяем функции для админки */
if(iADMIN) {
	/* функция, изменяющая опцию в базе, либо (при отсутствии записи) создающая ее */
	function updSettingToUlogin(){
		global $error;
		$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$_POST['reg_group_ulogin']."' WHERE settings_name='reg_group_ulogin'");
		if (mysql_affected_rows() === 0) {
			$result = dbquery("INSERT INTO ".DB_SETTINGS." (settings_name,settings_value) VALUES ('reg_group_ulogin','".$_POST['reg_group_ulogin']."')");
			if (!$result) { 
				$error = 1;
			}
		}
	}
	/* функция, выводящая меню выбора группы в админке */
	function selectToUlogin(){
		global $settings2;
		$ug = dbquery( "SELECT group_id,group_name FROM " . DB_PREFIX . "user_groups " );
		if (dbrows($ug)){
			$label = "Помещать пользователей авторизующихся через ULogin в группе:";
			echo "<td width='50%' class='tbl'>".$label."</td>\n";
			echo "<td width='50%' class='tbl'><select name='reg_group_ulogin' class='textbox'>\n";
			$sel_no = " selected='selected'";
			while($arr_ug = dbarray($ug)){
				if (@$settings2['reg_group_ulogin'] == $arr_ug['group_id']) {
					$sel = " selected='selected'";
					$sel_no = '';
				} else $sel = '';
				echo "<option value='".$arr_ug['group_id']."'".$sel.">".$arr_ug['group_name']."</option>\n";
			}
			echo "<option value=''".$sel_no.">Нет</option>\n";
			echo "</select></td>\n";
			echo "</tr>\n<tr>\n";
		}
	}
	
/* если гость - готовим uLogin к работе */
} elseif (!iMEMBER) {
	
	$ulogin_url = urlencode('http://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'] );
	$time = time();
	
	/* функция, инициализирующая вход */
	function register_user($id,$pass) {		
		global $settings,$_IP,$user,$time;
		$_IP = mysql_real_escape_string( $_SERVER['REMOTE_ADDR'] );
		$member_id = dbquery("SELECT user_id,user_name FROM " . DB_PREFIX . "users where user_id=".$id );
		$member = dbarray($member_id);

		$_POST['login'] = $member['user_name'];
		$_POST['user_name'] = $member['user_name'];
		$_POST['user_pass'] = $pass;
		
		if ($settings['login_method'] == "cookies") {
			/* если метод авторизации - cookie, вызываем скрипт для проведения авторизации */
			include(INCLUDES."cookie_include.php");
		} elseif ($settings['login_method'] == "sessions") {
			/* если метод авторизации - сессии, устанавливаем переменные и входим */
			$_SESSION[COOKIE_PREFIX.'user_id'] = $id;
			$_SESSION[COOKIE_PREFIX.'user_pass'] = md5($pass);
			$_SESSION[COOKIE_PREFIX.'lastvisit'] = $time;
			redirect(BASEDIR."setuser.php?user=".$member['user_name'], true);
		}
	}

	/* если получили token */
	if(isset($_POST['token'])){

		/* получаем и декодируем данные, на выходе имеем ассоциативный массив $user */
		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
		$user = json_decode($s, true);

		/* проверяем, существует ли такой пользователь в базе */
		$member_b = dbquery( "SELECT user_id,seed FROM " . DB_PREFIX . "ulogin where ident='".mysql_real_escape_string($user['identity'])."'" );
		if(dbrows($member_b)) {
			$member_id = dbarray($member_b);
			$password=$user['identity'].$member_id['seed'];
			$member_b = dbquery( "SELECT user_id FROM " . DB_PREFIX . "users where user_id='".$member_id['user_id']."'" );
			if(!dbrows($member_b)) {
				dbquery("delete from ".DB_PREFIX."ulogin where ident='".mysql_real_escape_string($user['identity'])."'");
			}
		}

		/* если пользователь уже существуем, передаем его данные в функцию авторизации */
		if(dbrows($member_b))
			register_user($member_id['user_id'],$password);
		else {
			/* Функция транслитерации и обрезания логина, если он больше допустимой длины */
			function transCrop($name,$crop){
				$tr = array(
					"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
					"У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH","Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
					"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
					"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
				);
				$name = strtr($name,$tr);
				while(strlen($name) > $crop) {
					$name = substr($name, 0, strlen($name)-1);
				}
				return $name;
			}
			$login = $user['first_name'].'_'.$user['last_name'];	// логин = имя_фамилия
			$nickname = @$user['nickname'];							// никнейм
			
		//For Non-Unicode version of PHP-Fusion
		//	   function convert_unicode($t) {
		//		   	$to = 'windows-1251';
		//			if( function_exists( 'iconv' ) ) $t = iconv( "UTF-8", $to."//IGNORE", $t );
		//			else $t = "The library iconv is not supported by your server";	
		//		   	return $t;
		//	  }
		//	  $login=convert_unicode($login);
		//	  $nickname=convert_unicode($nickname);
		//END for Non-Unicode version
		
			$login=addslashes($login);
			
			/* если длина логина больше допустимой (определяется параметрами таблицы в БД), пробуем присвоить никнейм,
 			   если опять больше - вызываем функцию транслитерации и, если потребуется, обрезаем логин */
			if (strlen($login) > 27) {
				if (isset($nickname) and !empty($nickname)) $login = $nickname;
				if (strlen($login) > 27) {
					$login = transCrop($login,27);
				}
			}
			
			if(isset($user['photo'])){
				$photo = $user['photo'];
			} else $photo ="";
			
			$email = $user['email'];
			$email = $user['email'];
			$be = dbquery( "SELECT COUNT(*) as how FROM " . DB_PREFIX . "users where user_email='".$email."'" );
			$re = dbarray($be);
			if($re['how']>0){
				$res=preg_match('/^([^\@]+)\@([^\@]+)$/',$email,$matches);
				$nemail=$matches[1].'+'.$user['network'];
				$i=0;
				do {
					$email=$nemail;
					$be=dbquery( "SELECT COUNT(*) as how FROM " . DB_PREFIX . "users where user_email='".mysql_real_escape_string($email.'@'.$matches[2])."'" );
					$re = dbarray($be);
					$i++;
					$nemail=$matches[1].'+'.$user['network'].'_'.$i;
				} while($re['how']>0);
				$email.='@'.$matches[2];
			}
			
			$city = !empty($user['city']) ? $user['city'] : '';
			$bdate = explode('.',$user['bdate']);
			$user_birthdate = $bdate[2].'-'.$bdate[1].'-'.$bdate[0];
			
			/* проверяем логин на занятость, если занят - добавляем индекс */
			$cnb=dbquery( "SELECT COUNT(*) as how FROM " . DB_PREFIX . "users where user_name='".$login."'" );
			$cnt = dbarray($cnb);
			if($cnt['how']>0){
				$i=0;
				do {
					$i++;
					$cnb=dbquery( "SELECT COUNT(*) as how FROM " . DB_PREFIX . "users where user_name='".$login.'_'.$i."'" );
					$cnt = dbarray($cnb);
				} while($cnt['how']>0);
				$login.='_'.$i;
			}
			
			$settings['reg_group_ulogin'] = intval( $settings['reg_group_ulogin'] ) ? intval( $settings['reg_group_ulogin'] ) : '';
			$seed=mt_rand();
			$password = md5(md5($user['identity'].$seed));

			$_IP = mysql_real_escape_string( $_SERVER['REMOTE_ADDR'] );
			
			/* добавляем нового пользователя в базу */ 
			dbquery( "INSERT INTO " . DB_PREFIX . "users (user_name, user_password, user_email, user_joined, user_lastvisit, user_rights, user_groups, user_level, user_threads, user_ip, user_sig, user_birthdate, user_location)
										VALUES ('$login', '$password', '$email', '$time', '$time', '', '" . $settings['reg_group_ulogin'] . "', '101', '', '" . $_IP . "', '', '$user_birthdate', '$city')" );
										
			/* получаем id нового пользователя */
			$u_id = dbquery( "SELECT user_id FROM " . DB_PREFIX . "users where user_name='".$login."' and user_password='".$password."' LIMIT 1" );
			if (dbrows($u_id)){
				$arr_id = dbarray($u_id);
				$user_id = $arr_id['user_id'];
				
				/* добавляем нового пользователя в базу ..._ulogin */
				dbquery("INSERT INTO " . DB_PREFIX . "ulogin (user_id, ident, email, seed) values ($user_id, '".$user['identity']."','".$email."', $seed)");
				
				/* если есть фото - вызываем файл с функциями обработки, обрабатываем и сохраняем аватару */
				if (isset($photo) and !empty($photo))  {
					$fparts = pathinfo($photo);
					$tmp_name = $fparts['filename'];
					$type = $fparts['extension'];
					$res = @copy($photo, IMAGES."avatars/".$tmp_name);
					if( $res ) {
						require_once INCLUDES."photo_functions_include.php";
						$imagefile = getimagesize(IMAGES."avatars/".$tmp_name);
						$foto_name = "foto_" . $user_id . "." . $type;
						if ($settings['avatar_ratio'] == 0) {
							createthumbnail($imagefile[2], IMAGES."avatars/".$tmp_name, IMAGES."avatars/" . $foto_name, $settings['avatar_width'], $settings['avatar_height']); 
						} else {
							createsquarethumbnail($imagefile[2], IMAGES."avatars/".$tmp_name, IMAGES."avatars/" . $foto_name, $settings['avatar_width']);
						}
						@unlink( IMAGES."avatars/".$tmp_name );
						dbquery( "UPDATE " . DB_PREFIX . "users set user_avatar='$foto_name' where user_id=$user_id" );
					}
				}
				
				/* передаем данные в функцию авторизации */
				register_user($user_id,$user['identity'].$seed);
			}
		}
		unset($_POST['token']);
	}
}
?>