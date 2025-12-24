<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\email;

use think\facade\Config;

class EmailService
{
    /**
     * @var array
     */
    protected $config = [
        'host' => '',
        'port' => 25,
        'username' => '',
        'password' => '',
        'encryption' => '', // ssl | tls | ''
        'timeout' => 10,
        'from' => '',
        'from_name' => '',
        'charset' => 'UTF-8',
    ];

    /**
     * EmailService constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $base = Config::get('email', []);
        if (!is_array($base)) {
            $base = [];
        }
        $this->config = array_merge($this->config, $base, $config);
    }

    /**
     * 设置配置
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * 发送邮件
     * @param string|array $to
     * @param string $subject
     * @param string $body
     * @param array $options
     * @return bool
     */
    public function send($to, string $subject, string $body, array $options = [])
    {
        $recipients = $this->normalizeRecipients($to);
        if (!$recipients) {
            throw new \InvalidArgumentException('Email recipients cannot be empty.');
        }

        $cc = $this->normalizeRecipients($options['cc'] ?? []);
        $bcc = $this->normalizeRecipients($options['bcc'] ?? []);
        $replyTo = $this->normalizeRecipients($options['reply_to'] ?? []);

        $from = $options['from'] ?? $this->config['from'];
        $fromName = $options['from_name'] ?? $this->config['from_name'];
        if (!$from) {
            throw new \InvalidArgumentException('Email from address cannot be empty.');
        }

        $isHtml = (bool)($options['is_html'] ?? true);
        $charset = $this->config['charset'] ?: 'UTF-8';

        $headers = [
            'From' => $this->formatAddress($from, $fromName),
            'To' => $this->formatAddressList($recipients),
            'Subject' => $this->encodeHeader($subject, $charset),
            'Date' => gmdate('D, d M Y H:i:s O'),
            'Message-ID' => $this->generateMessageId(),
            'MIME-Version' => '1.0',
        ];

        if ($cc) {
            $headers['Cc'] = $this->formatAddressList($cc);
        }
        if ($replyTo) {
            $headers['Reply-To'] = $this->formatAddressList($replyTo);
        }

        $headers['Content-Type'] = $isHtml
            ? 'text/html; charset=' . $charset
            : 'text/plain; charset=' . $charset;
        $headers['Content-Transfer-Encoding'] = 'base64';

        $bodyEncoded = rtrim(chunk_split(base64_encode($body), 76, "\r\n"));

        $message = $this->buildHeaders($headers) . "\r\n\r\n" . $bodyEncoded;

        $envelopeRecipients = array_values(array_unique(array_merge($recipients, $cc, $bcc)));

        $this->sendSmtp($from, $envelopeRecipients, $message);

        return true;
    }

    /**
     * SMTP发送
     * @param string $from
     * @param array $to
     * @param string $data
     */
    protected function sendSmtp(string $from, array $to, string $data)
    {
        $host = $this->config['host'];
        $port = (int)$this->config['port'];
        $timeout = (int)$this->config['timeout'];
        $encryption = strtolower((string)$this->config['encryption']);

        if (!$host) {
            throw new \InvalidArgumentException('SMTP host cannot be empty.');
        }
        if ($port <= 0) {
            $port = $encryption === 'ssl' ? 465 : 25;
        }

        $remote = $host;
        if ($encryption === 'ssl' && strpos($host, 'ssl://') !== 0) {
            $remote = 'ssl://' . $host;
        }

        $socket = @stream_socket_client(
            $remote . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            throw new \RuntimeException('SMTP connect failed: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($socket, $timeout);

        $this->expectCode($socket, [220]);

        $hostname = $this->getHostname();
        if (!$this->sendCommand($socket, 'EHLO ' . $hostname, [250])) {
            $this->sendCommand($socket, 'HELO ' . $hostname, [250]);
        }

        if ($encryption === 'tls') {
            $this->sendCommand($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('SMTP STARTTLS failed.');
            }
            $this->sendCommand($socket, 'EHLO ' . $hostname, [250]);
        }

        $username = (string)$this->config['username'];
        if ($username !== '') {
            $this->sendCommand($socket, 'AUTH LOGIN', [334]);
            $this->sendCommand($socket, base64_encode($username), [334]);
            $this->sendCommand($socket, base64_encode((string)$this->config['password']), [235]);
        }

        $this->sendCommand($socket, 'MAIL FROM:<' . $from . '>', [250]);
        foreach ($to as $address) {
            $this->sendCommand($socket, 'RCPT TO:<' . $address . '>', [250, 251]);
        }

        $this->sendCommand($socket, 'DATA', [354]);
        $this->writeData($socket, $data);
        $this->expectCode($socket, [250]);

        $this->sendCommand($socket, 'QUIT', [221, 250]);

        fclose($socket);
    }

    /**
     * 发送命令并校验返回码
     * @param resource $socket
     * @param string $command
     * @param array $okCodes
     * @return bool
     */
    protected function sendCommand($socket, string $command, array $okCodes)
    {
        $this->writeLine($socket, $command);
        return $this->expectCode($socket, $okCodes);
    }

    /**
     * 写入邮件数据
     * @param resource $socket
     * @param string $data
     */
    protected function writeData($socket, string $data)
    {
        $data = str_replace(["\r\n.", "\n."], ["\r\n..", "\n.."], $data);
        $this->writeLine($socket, $data . "\r\n.");
    }

    /**
     * 写入命令
     * @param resource $socket
     * @param string $line
     */
    protected function writeLine($socket, string $line)
    {
        fwrite($socket, $line . "\r\n");
    }

    /**
     * 读取返回并校验
     * @param resource $socket
     * @param array $okCodes
     * @return bool
     */
    protected function expectCode($socket, array $okCodes)
    {
        $response = $this->readResponse($socket);
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $okCodes, true)) {
            throw new \RuntimeException('SMTP error: ' . trim($response));
        }
        return true;
    }

    /**
     * 读取SMTP响应
     * @param resource $socket
     * @return string
     */
    protected function readResponse($socket): string
    {
        $data = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    }

    /**
     * 规范化收件人
     * @param string|array $to
     * @return array
     */
    protected function normalizeRecipients($to): array
    {
        if (is_string($to)) {
            $to = array_filter(array_map('trim', explode(',', $to)));
        }
        if (!is_array($to)) {
            return [];
        }
        $result = [];
        foreach ($to as $item) {
            if (is_string($item)) {
                $item = trim($item);
                if ($item !== '') {
                    $result[] = $item;
                }
                continue;
            }
            if (is_array($item) && isset($item['email'])) {
                $result[] = (string)$item['email'];
            }
        }
        return $result;
    }

    /**
     * 格式化地址
     * @param string $email
     * @param string $name
     * @return string
     */
    protected function formatAddress(string $email, string $name = ''): string
    {
        $email = trim($email);
        if ($name === '') {
            return $email;
        }
        $encodedName = $this->encodeHeader($name, $this->config['charset'] ?: 'UTF-8');
        return $encodedName . ' <' . $email . '>';
    }

    /**
     * 格式化地址列表
     * @param array $addresses
     * @return string
     */
    protected function formatAddressList(array $addresses): string
    {
        $formatted = [];
        foreach ($addresses as $address) {
            if (is_string($address)) {
                $formatted[] = $address;
                continue;
            }
            if (is_array($address) && isset($address['email'])) {
                $formatted[] = $this->formatAddress((string)$address['email'], (string)($address['name'] ?? ''));
            }
        }
        return implode(', ', $formatted);
    }

    /**
     * 编码头信息
     * @param string $value
     * @param string $charset
     * @return string
     */
    protected function encodeHeader(string $value, string $charset): string
    {
        if ($value === '') {
            return $value;
        }
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($value, $charset, 'B', "\r\n");
        }
        return '=?' . $charset . '?B?' . base64_encode($value) . '?=';
    }

    /**
     * 生成Message-ID
     * @return string
     */
    protected function generateMessageId(): string
    {
        $host = $this->getHostname();
        $random = bin2hex(random_bytes(16));
        return '<' . $random . '@' . $host . '>';
    }

    /**
     * 获取主机名
     * @return string
     */
    protected function getHostname(): string
    {
        $hostname = gethostname();
        if (!$hostname) {
            $hostname = php_uname('n') ?: 'localhost';
        }
        return $hostname;
    }

    /**
     * 构建邮件头
     * @param array $headers
     * @return string
     */
    protected function buildHeaders(array $headers): string
    {
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = $name . ': ' . $value;
        }
        return implode("\r\n", $lines);
    }
}
