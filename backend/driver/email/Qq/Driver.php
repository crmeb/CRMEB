<?php
declare(strict_types=1);

namespace driver\email\Qq;

use crmeb\services\email\BaseEmail;
use crmeb\services\email\EmailInterface;

class Driver extends BaseEmail implements EmailInterface
{
    protected $config = [];

    protected function initialize(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $content, array $options = []): bool
    {
        $host = $this->config['qq_host'] ?? 'smtp.qq.com';
        $port = (int)($this->config['qq_port'] ?? 465);
        $username = $this->config['qq_username'] ?? '';
        $authCode = $this->config['qq_auth_code'] ?? '';
        $fromName = $this->config['from_name'] ?? 'CRMEB';

        if (empty($host) || empty($username) || empty($authCode)) {
            return $this->setError('QQ邮箱配置不完整');
        }

        try {
            $socketType = $port === 465 ? 'ssl' : ($port === 587 ? 'tls' : 'tcp');
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            // 出口网络对 465/587 的 TLS 握手存在秒级瞬断/风控限流窗口（stream_socket_client
            // 失败且 errno=0，如 Failed to enable crypto），连接失败按递增间隔自动重试
            $socket = false;
            $lastError = '';
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $socket = @stream_socket_client(
                    "{$socketType}://{$host}:{$port}",
                    $errno,
                    $errstr,
                    30,
                    STREAM_CLIENT_CONNECT,
                    $context
                );
                if ($socket) {
                    break;
                }
                $lastError = "QQ邮箱SMTP连接失败: {$errstr} ({$errno})";
                if ($attempt < 3) {
                    usleep($attempt * 600000);
                }
            }

            if (!$socket) {
                return $this->setError($lastError);
            }

            stream_set_timeout($socket, 30);

            if (!$this->checkResponse($socket, 220)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP服务器响应异常');
            }

            $this->sendCommand($socket, "EHLO " . gethostname());
            if (!$this->checkResponse($socket, 250)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP EHLO失败');
            }

            if ($port === 587) {
                $this->sendCommand($socket, "STARTTLS");
                if (!$this->checkResponse($socket, 220)) {
                    fclose($socket);
                    return $this->setError('QQ邮箱SMTP STARTTLS失败');
                }
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand($socket, "EHLO " . gethostname());
                if (!$this->checkResponse($socket, 250)) {
                    fclose($socket);
                    return $this->setError('QQ邮箱SMTP EHLO(加密后)失败');
                }
            }

            $this->sendCommand($socket, "AUTH LOGIN");
            if (!$this->checkResponse($socket, 334)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP AUTH失败');
            }

            $this->sendCommand($socket, base64_encode($username));
            if (!$this->checkResponse($socket, 334)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP用户名认证失败');
            }

            $this->sendCommand($socket, base64_encode($authCode));
            if (!$this->checkResponse($socket, 235)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP授权码认证失败');
            }

            $this->sendCommand($socket, "MAIL FROM:<{$username}>");
            if (!$this->checkResponse($socket, 250)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP MAIL FROM失败');
            }

            $this->sendCommand($socket, "RCPT TO:<{$to}>");
            if (!$this->checkResponse($socket, 250)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP RCPT TO失败');
            }

            $this->sendCommand($socket, "DATA");
            if (!$this->checkResponse($socket, 354)) {
                fclose($socket);
                return $this->setError('QQ邮箱SMTP DATA失败');
            }

            $headers = $this->buildHeaders($username, $fromName, $to, $subject);
            // 正文按声明的 Content-Transfer-Encoding: base64 真实编码（chunk_split 每 76 字符换行，
            // 避免 SMTP 998 字节行限制与点透明问题）；headers 与正文之间以空行分隔
            $body = chunk_split(base64_encode($content));
            $message = $headers . "\r\n\r\n" . $body . "\r\n.";
            $this->sendCommand($socket, $message);

            if (!$this->checkResponse($socket, 250)) {
                fclose($socket);
                return $this->setError('QQ邮箱邮件发送失败');
            }

            $this->sendCommand($socket, "QUIT");
            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            return $this->setError('QQ邮箱邮件发送异常: ' . $e->getMessage());
        }
    }

    protected function sendCommand($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    protected function checkResponse($socket, int $expectedCode): bool
    {
        $response = '';
        do {
            $line = fgets($socket, 512);
            if ($line === false) {
                return false;
            }
            $response .= $line;
        } while (substr($line, 3, 1) === '-');

        $code = (int)substr($response, 0, 3);
        return $code === $expectedCode;
    }

    protected function buildHeaders(string $username, string $fromName, string $to, string $subject): string
    {
        $fromNameEncoded = $this->encodeHeader($fromName);
        $subjectEncoded = $this->encodeHeader($subject);

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "From: {$fromNameEncoded} <{$username}>";
        $headers[] = "To: <{$to}>";
        $headers[] = "Subject: {$subjectEncoded}";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . md5(uniqid((string)time())) . "@qq.com>";
        $headers[] = "Reply-To: {$username}";

        return implode("\r\n", $headers);
    }

    protected function encodeHeader(string $text): string
    {
        if (preg_match('/[\x80-\xff]/', $text)) {
            return "=?UTF-8?B?" . base64_encode($text) . "?=";
        }
        return $text;
    }
}
