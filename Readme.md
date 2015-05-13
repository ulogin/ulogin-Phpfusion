# uLogin

Donate link: http://ulogin.ru
Tags: ulogin, login, social, authorization
Requires at least: 7.x.x
Tested up to: 7.02.07
Stable tag: 2.0.0
License: GNU General Public License, version 2

**uLogin** — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток пользователей из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)

## Установка

- Скопируйте модуль ulogin_panel в папку /infusions/.
- Зайдите в *Панель администратора*, вкладка *Администрирование системы*, раздел *Плагины* и установите его.
- Установленный модуль заработает сразу после установки с настройками по умолчанию.
- Чтобы установить панель синхронизации в профиль пользователя необходимо в файл edit_profile.php, который находится в корневой папке, добавить строку:

	include_once INFUSIONS."ulogin_panel/ulogin_sync.php";

Например, после 59 строки:

	opentable($locale['u102']);

Более детальную информацию смотрите на сайте https://ulogin.ru/help.php


## Модуль "uLogin"

Настройки данного модуля находится в *Панеле администратора* во вкладке *Плагины*.

####Настройки плагина *ulogin*:

**uLogin ID форма авторизации:** общее поле для всех виджетов uLogin, необязательный параметр (см. *"Настройки виджета uLogin"*);
**uLogin ID форма синхронизации** общее поле для всех виджетов uLogin, необязательный параметр (см. *"Настройки виджета uLogin"*);

Более детальную информацию смотрите на сайте https://ulogin.ru/help.php

## Настройки виджета uLogin

При установке расширения uLogin авторизация пользователей будет осуществляться с настройками по умолчанию.
Для более детальной настройки виджетов uLogin Вы можете воспользоваться сервисом uLogin.

Вы можете создать свой виджет uLogin и редактировать его самостоятельно:

для создания виджета необходимо зайти в Личный Кабинет (ЛК) на сайте http://ulogin.ru/lk.php
добавить свой сайт к списку Мои сайты и на вкладке Виджеты добавить новый виджет. После этого вы можете отредактировать свой виджет.

В графе «Возвращаемые поля профиля пользователя» вы можете включить поля, например, **Город** и **Дата рождения**.

**Важно!** Для успешной работы плагина необходимо включить в обязательных полях профиля поле **Еmail** в Личном кабинете uLogin.
Заполнять поля в графе «Тип авторизации» не нужно, т.к. расширение uLogin настроено на автоматическое заполнение данных параметров.

Созданный в Личном Кабинете виджет имеет параметры **uLogin ID**.
Скопируйте значение **uLogin ID** вашего виджета в соответствующее поле в настройках плагина на вашем сайте и сохраните настройки.

Если всё было сделано правильно, виджет изменится согласно вашим настройкам.


## Особенности

Для вывода панели *Регистрации/Авторизации* в любом месте шаблона шаблона php-Fusion добавьте строку:

	include_once INFUSIONS."ulogin_panel/ulogin_panel.php";

Для вывода панели *Синхронизации* в любом месте шаблона php-Fusion добавьте строку:

	include_once INFUSIONS."ulogin_panel/ulogin_sync.php";

Для вывода только виджета *Регистрации/Авторизации* в любом месте шаблона php-Fusion используйте функцию:

    /**
   	* Вывод панели uLogin
    * int $panel - номер uLogin панели, соответствует указанным в настройках плагина полям с ID (значение 0 - для первого виджета авторизации/регистрации, 1 - для виджета синхронизации)
    * @return string
    */
   	function getPanelCode($place = 0)

Например:

	echo getPanelCode(0);
или

	echo getPanelCode(1);

**Важно!** Данный пример не будет работать без предварительного подключения файла ulogin.php, например, так:
	require_once INFUSIONS."ulogin_panel/ulogin.php";


Для вывода только списка подключённых аккаунтов пользователя (виджета *Синхронизации*) в любом месте шаблона php-Fusion используйте функцию:

	/**
	 * Вывод списка аккаунтов пользователя
	 * @param int $user_id - ID пользователя (если не задан - текущий пользователь)
	 * @return string
	 */
   	function getSyncPanel($place = 0)

Например:

	echo getSyncPanel();

**Важно!** Данный пример не будет работать без предварительного подлючения файла ulogin.php, например, так:
	require_once INFUSIONS."ulogin_panel/ulogin.php";


## Изменения

####2.0.0.
- Модуль реализован как infusions;
- Полное изменение структуры модуля согласно документации php-Fusion 7;
- Изменение функции генерации пароля на более сложный;
- Изменение функции генерации salt;
- Изменение сценария авторизации;
- Функционал модуля вынесен в отдельный файл ulogin.php;
- Настройки модуля теперь доступны в Панеле администратора во вкладке Плагины;
- Добавлен функционал плагина для работы с виджетами uLogin (http://ulogin.ru/lk.php);
- Добавлен функционал привязки социальных сетей, предоставляемых uLogin, к текущему профилю (Синхронизация Аккаунтов);
- Подготовка модуля к переводу на другие языки.

####1.0.0.
- Релиз.

