=== uLogin - ������ ����������� ����� ���������� ���� ===
Donate link: http://ulogin.ru/
Tested up to: 7.01.06
Tags: ulogin, login, social, authorization
License: GPL3

����� ����������� uLogin ����� ���������� ����. ���������� ������ loginza.

== Description ==

uLogin � ��� ����������, ������� ��������� ������������� �������� ������ ������ � ��������� ��������-�������� ��� ������������� ��������� �����������,
� ���������� ������ � �������� �������������� ������ �������� �� ���������� ����� � ���������� �������� (Google, ������, Mail.ru, ���������, Facebook � ��.)

== Installation ==

�������� ������� � �� (<prefix_>ulogin) ���������� �������������.

1) - ���������� ���� ulogin.php � /includes/

2) - � ���� infusions/user_info_panel/user_info_panel.php ���� ����� ����������� (����� echo "</form>\n<br />\n";) ���������:
		echo '<script src="http://ulogin.ru/js/ulogin.js"></script>
			<div id="uLogin" x-ulogin-params="display=small&fields=first_name,last_name,photo,email,bdate,nickname,city&providers=vkontakte,odnoklassniki,mailru,facebook&hidden=twitter,google,yandex,livejournal,openid&redirect_uri='.$ulogin_url.'"></div>
			<br>';

3) - � ���� /maincore.php ��������� � �����:
		include INCLUDES."ulogin.php";
		
uLogin ��� ��������!

��������� ���� ��������� � ������� ( Admin Panel -> Settings -> Registration) ����� ������ ������ �� ��������� ��� �������������, �������������������� � ������� uLogin.
���� ��� ����� ��� �� �����, ���������� �������� ����� �� ���������.
(����� �������� ������ ��� ������� ��������� ����� �������������)
	
4) - � ���� administration/settings_registration.php
		����� �������	
			redirect(FUSION_SELF.$aidlink."&error=".$error);	
		��������	
			updSettingToUlogin();	
	   -� ��� �� ����, 
		����� �������	
			echo "<td class='tbl' colspan='2'>".$locale['559']."</td>\n";	
		��������	
			selectToUlogin();

			
���� �� ����������� PHP-Fusion � ���������� windows-1251, ���������� ���� ��������� �������� � ������ uLogin:
	- � ����� ulogin.php ���������������� ����: //For Non-Unicode version of PHP-Fusion


