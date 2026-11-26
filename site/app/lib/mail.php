<?php
if (!defined('AVTOM')) { http_response_code(404); exit; }

/**
 * Отправка письма через SMTP (настройки в config.php). Без SMTP — через mail() хостинга.
 * Возвращает null при успехе или текст ошибки.
 */
function mail_send($to, $subject, $html, $text = '', $replyTo = '') {
  $to = array_values(array_filter(array_map('trim', is_array($to) ? $to : preg_split('~[,;\s]+~', (string)$to)), fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
  if (!$to) return 'Не указан адрес получателя.';
  $from = cfg('mail_from') ?: (cfg('smtp_user') ?: 'noreply@' . (parse_url(cfg('site_url'), PHP_URL_HOST) ?: 'localhost'));
  $fromName = cfg('mail_from_name', 'Сайт AVTO-M');
  $text = $text ?: trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</tr>', '</h2>'], "\n", $html)), ENT_QUOTES, 'UTF-8'));
  $b = 'b' . bin2hex(random_bytes(8));
  $enc = fn($s) => '=?UTF-8?B?' . base64_encode($s) . '?=';
  $headers = [
    'Date: ' . date('r'),
    'From: ' . $enc($fromName) . ' <' . $from . '>',
    'To: ' . implode(', ', $to),
    'Subject: ' . $enc($subject),
    'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . (parse_url(cfg('site_url'), PHP_URL_HOST) ?: 'avto-m.pro') . '>',
    'MIME-Version: 1.0',
    'Content-Type: multipart/alternative; boundary="' . $b . '"',
  ];
  if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: ' . $replyTo;
  $body = "--$b\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($text))
    . "--$b\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html)) . "--$b--\r\n";

  if (!cfg('smtp_host')) {
    $hdr = array_values(array_filter($headers, fn($x) => !preg_match('~^(To|Subject):~', $x)));
    $ok = @mail(implode(', ', $to), $enc($subject), $body, implode("\r\n", $hdr), '-f' . $from);
    return $ok ? null : 'mail() не отправил письмо — настройте SMTP в config.php.';
  }
  return smtp_send($from, $to, implode("\r\n", $headers) . "\r\n\r\n" . $body);
}

function smtp_send($from, array $to, $data) {
  $host = cfg('smtp_host'); $port = (int)cfg('smtp_port', 465); $secure = cfg('smtp_secure', 'ssl');
  $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
  $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
  if (!$fp) return "Нет соединения с SMTP $host:$port ($errstr)";
  stream_set_timeout($fp, 20);
  $read = function () use ($fp) { $r = ''; while (($l = fgets($fp, 1024)) !== false) { $r .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $r; };
  $cmd = function ($c, $expect) use ($fp, $read) { if ($c !== null) fwrite($fp, $c . "\r\n"); $r = $read(); return in_array((int)substr($r, 0, 3), (array)$expect, true) ? null : trim($r); };
  $ehlo = 'EHLO ' . (parse_url(cfg('site_url'), PHP_URL_HOST) ?: 'localhost');
  if ($e = $cmd(null, 220)) return "SMTP: $e";
  if ($e = $cmd($ehlo, 250)) return "SMTP EHLO: $e";
  if ($secure === 'tls') {
    if ($e = $cmd('STARTTLS', 220)) return "SMTP STARTTLS: $e";
    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) return 'SMTP: не удалось включить шифрование';
    if ($e = $cmd($ehlo, 250)) return "SMTP EHLO: $e";
  }
  if (cfg('smtp_user')) {
    if ($e = $cmd('AUTH LOGIN', 334)) return "SMTP AUTH: $e";
    if ($e = $cmd(base64_encode(cfg('smtp_user')), 334)) return "SMTP AUTH: $e";
    if ($e = $cmd(base64_encode(cfg('smtp_pass')), 235)) return 'SMTP: неверный логин или пароль почты';
  }
  if ($e = $cmd("MAIL FROM:<$from>", 250)) return "SMTP MAIL FROM: $e";
  foreach ($to as $rcpt) if ($e = $cmd("RCPT TO:<$rcpt>", [250, 251])) return "SMTP RCPT: $e";
  if ($e = $cmd('DATA', 354)) return "SMTP DATA: $e";
  $data = preg_replace('~^\.~m', '..', str_replace(["\r\n", "\n"], ["\n", "\r\n"], $data));
  if ($e = $cmd($data . "\r\n.", 250)) return "SMTP: письмо не принято: $e";
  $cmd('QUIT', 221);
  fclose($fp);
  return null;
}
