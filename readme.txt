=== uLogin - ������ ����������� ����� ���������� ���� ===

Donate link: http://ulogin.ru/
Tested up to: 7.02.04
Last tested: 28.03.2012
Tags: ulogin, login, social, authorization
License: GPL3
����� ����������� uLogin ����� ���������� ����. ���������� ������ loginza.

== Description ==

uLogin � ��� ����������, ������� ��������� ������������� �������� ������ ������ � ��������� ��������-�������� ��� ������������� ��������� �����������,
� ���������� ������ � �������� �������������� ������ �������� �� ���������� ����� � ���������� �������� (Google, ������, Mail.ru, ���������, Facebook � ��.)

== Installation ==

1) - ���������� ���� ulogin.php � /includes/

2) - � ���� infusions/user_info_panel/user_info_panel.php ���� ����� ����������� (����� echo "</form>\n<br />\n";) ���������:
		echo '<script src="http://ulogin.ru/js/ulogin.js"></script>
			<div id="uLogin" x-ulogin-params="display=small&fields=first_name,last_name,photo,email,bdate,nickname,city&providers=vkontakte,odnoklassniki,mailru,facebook&hidden=twitter,google,yandex,livejournal,openid&redirect_uri='.$ulogin_url.'"></div>
			<br>';

3) - � ���� /maincore.php ��������� � �����:
		include INCLUDES."ulogin.php";

uLogin ��� ��������!

��������� ���� ��������� � ������� ( Admin Panel -> Settings -> Registration ) ����� ������ ������ �� ��������� ��� �������������, �������������������� � ������� uLogin.
���� ��� ����� ��� �� �����, ���������� �������� ����� �� ���������.
(����� �������� ������ ��� ������� ��������� ����� �������������)

4) -� ���� administration/settings_registration.php
	����� �������
		redirect(FUSION_SELF.$aidlink."&error=".$error);
	��������
		updSettingToUlogin();
		
   -� ��� �� ����,
	����� �������
		echo "<td class='tbl' colspan='2'>".$locale['559']."</td>\n";
	��������
		selectToUlogin();
			
5) -� ���� /locale/Russian/admin/settings.php ( ���� �� ����������� ���������� ������, ����� ��������� ���� /locale/English/admin/settings.php )
	� ���� // Registration Settings
	��������
		$locale['5551'] = "�������� �������������� ����� ULogin ������������� � ������:";
	����� ��, ������� ��, ������ ��������.


== Unicode vs Windows-1251 ==

���� � ��� ��������� �������� � ���������� � ������, ���������� ����� uLogin,
���������� ���������������� ���� 
	//For Non-Unicode version of PHP-Fusion
� ����� ulogin.php

=============================