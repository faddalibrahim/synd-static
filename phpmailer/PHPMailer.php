<?php

/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5.
 *
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';
    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';
    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';
    const ICAL_METHOD_REQUEST = 'REQUEST';
    const ICAL_METHOD_PUBLISH = 'PUBLISH';
    const ICAL_METHOD_REPLY = 'REPLY';
    const ICAL_METHOD_ADD = 'ADD';
    const ICAL_METHOD_CANCEL = 'CANCEL';
    const ICAL_METHOD_REFRESH = 'REFRESH';
    const ICAL_METHOD_COUNTER = 'COUNTER';
    const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';
    const RFC822_DATE_FORMAT = 'D, j M Y H:i:s O';
    const VERSION = '7.1.1';
    const STOP_MESSAGE = 0;
    const STOP_CONTINUE = 1;
    const STOP_CRITICAL = 2;
    const CRLF = "\r\n";
    const FWS = ' ';
    const MAIL_MAX_LINE_LENGTH = 63;
    const MAX_LINE_LENGTH = 998;
    const STD_LINE_LENGTH = 76;

    public $Priority;
    public $CharSet = self::CHARSET_ISO88591;
    public $ContentType = self::CONTENT_TYPE_PLAINTEXT;
    public $Encoding = self::ENCODING_8BIT;
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $Ical = '';
    public $WordWrap = 0;
    public $Mailer = 'mail';
    public $Sendmail = '/usr/sbin/sendmail';
    public $UseSendmailOptions = true;
    public $ConfirmReadingTo = '';
    public $Hostname = '';
    public $MessageID = '';
    public $MessageDate = '';
    public $Host = 'localhost';
    public $Port = 25;
    public $Helo = '';
    public $SMTPSecure = '';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = false;
    public $SMTPOptions = [];
    public $Username = '';
    public $Password = '';
    public $AuthType = '';
    public $Timeout = 300;
    public $dsn = '';
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';
    public $SMTPKeepAlive = false;
    public $SingleTo = false;
    public $do_verp = false;
    public $AllowEmpty = false;
    public $DKIM_selector = '';
    public $DKIM_identity = '';
    public $DKIM_passphrase = '';
    public $DKIM_domain = '';
    public $DKIM_copyHeaderFields = true;
    public $DKIM_extraHeaders = [];
    public $DKIM_private = '';
    public $DKIM_private_string = '';
    public $action_function = '';
    public $XMailer = '';
    public $UseSMTPUTF8 = false;
    public static $validator = 'php';

    protected $smtp;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $ReplyTo = [];
    protected $all_recipients = [];
    protected $RecipientsQueue = [];
    protected $ReplyToQueue = [];
    protected $attachment = [];
    protected $CustomHeader = [];
    protected $lastMessageID = '';
    protected $message_type = '';
    protected $boundary = [];
    protected static $language = [];
    protected $error_count = 0;
    protected $sign_cert_file = '';
    protected $sign_key_file = '';
    protected $sign_extracerts_file = '';
    protected $sign_key_pass = '';
    protected $exceptions = false;
    protected $uniqueid = '';
    protected static $LE = self::CRLF;
    protected $MIMEBody = '';
    protected $MIMEHeader = '';
    protected $mailHeader = '';
    protected static $IcalMethods = [
        self::ICAL_METHOD_REQUEST,
        self::ICAL_METHOD_PUBLISH,
        self::ICAL_METHOD_REPLY,
        self::ICAL_METHOD_ADD,
        self::ICAL_METHOD_CANCEL,
        self::ICAL_METHOD_REFRESH,
        self::ICAL_METHOD_COUNTER,
        self::ICAL_METHOD_DECLINECOUNTER,
    ];
    protected $SMTPXClient = [];
    protected $oauth;
    protected $SingleToArray = [];

    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool) $exceptions;
        }
        $this->Debugoutput = (strpos(PHP_SAPI, 'cli') !== false ? 'echo' : 'html');
    }

    public function __destruct()
    {
        $this->smtpClose();
    }

    public function isHTML($isHtml = true)
    {
        if ($isHtml) {
            $this->ContentType = static::CONTENT_TYPE_TEXT_HTML;
        } else {
            $this->ContentType = static::CONTENT_TYPE_PLAINTEXT;
        }
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function isMail()
    {
        $this->Mailer = 'mail';
    }

    public function addAddress($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('to', $address, $name);
    }

    public function addCC($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('cc', $address, $name);
    }

    public function addBCC($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('bcc', $address, $name);
    }

    public function addReplyTo($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('Reply-To', $address, $name);
    }

    protected function addOrEnqueueAnAddress($kind, $address, $name)
    {
        $pos = false;
        if ($address !== null) {
            $address = trim($address);
            $pos = strrpos($address, '@');
        }
        if (false === $pos) {
            $error_message = sprintf('%s (%s): %s', self::lang('invalid_address'), $kind, $address);
            $this->setError($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ($name !== null && is_string($name)) {
            $name = trim(preg_replace('/[\r\n]+/', '', $name));
        } else {
            $name = '';
        }
        $params = [$kind, $address, $name];
        if ($this->has8bitChars(substr($address, ++$pos))) {
            if (static::idnSupported()) {
                if ('Reply-To' !== $kind) {
                    if (!array_key_exists($address, $this->RecipientsQueue)) {
                        $this->RecipientsQueue[$address] = $params;
                        return true;
                    }
                } elseif (!array_key_exists($address, $this->ReplyToQueue)) {
                    $this->ReplyToQueue[$address] = $params;
                    return true;
                }
            }
            return false;
        }
        return call_user_func_array([$this, 'addAnAddress'], $params);
    }

    protected function addAnAddress($kind, $address, $name = '')
    {
        if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
            $error_message = sprintf('%s: %s', self::lang('Invalid recipient kind'), $kind);
            $this->setError($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if (!static::validateAddress($address)) {
            $error_message = sprintf('%s (%s): %s', self::lang('invalid_address'), $kind, $address);
            $this->setError($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        if ('Reply-To' !== $kind) {
            if (!array_key_exists(strtolower($address), $this->all_recipients)) {
                $this->{$kind}[] = [$address, $name];
                $this->all_recipients[strtolower($address)] = true;
                return true;
            }
        } else {
            foreach ($this->ReplyTo as $replyTo) {
                if (0 === strcasecmp($replyTo[0], $address)) {
                    return false;
                }
            }
            $this->ReplyTo[] = [$address, $name];
            return true;
        }
        return false;
    }

    public function setFrom($address, $name = '', $auto = true)
    {
        if (is_null($name)) {
            $name = '';
        }
        $address = trim((string)$address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name));
        $pos = strrpos($address, '@');
        if (
            (false === $pos)
            || ((!$this->has8bitChars(substr($address, ++$pos)) || !static::idnSupported())
            && !static::validateAddress($address))
        ) {
            $error_message = sprintf('%s (From): %s', self::lang('invalid_address'), $address);
            $this->setError($error_message);
            if ($this->exceptions) {
                throw new Exception($error_message);
            }
            return false;
        }
        $this->From = $address;
        $this->FromName = $name;
        if ($auto && empty($this->Sender)) {
            $this->Sender = $address;
        }
        return true;
    }

    public static function validateAddress($address, $patternselect = null)
    {
        if (null === $patternselect) {
            $patternselect = static::$validator;
        }
        if (is_callable($patternselect) && !is_string($patternselect)) {
            return call_user_func($patternselect, $address);
        }
        if (strpos($address, "\n") !== false || strpos($address, "\r") !== false) {
            return false;
        }
        switch ($patternselect) {
            case 'html5':
                return (bool) preg_match(
                    '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
                    $address
                );
            case 'php':
            default:
                return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
        }
    }

    public static function idnSupported()
    {
        return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
    }

    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $exc) {
            $this->mailHeader = '';
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }

    public function preSend()
    {
        if ('smtp' === $this->Mailer || ('mail' === $this->Mailer && (\PHP_VERSION_ID >= 80000 || stripos(PHP_OS, 'WIN') === 0))) {
            static::setLE(self::CRLF);
        } else {
            static::setLE(PHP_EOL);
        }
        try {
            $this->error_count = 0;
            $this->mailHeader = '';
            foreach (array_merge($this->RecipientsQueue, $this->ReplyToQueue) as $params) {
                call_user_func_array([$this, 'addAnAddress'], $params);
            }
            if (count($this->to) + count($this->cc) + count($this->bcc) < 1) {
                throw new Exception(self::lang('provide_address'), self::STOP_CRITICAL);
            }
            foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
                if ($this->{$address_kind} === null) {
                    $this->{$address_kind} = '';
                    continue;
                }
                $this->{$address_kind} = trim($this->{$address_kind});
                if (empty($this->{$address_kind})) {
                    continue;
                }
                if (!static::validateAddress($this->{$address_kind})) {
                    $error_message = sprintf('%s (%s): %s', self::lang('invalid_address'), $address_kind, $this->{$address_kind});
                    $this->setError($error_message);
                    if ($this->exceptions) {
                        throw new Exception($error_message);
                    }
                    return false;
                }
            }
            if ($this->alternativeExists()) {
                $this->ContentType = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
            }
            $this->setMessageType();
            if (!$this->AllowEmpty && empty($this->Body)) {
                throw new Exception(self::lang('empty_message'), self::STOP_CRITICAL);
            }
            $this->Subject = trim($this->Subject);
            $this->MIMEHeader = '';
            $this->MIMEBody = $this->createBody();
            $tempheaders = $this->MIMEHeader;
            $this->MIMEHeader = $this->createHeader();
            $this->MIMEHeader .= $tempheaders;
            if ('mail' === $this->Mailer) {
                if (count($this->to) > 0) {
                    $this->mailHeader .= $this->addrAppend('To', $this->to);
                } else {
                    $this->mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
                }
                $this->mailHeader .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
            }
            return true;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }

    public function postSend()
    {
        try {
            switch ($this->Mailer) {
                case 'sendmail':
                case 'qmail':
                    return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
                case 'smtp':
                    return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
                case 'mail':
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
                default:
                    return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
            }
        } catch (Exception $exc) {
            $this->setError($exc->getMessage());
            if ($this->Mailer === 'smtp' && $this->SMTPKeepAlive && null !== $this->smtp && $this->smtp->connected()) {
                $this->smtp->reset();
            }
            if ($this->exceptions) {
                throw $exc;
            }
        }
        return false;
    }

    protected function sendmailSend($header, $body)
    {
        $header = static::stripTrailingWSP($header) . static::$LE . static::$LE;
        $sendmailArgs = [];
        if (!empty($this->Sender) && static::validateAddress($this->Sender) && self::isShellSafe($this->Sender)) {
            $sendmailArgs[] = '-f' . $this->Sender;
        }
        if ($this->Mailer !== 'qmail') {
            $sendmailArgs[] = '-i';
            $sendmailArgs[] = '-t';
        }
        $resultArgs = (empty($sendmailArgs) ? '' : ' ' . implode(' ', $sendmailArgs));
        $sendmail = trim(escapeshellcmd($this->Sendmail) . $resultArgs);
        $mail = @popen($sendmail, 'w');
        if (!$mail) {
            throw new Exception(self::lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
        }
        fwrite($mail, $header);
        fwrite($mail, $body);
        $result = pclose($mail);
        $this->doCallback(($result === 0), $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
        if (0 !== $result) {
            throw new Exception(self::lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
        }
        return true;
    }

    protected static function isShellSafe($string)
    {
        if (!function_exists('escapeshellarg') || !function_exists('escapeshellcmd')) {
            return false;
        }
        if (escapeshellcmd($string) !== $string || !in_array(escapeshellarg($string), ["'$string'", "\"$string\""])) {
            return false;
        }
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];
            if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
                return false;
            }
        }
        return true;
    }

    protected static function isPermittedPath($path)
    {
        return !preg_match('#^[a-z][a-z\d+.-]*://#i', $path);
    }

    protected static function fileIsAccessible($path)
    {
        if (!static::isPermittedPath($path)) {
            return false;
        }
        $readable = is_file($path);
        if (strpos($path, '\\\\') !== 0) {
            $readable = $readable && is_readable($path);
        }
        return $readable;
    }

    protected function mailSend($header, $body)
    {
        $header = static::stripTrailingWSP($header) . static::$LE . static::$LE;
        $toArr = [];
        foreach ($this->to as $toaddr) {
            $toArr[] = $this->addrFormat($toaddr);
        }
        $to = trim(implode(', ', $toArr));
        if ($to === '') {
            $to = 'undisclosed-recipients:;';
        }
        $params = null;
        $sendmail_from_value = ini_get('sendmail_from');
        if (empty($this->Sender) && !empty($sendmail_from_value)) {
            $this->Sender = ini_get('sendmail_from');
        }
        if (!empty($this->Sender) && static::validateAddress($this->Sender)) {
            $phpmailer_path = ini_get('sendmail_path');
            if (self::isShellSafe($this->Sender) && strpos($phpmailer_path, ' -f') === false) {
                $params = sprintf('-f%s', $this->Sender);
            }
            $old_from = ini_get('sendmail_from');
            ini_set('sendmail_from', $this->Sender);
        }
        $result = $this->mailPassthru($to, $this->Subject, $body, $header, $params);
        $this->doCallback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
        if (isset($old_from)) {
            ini_set('sendmail_from', $old_from);
        }
        if (!$result) {
            throw new Exception(self::lang('instantiate'), self::STOP_CRITICAL);
        }
        return true;
    }

    private function mailPassthru($to, $subject, $body, $header, $params)
    {
        if ((int)ini_get('mbstring.func_overload') & 1) {
            $subject = $this->secureHeader($subject);
        } else {
            $subject = $this->encodeHeader($this->secureHeader($subject));
        }
        if (!$this->UseSendmailOptions || null === $params) {
            $result = @mail($to, $subject, $body, $header);
        } else {
            $result = @mail($to, $subject, $body, $header, $params);
        }
        return $result;
    }

    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new SMTP();
        }
        return $this->smtp;
    }

    protected function smtpSend($header, $body)
    {
        $header = static::stripTrailingWSP($header) . static::$LE . static::$LE;
        $bad_rcpt = [];
        if (!$this->smtpConnect($this->SMTPOptions)) {
            throw new Exception(self::lang('smtp_connect_failed'), self::STOP_CRITICAL);
        }
        if ($this->UseSMTPUTF8 && !$this->smtp->getServerExt('SMTPUTF8')) {
            throw new Exception(self::lang('no_smtputf8'), self::STOP_CRITICAL);
        }
        $smtp_from = '' === $this->Sender ? $this->From : $this->Sender;
        if (count($this->SMTPXClient)) {
            $this->smtp->xclient($this->SMTPXClient);
        }
        if (!$this->smtp->mail($smtp_from)) {
            $this->setError(self::lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
            throw new Exception($this->ErrorInfo, self::STOP_CRITICAL);
        }
        $callbacks = [];
        foreach ([$this->to, $this->cc, $this->bcc] as $togroup) {
            foreach ($togroup as $to) {
                if (!$this->smtp->recipient($to[0], $this->dsn)) {
                    $error = $this->smtp->getError();
                    $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
                    $isSent = false;
                } else {
                    $isSent = true;
                }
                $callbacks[] = ['issent' => $isSent, 'to' => $to[0], 'name' => $to[1]];
            }
        }
        if ((count($this->all_recipients) > count($bad_rcpt)) && !$this->smtp->data($header . $body)) {
            throw new Exception(self::lang('data_not_accepted'), self::STOP_CRITICAL);
        }
        $smtp_transaction_id = $this->smtp->getLastTransactionID();
        if ($this->SMTPKeepAlive) {
            $this->smtp->reset();
        } else {
            $this->smtp->quit();
            $this->smtp->close();
        }
        foreach ($callbacks as $cb) {
            $this->doCallback($cb['issent'], [[$cb['to'], $cb['name']]], [], [], $this->Subject, $body, $this->From, ['smtp_transaction_id' => $smtp_transaction_id]);
        }
        if (count($bad_rcpt) > 0) {
            $errstr = '';
            foreach ($bad_rcpt as $bad) {
                $errstr .= $bad['to'] . ': ' . $bad['error'];
            }
            throw new Exception(self::lang('recipients_failed') . $errstr, self::STOP_CONTINUE);
        }
        return true;
    }

    public function smtpConnect($options = null)
    {
        if (null === $this->smtp) {
            $this->smtp = $this->getSMTPInstance();
        }
        if (null === $options) {
            $options = $this->SMTPOptions;
        }
        if ($this->smtp->connected()) {
            return true;
        }
        $this->smtp->setTimeout($this->Timeout);
        $this->smtp->setDebugLevel($this->SMTPDebug);
        $this->smtp->setDebugOutput($this->Debugoutput);
        $this->smtp->setVerp($this->do_verp);
        $this->smtp->setSMTPUTF8($this->UseSMTPUTF8);
        if ($this->Host === null) {
            $this->Host = 'localhost';
        }
        $hosts = explode(';', $this->Host);
        $lastexception = null;
        foreach ($hosts as $hostentry) {
            $hostinfo = [];
            if (!preg_match('/^(?:(ssl|tls):\/\/)?(.+?)(?::(\d+))?$/', trim($hostentry), $hostinfo)) {
                continue;
            }
            if (!static::isValidHost($hostinfo[2])) {
                continue;
            }
            $prefix = '';
            $secure = $this->SMTPSecure;
            $tls = (static::ENCRYPTION_STARTTLS === $this->SMTPSecure);
            if ('ssl' === $hostinfo[1] || ('' === $hostinfo[1] && static::ENCRYPTION_SMTPS === $this->SMTPSecure)) {
                $prefix = 'ssl://';
                $tls = false;
                $secure = static::ENCRYPTION_SMTPS;
            } elseif ('tls' === $hostinfo[1]) {
                $tls = true;
                $secure = static::ENCRYPTION_STARTTLS;
            }
            $sslext = defined('OPENSSL_ALGO_SHA256');
            if (static::ENCRYPTION_STARTTLS === $secure || static::ENCRYPTION_SMTPS === $secure) {
                if (!$sslext) {
                    throw new Exception(self::lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
                }
            }
            $host = $hostinfo[2];
            $port = $this->Port;
            if (array_key_exists(3, $hostinfo) && is_numeric($hostinfo[3]) && $hostinfo[3] > 0 && $hostinfo[3] < 65536) {
                $port = (int) $hostinfo[3];
            }
            if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                try {
                    $hello = $this->Helo ?: $this->serverHostname();
                    $this->smtp->hello($hello);
                    if ($this->SMTPAutoTLS && $this->Host !== 'localhost' && $sslext && $secure !== 'ssl' && $this->smtp->getServerExt('STARTTLS')) {
                        $tls = true;
                    }
                    if ($tls) {
                        if (!$this->smtp->startTLS()) {
                            throw new Exception($this->getSmtpErrorMessage('connect_host'));
                        }
                        $this->smtp->hello($hello);
                    }
                    if ($this->SMTPAuth && !$this->smtp->authenticate($this->Username, $this->Password, $this->AuthType, $this->oauth)) {
                        throw new Exception(self::lang('authenticate'));
                    }
                    return true;
                } catch (Exception $exc) {
                    $lastexception = $exc;
                    $this->smtp->quit();
                }
            }
        }
        $this->smtp->close();
        if ($this->exceptions && null !== $lastexception) {
            throw $lastexception;
        }
        if ($this->exceptions) {
            throw new Exception($this->getSmtpErrorMessage('connect_host'));
        }
        return false;
    }

    public function smtpClose()
    {
        if ((null !== $this->smtp) && $this->smtp->connected()) {
            $this->smtp->quit();
            $this->smtp->close();
        }
    }

    public static function setLanguage($langcode = 'en', $lang_path = '')
    {
        $PHPMAILER_LANG = [
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'extension_missing' => 'Extension missing: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address: ',
            'invalid_header' => 'Invalid header name or value',
            'invalid_hostentry' => 'Invalid hostentry: ',
            'invalid_host' => 'Invalid host: ',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_code' => 'SMTP code: ',
            'smtp_code_ex' => 'Additional SMTP info: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_detail' => 'Detail: ',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: ',
            'no_smtputf8' => 'Server does not support SMTPUTF8',
            'imap_recommended' => 'Install the PHP IMAP extension for full RFC822 parsing.',
            'deprecated_argument' => 'Deprecated Argument: ',
            'Invalid recipient kind' => 'Invalid recipient kind',
            'buggy_php' => 'Your version of PHP is affected by a bug.',
        ];
        self::$language = $PHPMAILER_LANG;
        return true;
    }

    public function getTranslations()
    {
        if (empty(self::$language)) {
            self::setLanguage();
        }
        return self::$language;
    }

    public function addrAppend($type, $addr)
    {
        $addresses = [];
        foreach ($addr as $address) {
            $addresses[] = $this->addrFormat($address);
        }
        return $type . ': ' . implode(', ', $addresses) . static::$LE;
    }

    public function addrFormat($addr)
    {
        if (!isset($addr[1]) || ($addr[1] === '')) {
            return $this->secureHeader($addr[0]);
        }
        return $this->encodeHeader($this->secureHeader($addr[1]), 'phrase') . ' <' . $this->secureHeader($addr[0]) . '>';
    }

    public function setBoundaries()
    {
        $this->uniqueid = $this->generateId();
        $this->boundary[1] = 'b1=_' . $this->uniqueid;
        $this->boundary[2] = 'b2=_' . $this->uniqueid;
        $this->boundary[3] = 'b3=_' . $this->uniqueid;
    }

    protected function generateId()
    {
        $len = 32;
        $bytes = '';
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes($len);
            } catch (\Exception $e) {}
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($len);
        }
        if ($bytes === '') {
            $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
        }
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }

    public function createBody()
    {
        $body = '';
        $this->setBoundaries();
        $this->setWordWrap();
        $bodyEncoding = $this->Encoding;
        $bodyCharSet = $this->CharSet;
        if (static::ENCODING_8BIT === $bodyEncoding && !$this->has8bitChars($this->Body)) {
            $bodyEncoding = static::ENCODING_7BIT;
            $bodyCharSet = static::CHARSET_ASCII;
        }
        if (static::ENCODING_BASE64 !== $this->Encoding && static::hasLineLongerThanMax($this->Body)) {
            $bodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        $altBodyEncoding = $this->Encoding;
        $altBodyCharSet = $this->CharSet;
        if (static::ENCODING_8BIT === $altBodyEncoding && !$this->has8bitChars($this->AltBody)) {
            $altBodyEncoding = static::ENCODING_7BIT;
            $altBodyCharSet = static::CHARSET_ASCII;
        }
        if (static::ENCODING_BASE64 !== $altBodyEncoding && static::hasLineLongerThanMax($this->AltBody)) {
            $altBodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
        }
        switch ($this->message_type) {
            case 'alt':
                $body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, static::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding);
                $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
                $body .= static::$LE;
                $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, static::CONTENT_TYPE_TEXT_HTML, $bodyEncoding);
                $body .= $this->encodeString($this->Body, $bodyEncoding);
                $body .= static::$LE;
                $body .= $this->endBoundary($this->boundary[1]);
                break;
            default:
                $this->Encoding = $bodyEncoding;
                $body .= $this->encodeString($this->Body, $this->Encoding);
                break;
        }
        if ($this->isError()) {
            $body = '';
            if ($this->exceptions) {
                throw new Exception(self::lang('empty_message'), self::STOP_CRITICAL);
            }
        }
        return $body;
    }

    protected function getBoundary($boundary, $charSet, $contentType, $encoding)
    {
        $result = '';
        if ('' === $charSet) $charSet = $this->CharSet;
        if ('' === $contentType) $contentType = $this->ContentType;
        if ('' === $encoding) $encoding = $this->Encoding;
        $result .= $this->textLine('--' . $boundary);
        $result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
        $result .= static::$LE;
        if (static::ENCODING_7BIT !== $encoding) {
            $result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
        }
        $result .= static::$LE;
        return $result;
    }

    protected function endBoundary($boundary)
    {
        return static::$LE . '--' . $boundary . '--' . static::$LE;
    }

    protected function setMessageType()
    {
        $type = [];
        if ($this->alternativeExists()) $type[] = 'alt';
        if ($this->inlineImageExists()) $type[] = 'inline';
        if ($this->attachmentExists()) $type[] = 'attach';
        $this->message_type = implode('_', $type);
        if ('' === $this->message_type) $this->message_type = 'plain';
    }

    public function headerLine($name, $value)
    {
        return $name . ': ' . $value . static::$LE;
    }

    public function textLine($value)
    {
        return $value . static::$LE;
    }

    public function createHeader()
    {
        $result = '';
        $result .= $this->headerLine('Date', self::rfcDate());
        if ('mail' !== $this->Mailer) {
            if (count($this->to) > 0) {
                $result .= $this->addrAppend('To', $this->to);
            } elseif (count($this->cc) === 0) {
                $result .= $this->headerLine('To', 'undisclosed-recipients:;');
            }
        }
        $result .= $this->addrAppend('From', [[trim($this->From), $this->FromName]]);
        if (count($this->cc) > 0) {
            $result .= $this->addrAppend('Cc', $this->cc);
        }
        if (('sendmail' === $this->Mailer || 'qmail' === $this->Mailer || 'mail' === $this->Mailer) && count($this->bcc) > 0) {
            $result .= $this->addrAppend('Bcc', $this->bcc);
        }
        if (count($this->ReplyTo) > 0) {
            $result .= $this->addrAppend('Reply-To', $this->ReplyTo);
        }
        if ('mail' !== $this->Mailer) {
            $result .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
        }
        $this->lastMessageID = sprintf('<%s@%s>', $this->uniqueid, $this->serverHostname());
        $result .= $this->headerLine('Message-ID', $this->lastMessageID);
        if ('' === $this->XMailer) {
            $result .= $this->headerLine('X-Mailer', 'PHPMailer ' . self::VERSION . ' (https://github.com/PHPMailer/PHPMailer)');
        }
        $foreach_custom = $this->CustomHeader;
        foreach ($foreach_custom as $header) {
            $result .= $this->headerLine(trim($header[0]), $this->encodeHeader(trim($header[1])));
        }
        $result .= $this->headerLine('MIME-Version', '1.0');
        $result .= $this->getMailMIME();
        return $result;
    }

    public function getMailMIME()
    {
        $result = '';
        $ismultipart = true;
        switch ($this->message_type) {
            case 'alt':
            case 'alt_inline':
                $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                $result .= $this->textLine(' boundary="' . $this->boundary[1] . '"');
                break;
            default:
                $result .= $this->textLine('Content-Type: ' . $this->secureHeader($this->ContentType) . '; charset=' . $this->secureHeader($this->CharSet));
                $ismultipart = false;
                break;
        }
        if (static::ENCODING_7BIT !== $this->Encoding) {
            if (!$ismultipart) {
                $result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
            }
        }
        return $result;
    }

    public static function rfcDate()
    {
        date_default_timezone_set(@date_default_timezone_get());
        return date(self::RFC822_DATE_FORMAT);
    }

    protected function serverHostname()
    {
        $result = '';
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } elseif (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
            $result = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result = gethostname();
        } elseif (php_uname('n') !== '') {
            $result = php_uname('n');
        }
        if (!static::isValidHost($result)) {
            return 'localhost.localdomain';
        }
        return $result;
    }

    public static function isValidHost($host)
    {
        if (empty($host) || !is_string($host) || strlen($host) > 256 || !preg_match('/^([a-z\d.-]*|\[[a-f\d:]+\])$/i', $host)) {
            return false;
        }
        if (strlen($host) > 2 && substr($host, 0, 1) === '[' && substr($host, -1, 1) === ']') {
            return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }
        if (is_numeric(str_replace('.', '', $host))) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }
        return filter_var('https://' . $host, FILTER_VALIDATE_URL) !== false;
    }

    public function encodeString($str, $encoding = self::ENCODING_BASE64)
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case static::ENCODING_BASE64:
                $encoded = chunk_split(base64_encode($str), static::STD_LINE_LENGTH, static::$LE);
                break;
            case static::ENCODING_7BIT:
            case static::ENCODING_8BIT:
                $encoded = static::normalizeBreaks($str);
                if (substr($encoded, -(strlen(static::$LE))) !== static::$LE) {
                    $encoded .= static::$LE;
                }
                break;
            case static::ENCODING_BINARY:
                $encoded = $str;
                break;
            case static::ENCODING_QUOTED_PRINTABLE:
                $encoded = $this->encodeQP($str);
                break;
            default:
                $this->setError(self::lang('encoding') . $encoding);
                if ($this->exceptions) {
                    throw new Exception(self::lang('encoding') . $encoding);
                }
                break;
        }
        return $encoded;
    }

    public function encodeHeader($str, $position = 'text')
    {
        $position = strtolower($position);
        $matchcount = 0;
        switch ($position) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return $encoded;
                    }
                    return "\"$encoded\"";
                }
                $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                break;
            case 'comment':
                $matchcount = preg_match_all('/[()"]/', $str, $matches);
            case 'text':
            default:
                $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
        }
        if ($this->has8bitChars($str)) {
            $charset = $this->CharSet;
        } else {
            $charset = static::CHARSET_ASCII;
        }
        $overhead = 8 + strlen($charset);
        if ('mail' === $this->Mailer) {
            $maxlen = static::MAIL_MAX_LINE_LENGTH - $overhead;
        } else {
            $maxlen = static::MAX_LINE_LENGTH - $overhead;
        }
        if ($matchcount > strlen($str) / 3) {
            $encoding = 'B';
        } elseif ($matchcount > 0) {
            $encoding = 'Q';
        } elseif (strlen($str) > $maxlen) {
            $encoding = 'Q';
        } else {
            $encoding = false;
        }
        switch ($encoding) {
            case 'B':
                $encoded = base64_encode($str);
                $maxlen -= $maxlen % 4;
                $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?$encoding?\\1?=", $encoded);
                break;
            case 'Q':
                $encoded = $this->encodeQ($str, $position);
                $encoded = $this->wrapText($encoded, $maxlen, true);
                $encoded = str_replace('=' . static::$LE, "\n", trim($encoded));
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?$encoding?\\1?=", $encoded);
                break;
            default:
                return $str;
        }
        return trim(static::normalizeBreaks($encoded));
    }

    public function encodeQP($string)
    {
        return static::normalizeBreaks(quoted_printable_encode($string));
    }

    public function encodeQ($str, $position = 'text')
    {
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            case 'comment':
                $pattern = '\(\)"';
            case 'text':
            default:
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                break;
        }
        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            $eqkey = array_search('=', $matches[0], true);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        return str_replace(' ', '_', $encoded);
    }

    public function wrapText($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', static::$LE);
        } else {
            $soft_break = static::$LE;
        }
        $is_utf8 = static::CHARSET_UTF8 === strtolower($this->CharSet);
        $lelen = strlen(static::$LE);
        $message = static::normalizeBreaks($message);
        if (substr($message, -$lelen) === static::$LE) {
            $message = substr($message, 0, -$lelen);
        }
        $lines = explode(static::$LE, $message);
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstword = true;
            foreach ($words as $word) {
                if ($qp_mode && (strlen($word) > $length)) {
                    $space_left = $length - strlen($buf) - strlen(static::$LE);
                    if (!$firstword) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', static::$LE);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while ($word !== '') {
                        if ($length <= 0) break;
                        $len = $length;
                        $part = substr($word, 0, $len);
                        $word = (string) substr($word, $len);
                        if ($word !== '') {
                            $message .= $part . sprintf('=%s', static::$LE);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstword) $buf .= ' ';
                    $buf .= $word;
                    if ('' !== $buf_o && strlen($buf) > $length) {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstword = false;
            }
            $message .= $buf . static::$LE;
        }
        return $message;
    }

    public function setWordWrap()
    {
        if ($this->WordWrap < 1) return;
        switch ($this->message_type) {
            case 'alt':
            case 'alt_inline':
            case 'alt_attach':
            case 'alt_inline_attach':
                $this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
                break;
            default:
                $this->Body = $this->wrapText($this->Body, $this->WordWrap);
                break;
        }
    }

    protected function inlineImageExists()
    {
        foreach ($this->attachment as $attachment) {
            if ('inline' === $attachment[6]) return true;
        }
        return false;
    }

    public function attachmentExists()
    {
        foreach ($this->attachment as $attachment) {
            if ('attachment' === $attachment[6]) return true;
        }
        return false;
    }

    public function alternativeExists()
    {
        return !empty($this->AltBody);
    }

    protected function setError($msg)
    {
        ++$this->error_count;
        $this->ErrorInfo = $msg;
    }

    public function isError()
    {
        return $this->error_count > 0;
    }

    public function secureHeader($str)
    {
        return trim(str_replace(["\r", "\n"], '', $str));
    }

    public static function normalizeBreaks($text, $breaktype = null)
    {
        if (null === $breaktype) $breaktype = static::$LE;
        $text = str_replace([self::CRLF, "\r"], "\n", $text);
        if ("\n" !== $breaktype) $text = str_replace("\n", $breaktype, $text);
        return $text;
    }

    public static function stripTrailingWSP($text)
    {
        return rtrim($text, " \r\n\t");
    }

    public static function hasLineLongerThanMax($str)
    {
        return (bool) preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(static::$LE)) . ',})/m', $str);
    }

    public function has8bitChars($text)
    {
        return (bool) preg_match('/[\x80-\xFF]/', $text);
    }

    public function hasMultiBytes($str)
    {
        if (function_exists('mb_strlen')) {
            return strlen($str) > mb_strlen($str, $this->CharSet);
        }
        return false;
    }

    public static function getLE()
    {
        return static::$LE;
    }

    protected static function setLE($le)
    {
        static::$LE = $le;
    }

    protected static function lang($key)
    {
        if (count(self::$language) < 1) {
            self::setLanguage();
        }
        if (array_key_exists($key, self::$language)) {
            return self::$language[$key];
        }
        return $key;
    }

    private function getSmtpErrorMessage($base_key)
    {
        $message = self::lang($base_key);
        if (null !== $this->smtp) {
            $error = $this->smtp->getError();
            if (!empty($error['error'])) {
                $message .= ' ' . $error['error'];
                if (!empty($error['detail'])) {
                    $message .= ' ' . $error['detail'];
                }
            }
        }
        return $message;
    }

    public function addCustomHeader($name, $value = null)
    {
        if (null === $value && strpos($name, ':') !== false) {
            list($name, $value) = explode(':', $name, 2);
        }
        $name = trim($name);
        $value = (null === $value) ? '' : trim($value);
        if (empty($name) || strpbrk($name . $value, "\r\n") !== false) {
            if ($this->exceptions) {
                throw new Exception(self::lang('invalid_header'));
            }
            return false;
        }
        $this->CustomHeader[] = [$name, $value];
        return true;
    }

    public function getCustomHeaders()
    {
        return $this->CustomHeader;
    }

    public function clearAddresses()
    {
        foreach ($this->to as $to) unset($this->all_recipients[strtolower($to[0])]);
        $this->to = [];
        $this->RecipientsQueue = array_filter($this->RecipientsQueue, function ($params) { return $params[0] !== 'to'; });
    }

    public function clearAllRecipients()
    {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->all_recipients = [];
        $this->RecipientsQueue = [];
    }

    public function clearAttachments()
    {
        $this->attachment = [];
    }

    public function clearCustomHeaders()
    {
        $this->CustomHeader = [];
    }

    protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra)
    {
        if (!empty($this->action_function) && is_callable($this->action_function)) {
            call_user_func($this->action_function, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
        }
    }

    public function getLastMessageID()
    {
        return $this->lastMessageID;
    }

    protected function addressHasUnicodeLocalPart($address)
    {
        return (bool) preg_match('/[\x80-\xFF].*@/', $address);
    }

    public function punyencodeAddress($address)
    {
        $pos = strrpos($address, '@');
        if (!empty($this->CharSet) && false !== $pos && static::idnSupported()) {
            $domain = substr($address, ++$pos);
            if ($this->has8bitChars($domain) && @mb_check_encoding($domain, $this->CharSet)) {
                $domain = mb_convert_encoding($domain, self::CHARSET_UTF8, $this->CharSet);
                $errorcode = 0;
                if (defined('INTL_IDNA_VARIANT_UTS46')) {
                    $punycode = idn_to_ascii($domain, \IDNA_DEFAULT | \IDNA_USE_STD3_RULES | \IDNA_CHECK_BIDI | \IDNA_CHECK_CONTEXTJ | \IDNA_NONTRANSITIONAL_TO_ASCII, \INTL_IDNA_VARIANT_UTS46);
                } else {
                    $punycode = idn_to_ascii($domain, $errorcode);
                }
                if (false !== $punycode) {
                    return substr($address, 0, $pos) . $punycode;
                }
            }
        }
        return $address;
    }

    public static function filenameToType($filename)
    {
        $qpos = strpos($filename, '?');
        if (false !== $qpos) $filename = substr($filename, 0, $qpos);
        $ext = static::mb_pathinfo($filename, PATHINFO_EXTENSION);
        return static::_mime_types($ext);
    }

    public static function _mime_types($ext = '')
    {
        $mimes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'pdf' => 'application/pdf', 'doc' => 'application/msword',
            'txt' => 'text/plain', 'html' => 'text/html', 'htm' => 'text/html',
            'css' => 'text/css', 'js' => 'application/javascript', 'zip' => 'application/zip',
        ];
        $ext = strtolower($ext);
        if (array_key_exists($ext, $mimes)) return $mimes[$ext];
        return 'application/octet-stream';
    }

    public static function mb_pathinfo($path, $options = null)
    {
        $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
        $pathinfo = [];
        if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathinfo)) {
            if (array_key_exists(1, $pathinfo)) $ret['dirname'] = $pathinfo[1];
            if (array_key_exists(2, $pathinfo)) $ret['basename'] = $pathinfo[2];
            if (array_key_exists(5, $pathinfo)) $ret['extension'] = $pathinfo[5];
            if (array_key_exists(3, $pathinfo)) $ret['filename'] = $pathinfo[3];
        }
        switch ($options) {
            case PATHINFO_DIRNAME: case 'dirname': return $ret['dirname'];
            case PATHINFO_BASENAME: case 'basename': return $ret['basename'];
            case PATHINFO_EXTENSION: case 'extension': return $ret['extension'];
            case PATHINFO_FILENAME: case 'filename': return $ret['filename'];
            default: return $ret;
        }
    }

    protected function validateEncoding($encoding)
    {
        return in_array(strtolower($encoding), [
            self::ENCODING_7BIT, self::ENCODING_QUOTED_PRINTABLE,
            self::ENCODING_BASE64, self::ENCODING_8BIT, self::ENCODING_BINARY,
        ], true);
    }

    public static function quotedString($str)
    {
        if (preg_match('/[ ()<>@,;:"\/\[\]?=]/', $str)) {
            return '"' . str_replace('"', '\\"', $str) . '"';
        }
        return $str;
    }

    public function sign($cert_filename, $key_filename, $key_pass, $extracerts_filename = '')
    {
        $this->sign_cert_file = $cert_filename;
        $this->sign_key_file = $key_filename;
        $this->sign_key_pass = $key_pass;
        $this->sign_extracerts_file = $extracerts_filename;
    }

    public function setSMTPInstance(SMTP $smtp)
    {
        $this->smtp = $smtp;
        return $this->smtp;
    }

    public function setSMTPXclientAttribute($name, $value)
    {
        if (!in_array($name, SMTP::$xclient_allowed_attributes)) return false;
        if (isset($this->SMTPXClient[$name]) && $value === null) {
            unset($this->SMTPXClient[$name]);
        } elseif ($value !== null) {
            $this->SMTPXClient[$name] = $value;
        }
        return true;
    }

    public function getSMTPXclientAttributes()
    {
        return $this->SMTPXClient;
    }

    public function getOAuth()
    {
        return $this->oauth;
    }

    public function setOAuth($oauth)
    {
        $this->oauth = $oauth;
    }
}
