<?php
/**
 * Настройки сервера AVTO-M. Скопируйте в app/config.php и заполните.
 * Файл config.php не попадает в архив и в git — пароли только здесь.
 */
return [
  // Адрес сайта без косой черты в конце
  'site_url' => 'https://avto-m.pro',

  // Почта для отправки уведомлений о заявках (SMTP).
  // Почта рег.ру на хостинге: smtp_host = 'mail.hosting.reg.ru', порт 465, ssl.
  // Яндекс 360: 'smtp.yandex.ru', 465, ssl (нужен пароль приложения).
  // VK WorkSpace / Почта Mail для бизнеса: 'smtp.mail.ru', 465, ssl (пароль для внешних приложений).
  'smtp_host' => '',
  'smtp_port' => 465,
  'smtp_secure' => 'ssl',          // ssl (порт 465) или tls (порт 587)
  'smtp_user' => 'info@avto-m.pro',
  'smtp_pass' => '',
  'mail_from' => 'info@avto-m.pro', // обычно совпадает с smtp_user
  'mail_from_name' => 'Сайт AVTO-M',

  // Сколько ежедневных резервных копий хранить
  'backup_keep' => 14,

  // Ключ для планировщика (cron): https://avto-m.pro/cron?key=… — делает резервную копию раз в сутки.
  // Придумайте длинную случайную строку (латиница и цифры).
  'cron_key' => '',

  // Только для локальной разработки
  'dev' => false,
];
