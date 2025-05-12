<?php
class RouterosAPI
{
    public bool $debug = false;
    public bool $connected = false;
    public int $port = 8728; // default MikroTik API port
    public bool $ssl = false;
    public bool $certless = false;
    public int $timeout = 3;
    public int $attempts = 5;
    public int $delay = 3;

    private $socket;
    private int $error_no = 0;
    private string $error_str = '';

    public function isIterable($var)
    {
        return $var !== null && (is_array($var) || $var instanceof Traversable);
    }

    public function debug($text)
    {
        if ($this->debug) {
            echo $text . "\n";
        }
    }

    public function encodeLength($length)
    {
        if ($length < 0x80) {
            return chr($length);
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            return chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            return chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            return chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } else {
            return chr(0xF0) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }
    }

    public function connect($ip, $login, $password)
    {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $this->connected = false;
            $protocol = $this->ssl ? 'ssl://' : '';
            $contextOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
                    'ciphers' => 'ALL'
                ]
            ];
            $context = stream_context_create($contextOptions);

            $this->debug("Connection attempt #$attempt to $protocol$ip:$this->port...");
            $this->socket = @stream_socket_client($protocol . $ip . ':' . $this->port, $this->error_no, $this->error_str, $this->timeout, STREAM_CLIENT_CONNECT, $context);

            if ($this->socket) {
                stream_set_timeout($this->socket, $this->timeout);

                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
                $response = $this->read(false);

                if (isset($response[0]) && $response[0] == '!done') {
                    if (!isset($response[1])) {
                        $this->connected = true;
                        break;
                    } else {
                        if (preg_match('/=ret=([a-f0-9]+)/i', $response[1], $matches)) {
                            $this->write('/login', false);
                            $this->write('=name=' . $login, false);
                            $this->write('=response=00' . md5(chr(0) . $password . hex2bin($matches[1])));
                            $response = $this->read(false);
                            if (isset($response[0]) && $response[0] == '!done') {
                                $this->connected = true;
                                break;
                            }
                        }
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }

        $this->debug($this->connected ? 'Connected...' : 'Connection Failed...');
        return $this->connected;
    }

    public function disconnect()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->connected = false;
        $this->debug('Disconnected...');
    }

    public function parseResponse($response)
    {
        $parsed = [];
        if (is_array($response)) {
            $current = null;
            $singlevalue = null;

            foreach ($response as $x) {
                if (in_array($x, ['!fatal', '!re', '!trap'])) {
                    if ($x == '!re') {
                        $current = &$parsed[];
                    } else {
                        $current = &$parsed[$x][];
                    }
                } elseif ($x != '!done') {
                    if (preg_match('/=([^=]+)=(.*)/', $x, $matches)) {
                        $current[$matches[1]] = $matches[2];
                    }
                }
            }

            if (empty($parsed) && $singlevalue !== null) {
                return $singlevalue;
            }
        }
        return $parsed;
    }

    public function read($parse = true)
    {
        $response = [];
        $receivedDone = false;

        while (true) {
            $length = $this->readLength();
            $_ = '';

            if ($length > 0) {
                while (strlen($_) < $length) {
                    $_ .= fread($this->socket, $length - strlen($_));
                }
                $response[] = $_;
                $this->debug(">>> [$length bytes] $_");
            }

            if ($_ === '!done') {
                $receivedDone = true;
            }

            $status = stream_get_meta_data($this->socket);
            if ((!$this->connected && !$status['unread_bytes']) || ($this->connected && !$status['unread_bytes'] && $receivedDone) || $status['timed_out']) {
                break;
            }
        }

        return $parse ? $this->parseResponse($response) : $response;
    }

    private function readLength()
    {
        $c = ord(fread($this->socket, 1));
        if ($c < 0x80) {
            return $c;
        } elseif ($c < 0xC0) {
            $c &= ~0x80;
            return ($c << 8) + ord(fread($this->socket, 1));
        } elseif ($c < 0xE0) {
            $c &= ~0xC0;
            return ($c << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        } elseif ($c < 0xF0) {
            $c &= ~0xE0;
            return ($c << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        } else {
            return (ord(fread($this->socket, 1)) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        }
    }

    public function write($command, $param2 = true)
    {
        if (empty($command)) return false;

        $data = explode("\n", $command);
        foreach ($data as $line) {
            fwrite($this->socket, $this->encodeLength(strlen($line)) . $line);
            $this->debug('<<< ' . $line);
        }

        if (is_int($param2)) {
            fwrite($this->socket, $this->encodeLength(strlen('.tag=' . $param2)) . '.tag=' . $param2 . chr(0));
        } elseif (is_bool($param2) && $param2) {
            fwrite($this->socket, chr(0));
        }

        return true;
    }

    public function comm($com, $arr = [])
    {
        $this->write($com, !$arr);
        $i = 0;
        $count = count($arr);

        if ($this->isIterable($arr)) {
            foreach ($arr as $k => $v) {
                $cmd = ($k[0] == '?' || $k[0] == '~') ? "$k=$v" : "=$k=$v";
                $this->write($cmd, ++$i == $count);
            }
        }

        return $this->read();
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
?>
