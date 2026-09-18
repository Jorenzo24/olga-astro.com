<?php
/**
 * OLGA ASTRO — traitement du formulaire de demande.
 *
 * Lit la configuration SMTP dans .env, envoie via PHPMailer. Même structure que
 * les autres projets du studio. Particularité ici : le site est trilingue, donc
 * les messages rendus au visiteur suivent le champ caché "lang" du formulaire.
 *
 * Aucune donnée ne transite par un tiers : c'est ce que promet la politique de
 * confidentialité du site, et c'est la raison pour laquelle on n'utilise pas de
 * service externe de formulaire.
 */

declare(strict_types=1);

// ─── 1. Sécurité : POST uniquement ───
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// ─── 2. Langue du visiteur (pour les messages rendus) ───
$lang = strtolower(trim((string) ($_POST['lang'] ?? 'en')));
if (!in_array($lang, ['en', 'fr', 'ru'], true)) {
    $lang = 'en';
}

const MSG = [
    'en' => [
        'config'   => 'The site is not configured to send mail yet. Please write to info@olga-astro.com.',
        'invalid'  => 'Some fields need attention: ',
        'name'     => 'your name',
        'email'    => 'a valid email address',
        'message'  => 'a message between 10 and 5000 characters',
        'phone'    => 'a valid phone number',
        'consent'  => 'your agreement to the privacy policy',
        'rate'     => 'Please wait a few seconds before sending again.',
        'robot'    => 'The anti-robot check failed. Please try again, or write to info@olga-astro.com.',
        'sent'     => 'Thank you. Your request has been sent, and Olga will come back to you personally.',
        'failed'   => 'The message could not be sent. Please try again, or write to info@olga-astro.com.',
        'subject'  => 'New request from the site',
        'f_name' => 'Name', 'f_email' => 'Email', 'f_phone' => 'Phone',
        'f_reading' => 'Consultation', 'f_message' => 'Message', 'f_lang' => 'Page language',
    ],
    'fr' => [
        'config'   => "Le site n'est pas encore configuré pour envoyer des messages. Écrivez à info@olga-astro.com.",
        'invalid'  => 'Certains champs demandent votre attention : ',
        'name'     => 'votre nom',
        'email'    => 'une adresse e-mail valide',
        'message'  => 'un message de 10 à 5000 caractères',
        'phone'    => 'un numéro de téléphone valide',
        'consent'  => 'votre accord sur la politique de confidentialité',
        'rate'     => "Merci de patienter quelques secondes avant de renvoyer votre demande.",
        'robot'    => "La vérification anti-robot a échoué. Réessayez, ou écrivez à info@olga-astro.com.",
        'sent'     => 'Merci. Votre demande est bien partie, Olga vous répondra personnellement.',
        'failed'   => "L'envoi a échoué. Réessayez, ou écrivez directement à info@olga-astro.com.",
        'subject'  => 'Nouvelle demande depuis le site',
        'f_name' => 'Nom', 'f_email' => 'E-mail', 'f_phone' => 'Téléphone',
        'f_reading' => 'Consultation', 'f_message' => 'Message', 'f_lang' => 'Langue de la page',
    ],
    'ru' => [
        'config'   => 'Отправка сообщений с сайта пока не настроена. Напишите, пожалуйста, на info@olga-astro.com.',
        'invalid'  => 'Проверьте, пожалуйста, поля: ',
        'name'     => 'ваше имя',
        'email'    => 'корректный адрес электронной почты',
        'message'  => 'сообщение от 10 до 5000 символов',
        'phone'    => 'корректный номер телефона',
        'consent'  => 'согласие с политикой конфиденциальности',
        'rate'     => 'Пожалуйста, подождите несколько секунд перед повторной отправкой.',
        'robot'    => 'Проверка не пройдена. Попробуйте ещё раз или напишите на info@olga-astro.com.',
        'sent'     => 'Спасибо! Ваша заявка отправлена, Ольга ответит вам лично.',
        'failed'   => 'Отправить не удалось. Попробуйте ещё раз или напишите на info@olga-astro.com.',
        'subject'  => 'Новая заявка с сайта',
        'f_name' => 'Имя', 'f_email' => 'E-mail', 'f_phone' => 'Телефон',
        'f_reading' => 'Консультация', 'f_message' => 'Сообщение', 'f_lang' => 'Язык страницы',
    ],
];
$t = MSG[$lang];

function reply(int $code, array $payload): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── 3. Charger la config .env ───
function load_env(string $path): array {
    if (!file_exists($path)) return [];
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
        $env[$key] = $value;
    }
    return $env;
}
$env = load_env(__DIR__ . '/.env');

function client_ip(): string {
    $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) return $cf;
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : 'unknown';
}

/** Journalise un rejet dans logs/spam.log. Dossier bloqué par .htaccess. */
function spam_log(string $reason, string $ip, string $message, string $detail = ''): void {
    $dir = __DIR__ . '/logs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $excerpt = mb_substr(trim((string) preg_replace('/\s+/u', ' ', $message)), 0, 100);
    @file_put_contents(
        $dir . '/spam.log',
        implode("\t", [date('c'), $ip, $reason, $excerpt, $detail]) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

/** Faux succès : le bot croit avoir réussi, rien n'est envoyé. */
function fake_success(array $t): void {
    reply(200, ['ok' => true, 'message' => $t['sent']]);
}

/**
 * Filtrage de contenu. Le site étant trilingue, le test « pas un seul mot
 * courant » se fait dans la langue de la page ; en russe on vérifie aussi que
 * le message contient bien du cyrillique.
 */
function spam_reason(string $message, string $lang): ?string {
    if (preg_match_all('#https?://#i', $message, $m) && count($m[0]) >= 3) {
        return 'liens:' . count($m[0]);
    }
    foreach (['token', 'tokens', 'USD', 'crypto', 'bitcoin', 'binance', 'casino'] as $mot) {
        if (preg_match('/\b' . preg_quote($mot, '/') . '\b/i', $message)) {
            return 'mot_cle:' . $mot;
        }
    }
    $common = [
        'en' => ['the', 'and', 'you', 'for', 'hello', 'hi', 'thanks', 'please', 'my', 'would', 'want', 'chart', 'birth'],
        'fr' => ['le', 'la', 'les', 'de', 'des', 'un', 'une', 'pour', 'bonjour', 'merci', 'je', 'nous', 'vous'],
        'ru' => ['и', 'в', 'не', 'на', 'я', 'что', 'здравствуйте', 'спасибо', 'хочу', 'меня', 'вы', 'мне'],
    ];
    if (mb_strlen($message) > 25) {
        $words = $common[$lang] ?? $common['en'];
        if (!preg_match('/(' . implode('|', array_map(fn($w) => preg_quote($w, '/'), $words)) . ')/iu', $message)) {
            return 'aucun_mot_courant_' . $lang;
        }
    }
    return null;
}

$ip = client_ip();

if (empty($env['SMTP_HOST']) || empty($env['SMTP_USER']) || empty($env['SMTP_PASS']) || empty($env['SMTP_TO'])
    || $env['SMTP_PASS'] === 'replace_me') {
    error_log('[OLGA] .env incomplet ou non déposé sur le serveur');
    reply(500, ['ok' => false, 'error' => $t['config']]);
}

// ─── 4. Honeypot ───
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    spam_log('honeypot', $ip, (string) ($_POST['message'] ?? ''), (string) ($_POST['email'] ?? ''));
    fake_success($t);
}

// ─── 5. Limitation de débit, par IP ───
$rate_file = sys_get_temp_dir() . '/olga_rate_' . md5($ip) . '.txt';
$now = time();
if (file_exists($rate_file) && ($now - (int) file_get_contents($rate_file)) < 30) {
    reply(429, ['ok' => false, 'error' => $t['rate']]);
}

// ─── 6. Validation ───
$name    = trim((string) ($_POST['name'] ?? ''));
$surname = trim((string) ($_POST['surname'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$reading = trim((string) ($_POST['reading'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$consent = trim((string) ($_POST['consent'] ?? ''));

$errors = [];
if ($name === '' || mb_strlen($name) > 100)                      $errors[] = $t['name'];
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $t['email'];
if (mb_strlen($message) < 10 || mb_strlen($message) > 5000)      $errors[] = $t['message'];
if ($phone !== '' && !preg_match('/^[\d\s\+\-\.\(\)]{6,30}$/', $phone)) $errors[] = $t['phone'];
if ($consent === '')                                             $errors[] = $t['consent'];

if ($errors) {
    reply(400, ['ok' => false, 'error' => $t['invalid'] . implode(', ', $errors) . '.']);
}

// ─── 7. Turnstile, si configuré ───
$secret = trim((string) ($env['TURNSTILE_SECRET_KEY'] ?? ''));
if ($secret === '' || $secret === 'replace_me') {
    spam_log('turnstile_non_configure', $ip, $message, $email);
} else {
    $token = (string) ($_POST['cf-turnstile-response'] ?? '');
    $ok = false;
    if ($token !== '') {
        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_POSTFIELDS => http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $ip]),
        ]);
        $raw = curl_exec($ch);
        $transport = ($raw === false);
        curl_close($ch);
        if ($transport) {
            // API injoignable : on laisse passer plutôt que de couper le seul
            // canal de contact. Tracé dans le log.
            spam_log('turnstile_indisponible', $ip, $message, $email);
            $ok = true;
        } else {
            $ok = (bool) (json_decode((string) $raw, true)['success'] ?? false);
        }
    }
    if (!$ok) {
        spam_log('turnstile_echec', $ip, $message, $email);
        reply(403, ['ok' => false, 'error' => $t['robot']]);
    }
}

// ─── 8. Filtrage de contenu ───
if (($reason = spam_reason($message, $lang)) !== null) {
    spam_log('contenu:' . $reason, $ip, $message, $email);
    fake_success($t);
}

// ─── 9. Envoi ───
require __DIR__ . '/vendor/phpmailer/Exception.php';
require __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$full = trim($name . ' ' . $surname);
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $env['SMTP_HOST'];
    $mail->Port       = (int) ($env['SMTP_PORT'] ?? 587);
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['SMTP_USER'];
    $mail->Password   = $env['SMTP_PASS'];
    $secure = strtolower($env['SMTP_SECURE'] ?? 'tls');
    if ($secure === 'tls')      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    elseif ($secure === 'ssl')  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom($env['SMTP_FROM'] ?? $env['SMTP_USER'], $env['SMTP_FROM_NAME'] ?? 'Olga Astro');
    $mail->addReplyTo($email, $full !== '' ? $full : $email);
    $mail->addAddress($env['SMTP_TO'], $env['SMTP_TO_NAME'] ?? '');
    if (!empty($env['SMTP_CC'])) {
        foreach (explode(',', $env['SMTP_CC']) as $cc) {
            $cc = trim($cc);
            if ($cc !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL)) $mail->addCC($cc);
        }
    }

    $mail->Subject = '[Olga Astro] ' . $t['subject'] . ' — ' . $full;

    $rows = [
        $t['f_name']    => $full,
        $t['f_email']   => $email,
        $t['f_phone']   => $phone,
        $t['f_reading'] => $reading,
        $t['f_lang']    => strtoupper($lang),
    ];
    $html  = '<!DOCTYPE html><html><body style="font-family:Arial,Helvetica,sans-serif;color:#222;max-width:620px;margin:0 auto">';
    $html .= '<div style="background:#0a112e;color:#fff;padding:22px;text-align:center">';
    $html .= '<h2 style="margin:0;font-weight:600;letter-spacing:.02em">Olga Astro</h2>';
    $html .= '<p style="margin:6px 0 0;font-size:14px;color:#e6c97f">' . htmlspecialchars($t['subject']) . '</p></div>';
    $html .= '<div style="padding:22px;background:#f7f2e8">';
    foreach ($rows as $label => $value) {
        if ($value === '') continue;
        $html .= '<p style="margin:.35rem 0"><strong>' . htmlspecialchars($label) . ' :</strong> ' . htmlspecialchars($value) . '</p>';
    }
    $html .= '<p style="margin-top:1rem"><strong>' . htmlspecialchars($t['f_message']) . ' :</strong></p>';
    $html .= '<div style="background:#fff;padding:15px;border-left:4px solid #c9a24b;white-space:pre-wrap">' . htmlspecialchars($message) . '</div>';
    $html .= '<p style="margin-top:20px;font-size:12px;color:#888">olga-astro.com — IP : ' . htmlspecialchars($ip) . '</p>';
    $html .= '</div></body></html>';

    $text = $t['subject'] . "\n" . str_repeat('=', 40) . "\n\n";
    foreach ($rows as $label => $value) {
        if ($value !== '') $text .= $label . " : " . $value . "\n";
    }
    $text .= "\n" . $t['f_message'] . " :\n" . $message . "\n\n-\nIP : " . $ip . "\n";

    $mail->isHTML(true);
    $mail->Body    = $html;
    $mail->AltBody = $text;
    $mail->send();

    @file_put_contents($rate_file, (string) $now);
    reply(200, ['ok' => true, 'message' => $t['sent']]);

} catch (Exception $e) {
    error_log('[OLGA] SMTP: ' . $e->getMessage());
    reply(500, ['ok' => false, 'error' => $t['failed']]);
}
