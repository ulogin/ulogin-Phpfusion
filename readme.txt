=== uLogin - виджет авторизации через социальные сети ===

Donate link: http://ulogin.ru/
Tested up to: 7.02.04
Last tested: 28.03.2012
Tags: ulogin, login, social, authorization
License: GPL3
Форма авторизации uLogin через социальные сети. Улучшенный аналог loginza.

== Description ==

uLogin — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)

== Installation ==

1) - Скопируйте файл ulogin.php в /includes/

2) - В файл infusions/user_info_panel/user_info_panel.php ниже формы авторизации (после echo "</form>\n<br />\n";) вставляем:
		echo '<script src="http://ulogin.ru/js/ulogin.js"></script>
			<div id="uLogin" x-ulogin-params="display=small&fields=first_name,last_name,photo,email,bdate,nickname,city&providers=vkontakte,odnoklassniki,mailru,facebook&hidden=twitter,google,yandex,livejournal,openid&redirect_uri='.$ulogin_url.'"></div>
			<br>';

3) - В файл /maincore.php добавляем в конец:
		include INCLUDES."ulogin.php";

uLogin уже работает!

Следующие шаги добавляют в админке ( Admin Panel -> Settings -> Registration ) опцию выбора группы по умолчанию для пользователей, зарегистрировавшихся с помощью uLogin.
Если эта опция Вам не нужна, дальнейшие действия можно не выполнять.
(опция появится только при наличии созданных групп пользователей)

4) -В файл administration/settings_registration.php
	ПЕРЕД строкой
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	добавить
		updSettingToUlogin();
		
   -В тот же файл,
	ПЕРЕД строкой
		echo "<td class='tbl' colspan='2'>".$locale['559']."</td>\n";
	добавить
		selectToUlogin();
			
5) -В файл /locale/Russian/admin/settings.php ( если Вы используете английскую версию, тогда требуется файл /locale/English/admin/settings.php )
	в блок // Registration Settings
	добавить
		$locale['5551'] = "Помещать авторизующихся через ULogin пользователей в группе:";
	Текст Вы, конечно же, можете изменить.


== Unicode vs Windows-1251 ==

Если у Вас возникают проблемы с кодировкой у данных, получаемых через uLogin,
попробуйте закомментировать блок 
	//For Non-Unicode version of PHP-Fusion
в файле ulogin.php

=============================