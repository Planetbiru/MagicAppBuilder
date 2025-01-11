<?php

namespace MagicObject\Util\WS;

/**
 * WebSocket client.
 *
 * By Paragi 2013, Simon Riget MIT license.
 * This is a demonstration of a WebSocket client.
 * If you find flaws in it, please let me know at simon.riget (at) gmail.
 *
 * WebSockets use hybi10 frame encoding:
 * 
 *        0                   1                   2                   3
 *        0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
 *       +-+-+-+-+-------+-+-------------+-------------------------------+
 *       |F|R|R|R| opcode|M| Payload len |    Extended payload length    |
 *       |I|S|S|S|  (4)  |A|     (7)     |             (16/63)           |
 *       |N|V|V|V|       |S|             |   (if payload len == 126/127) |
 *       | |1|2|3|       |K|             |                               |
 *       +-+-+-+-+-------+-+-------------+-------------------------------+
 *       |     Extended payload length continued, if payload len == 127  |
 *       +---------------------------------------------------------------+
 *       |                               |Masking-key, if MASK set to 1  |
 *       +-------------------------------+-------------------------------+
 *       | Masking-key (continued)       |          Payload Data         |
 *       +-------------------------------- - - - - - - - - - - - - - - - +
 *       :                     Payload Data continued ...                :
 *       + - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +
 *       |                     Payload Data continued ...                |
 *       +---------------------------------------------------------------+
 * 
 * See: https://tools.ietf.org/rfc/rfc6455.txt
 * or:  http://tools.ietf.org/html/draft-ietf-hybi-thewebsocketprotocol-10#section-4.2
 *
 * Open WebSocket connection:
 * 
 * resource websocketOpen(string $host [,int $port [,array $headers [,string &$error_string [,int $timeout]]]])
 * 
 * @param string $host The host URL. It can be a domain name like www.example.com or an IP address, with port number. Local host example: 127.0.0.1:8080.
 * @param int $port (Optional) The port number.
 * @param array $headers (Optional) Additional HTTP headers to attach to the request. For example, to parse a session cookie: "Cookie: SID=" . session_id().
 * @param string &$error_string (Optional) A referenced variable to store error messages, if any.
 * @param int $timeout (Optional) The maximum time in seconds that a read operation will wait for an answer from the server. Default value is 10 seconds.
 * @param bool $ssl (Optional) Whether to use SSL for the connection. Default is false.
 * @param bool $persistant (Optional) Whether to use a persistent connection. Default is false.
 * @param string $path (Optional) The path to connect to on the server. Default is '/'.
 * @param resource|null $context (Optional) A stream context resource to be used for the connection.
 *
 * @return resource|false Returns the WebSocket resource on success, or false on failure.
 */
class PicoWebsocketClient
{

    /**
     * Opens a WebSocket connection.
     *
     * This method initiates a WebSocket connection to the specified host and port
     * by sending an HTTP GET request with an upgrade header. If the server accepts
     * the connection, it will respond with a 101 status code indicating the upgrade.
     *
     * @param string $host The host URL (domain or IP address). Example: 'www.example.com'.
     * @param int $port The port number. Defaults to 80 for HTTP and 443 for HTTPS.
     * @param array $headers Optional additional HTTP headers to include in the request.
     * @param string &$error_string Reference to a variable that will store error messages, if any.
     * @param int $timeout The maximum time (in seconds) to wait for a server response. Defaults to 10.
     * @param bool $ssl Whether to use SSL for the connection. Defaults to false.
     * @param bool $persistant Whether to use a persistent connection. Defaults to false.
     * @param string $path The path to connect to on the server. Defaults to '/'.
     * @param resource|null $context A stream context resource to be used for the connection.
     *
     * @return resource|false Returns the WebSocket resource on success, or false on failure.
     */
    public static function websocketOpen($host = '', $port = 80, $headers = array(), &$error_string = '', $timeout = 10, $ssl = false, $persistant = false, $path = '/', $context = null) // NOSONAR
    {

        // Generate a key (to convince server that the update is not random)
        // The key is for the server to prove it i websocket aware. (We know it is)
        $key = base64_encode(openssl_random_pseudo_bytes(16));

        $header = "GET " . $path . " HTTP/1.1\r\n"
            . "Host: $host\r\n"
            . "pragma: no-cache\r\n"
            . "Upgrade: WebSocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: $key\r\n"
            . "Sec-WebSocket-Version: 13\r\n";

        // Add extra headers
        if (!empty($headers)) {
            foreach ($headers as $h) {
                $header .= $h . "\r\n";
            }
        }

        // Add end of header marker
        $header .= "\r\n";

        // Connect to server
        $host = $host ? $host : "127.0.0.1";

        if ($port < 1) {
            if ($ssl) {
                $port = 443;
            } else {
                $port = 80;
            }
        }

        $address = ($ssl ? 'ssl://' : '') . $host . ':' . $port;

        $flags = STREAM_CLIENT_CONNECT | ($persistant ? STREAM_CLIENT_PERSISTENT : 0);
        $ctx = ($context != null) ? $context : stream_context_create();
        $sp = @stream_socket_client($address, $errno, $errstr, $timeout, $flags, $ctx);

        if (!$sp) {
            $error_string = "Unable to connect to websocket server: $errstr ($errno)";
            return false;
        }

        // Set timeouts
        stream_set_timeout($sp, $timeout);

        if (!$persistant || ftell($sp) === 0) {

            //Request upgrade to websocket
            $rc = fwrite($sp, $header);
            if (!$rc) {
                $error_string = "Unable to send upgrade header to websocket server: $errstr ($errno)";
                return false;
            }

            // Read response into an assotiative array of headers. Fails if upgrade failes.
            $reaponse_header = fread($sp, 1024);

            // status code 101 indicates that the WebSocket handshake has completed.
            if (stripos($reaponse_header, ' 101 ') === false || stripos($reaponse_header, 'Sec-WebSocket-Accept: ') === false) {
                $error_string = "Server did not accept to upgrade connection to websocket." . $reaponse_header . E_USER_ERROR;
                return false;
            }
            // The key we send is returned, concatenate with "258EAFA5-E914-47DA-95CA-
            // C5AB0DC85B11" and then base64-encoded. one can verify if one feels the need...

        }
        return $sp;
    }

    /**
     * Sends a message through the WebSocket connection.
     *
     * This method encodes the provided data into a WebSocket frame and sends it to the server.
     *
     * @param resource $sp The WebSocket resource returned by websocketOpen.
     * @param string $data The data to send to the server.
     * @param bool $final Indicates whether this is the final fragment of data. Defaults to true.
     *
     * @return int|false The number of bytes written on success, or false on failure.
     */
    public static function websocketWrite($sp, $data, $final = true) // NOSONAR
    {
        // Assamble header: FINal 0x80 | Opcode 0x02
        $dt = self::hybi10Encode($data, 'text', true);
        return fwrite($sp, $dt);
    }

    /**
     * Sends a WebSocket message through the specified socket.
     *
     * @param resource $sp    The socket resource to write to.
     * @param string   $data  The data to send.
     * @param bool     $final Indicates whether this is the final fragment.
     *
     * @return int|false The number of bytes written, or false on failure.
     */
    public static function websocketWrite2($sp, $data, $final = true)
    {
        // Assemble header: FINal 0x80 | Opcode 0x02
        $header = chr(($final ? 0x80 : 0) | 0x02); // 0x02 binary

        // Mask 0x80 | payload length (0-125)
        if (strlen($data) < 126) {
            $header .= chr(0x80 | strlen($data));
        } elseif (strlen($data) < 0xFFFF) {
            $header .= chr(0x80 | 126) . pack("n", strlen($data));
        } else {
            $header .= chr(0x80 | 127) . pack("N", 0) . pack("N", strlen($data));
        }

        // Add mask
        $mask = pack("N", rand(1, 0x7FFFFFFF));
        $header .= $mask;

        // Mask application data
        for ($i = 0; $i < strlen($data); $i++) {
            $data[$i] = chr(ord($data[$i]) ^ ord($mask[$i % 4]));
        }

        return fwrite($sp, $header . $data);
    }

    /**
     * Reads a message from the WebSocket connection.
     *
     * This method reads a chunk of data from the server, handling the decoding of
     * the WebSocket frame. It waits for the final chunk of data before returning.
     *
     * @param resource $sp The WebSocket resource returned by websocketOpen.
     * @param string|null &$errorCode Reference to a variable that will store an error code, if any.
     * @param string|null &$errorString Reference to a variable that will store an error message, if any.
     *
     * @return string|false The received data on success, or false on failure.
     */
    public static function websocketRead($sp, &$errorCode = null, &$errorString = null) // NOSONAR
    {
        $data = "";
        $errorCode = "000";

        do {
            // Read header
            $header = @fread($sp, 2);
            if (!$header) {
                $errorString = "Reading header from websocket failed.";
                $errorCode = "004";
                return false;
            }

            $opcode = ord($header[0]) & 0x0F;
            $final = ord($header[0]) & 0x80;
            $masked = ord($header[1]) & 0x80;
            $payloadLen = ord($header[1]) & 0x7F;

            // Get payload length extensions
            $extLen = 0;
            if ($payloadLen >= 0x7E) {
                $extLen = 2;
                if ($payloadLen == 0x7F) {
                    $extLen = 8;
                }
                $header = fread($sp, $extLen);
                if (!$header) {
                    $errorString = "Reading header extension from websocket failed.";
                    $errorCode = "003";
                    return false;
                }

                // Set extended payload length
                $payloadLen = 0;
                for ($i = 0; $i < $extLen; $i++) {
                    $payloadLen += ord($header[$i]) << ($extLen - $i - 1) * 8;
                }
            }

            // Get Mask key
            if ($masked) {
                $mask = fread($sp, 4);
                if (!$mask) {
                    $errorString = "Reading header mask from websocket failed.";
                    $errorCode = "002";
                    return false;
                }
            }

            // Get payload
            $frameData = '';
            do {
                $frame = fread($sp, $payloadLen);
                if (!$frame) {
                    $errorString = "Reading from websocket failed.";
                    $errorCode = "001";
                    return false;
                }
                $payloadLen -= strlen($frame);
                $frameData .= $frame;
            } while ($payloadLen > 0);

            // Handle ping requests
            if ($opcode == 9) {
                // Assemble header: FINal 0x80 | Opcode 0x0A + Mask on 0x80 with zero payload
                fwrite($sp, chr(0x8A) . chr(0x80) . pack("N", rand(1, 0x7FFFFFFF)));
                continue; // NOSONAR
            } elseif ($opcode == 8) {
                fclose($sp);
            } elseif ($opcode < 3) {
                // Unmask data
                $dataLen = strlen($frameData);
                if ($masked) {
                    for ($i = 0; $i < $dataLen; $i++) {
                        $data .= $frameData[$i] ^ $mask[$i % 4];
                    }
                } else {
                    $data .= $frameData;
                }
            } else {
                continue; // NOSONAR
            }
        } while (!$final);

        return $data;
    }

    /**
     * Decodes a HyBi 10 WebSocket frame.
     *
     * This method extracts and decodes the payload from a WebSocket frame according
     * to the HyBi 10 specification. It handles masking if the frame is masked and
     * supports both short and long payload lengths.
     *
     * @param string $data The frame data to decode, typically received from the WebSocket.
     *
     * @return string The decoded payload data. If the frame is not a valid WebSocket frame,
     *                the behavior is undefined and may result in an empty string.
     */
    public static function hybi10Decode($data)
    {
        $bytes = $data;
        $dataLength = '';
        $mask = '';
        $codedData = '';
        $decodedData = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);

        if ($masked === true) {
            if ($dataLength === 126) {
                $mask = substr($bytes, 4, 4);
                $codedData = substr($bytes, 8);
            } elseif ($dataLength === 127) {
                $mask = substr($bytes, 10, 4);
                $codedData = substr($bytes, 14);
            } else {
                $mask = substr($bytes, 2, 4);
                $codedData = substr($bytes, 6);
            }
            for ($i = 0; $i < strlen($codedData); $i++) {
                $decodedData .= $codedData[$i] ^ $mask[$i % 4];
            }
        } else {
            if ($dataLength === 126) {
                $decodedData = substr($bytes, 4);
            } elseif ($dataLength === 127) {
                $decodedData = substr($bytes, 10);
            } else {
                $decodedData = substr($bytes, 2);
            }
        }
        return $decodedData;
    }

    /**
     * Gets the frame header byte for the specified frame type.
     *
     * This method returns the appropriate header byte for different WebSocket frame types
     * based on the HyBi 10 protocol. The frame header determines how the WebSocket payload
     * is treated during transmission.
     *
     * @param string $type The type of frame. Acceptable values are:
     *                     - 'text': Represents a text frame.
     *                     - 'close': Represents a close frame.
     *                     - 'ping': Represents a ping frame.
     *                     - 'pong': Represents a pong frame.
     *
     * @return int The frame header byte corresponding to the specified type.
     *             Defaults to the header byte for text frames if the type is unrecognized.
     */
    private static function getFrameHead0($type)
    {
        $frameHead = 0;
        switch ($type) {
            case 'text':
                $frameHead = 129; // FIN, Text-Frame
                break;
            case 'close':
                $frameHead = 136; // FIN, Close Frame
                break;
            case 'ping':
                $frameHead = 137; // FIN, Ping frame
                break;
            case 'pong':
                $frameHead = 138; // FIN, Pong frame
                break;
            default:
                $frameHead = 129; // Default to Text-Frame
                break;
        }
        return $frameHead;
    }

    /**
     * Encodes the payload into a HyBi 10 WebSocket frame.
     *
     * @param string $payload The payload data to encode.
     * @param string $type The type of frame (e.g., 'text', 'close', 'ping', 'pong'). Defaults to 'text'.
     * @param bool $masked Whether to mask the payload. Defaults to true.
     *
     * @return string|false The encoded WebSocket frame on success, or false on failure.
     */
    public static function hybi10Encode($payload, $type = 'text', $masked = true) // NOSONAR
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        $frameHead[0] = self::getFrameHead0($type);

        // Set mask and payload length
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }

            // Most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // Convert frame head to string
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }

        if ($masked === true) {
            // Generate a random mask
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }

        $frame = implode('', $frameHead);

        // Append payload to frame
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }
}
