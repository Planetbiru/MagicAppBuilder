<?php

namespace AppBuilder\WebSocket;
use Socket;

/**
 * WebSocketServer
 *
 * A simple WebSocket server implementation for handling real-time, bidirectional communication.
 * This server manages client connections, performs WebSocket handshakes, decodes and encodes messages,
 * manages sessions via cookies, and broadcasts messages to connected clients.
 *
 * Features:
 * - Handles WebSocket protocol handshake and message framing.
 * - Associates each client with a PHP session using cookies.
 * - Broadcasts messages to all connected clients except the sender.
 * - Stores the last message in the user's session.
 * - Supports custom host, port, and session name.
 *
 * Usage:
 * $server = new WebSocketServer('0.0.0.0', 8080, 'PHPSESSID');
 * $server->start();
 */
class WebSocketServer {
    /**
     * Host address to bind the WebSocket server.
     *
     * @var string
     */
    private $host;

    /**
     * Port number to listen for WebSocket connections.
     *
     * @var int
     */
    private $port;

    /**
     * Name of the PHP session cookie.
     *
     * @var string
     */
    private $sessionName;

    /**
     * Main server socket resource.
     *
     * @var Socket
     */
    private $serverSocket;

    /**
     * Array of all connected client sockets.
     *
     * @var Socket[]
     */
    private $clients = [];

    /**
     * Array to track handshake status for each client.
     *
     * @var array
     */
    private $handshakes = [];

    /**
     * Array to map client sockets to session IDs.
     *
     * @var array
     */
    private $sessions = [];

    /**
     * Constructor for WebSocketServer.
     *
     * @param string $host The host address to bind the server.
     * @param int $port The port number to listen on.
     * @param string $sessionName The name of the PHP session cookie.
     */
    public function __construct($host = '0.0.0.0', $port = 8080, $sessionName = 'PHPSESSID') {
        $this->host = $host;
        $this->port = $port;
        $this->sessionName = $sessionName;
    }

    /**
     * Starts the WebSocket server and handles incoming connections.
     *
     * This method creates the server socket, listens for new clients,
     * performs handshakes, receives and broadcasts messages, and manages sessions.
     *
     * @return void
     */
    public function start() // NOSONAR
    {
        // Create server socket
        $this->serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->serverSocket, $this->host, $this->port);
        socket_listen($this->serverSocket);

        $this->clients[] = $this->serverSocket;

        echo "WebSocket server running at ws://{$this->host}:{$this->port} with session name '{$this->sessionName}'\n";

        $running = true;
        while ($running) {
            $read = $this->clients;
            $write = null;
            $except = null;
            socket_select($read, $write, $except, 0); // NOSONAR
            if ($read === false) {
                continue; // Skip if select failed
            }

            foreach ($read as $sock) {
                if ($sock === $this->serverSocket) {
                    // Accept new client connection
                    $newClient = socket_accept($this->serverSocket);
                    $this->clients[] = $newClient;
                } else {
                    // Receive data from client
                    $bytes = @socket_recv($sock, $buffer, 2048, 0);

                    if ($bytes === false || $bytes === 0) {
                        // Disconnect client if no data received
                        $this->disconnect($sock);
                        continue;
                    }

                    if (!isset($this->handshakes[(int)$sock])) {
                        // Perform WebSocket handshake if not done yet
                        $this->performHandshake($sock, $buffer);
                        $this->handshakes[(int)$sock] = true;
                    } else {
                        // Decode and process message
                        $message = $this->decode($buffer);
                        echo "Message received from session {$this->sessions[(int)$sock]}: $message\n";

                        // Call onMessage event handler
                        $this->onMessage($sock, $message);

                        // Check for stop command
                        if ($this->isCommandToStop($message)) {
                            echo "Stop command received. Shutting down server...\n";
                            $running = false;
                            break 2; // Exit both foreach and while
                        }

                        $sessionId = $this->sessions[(int)$sock];
                        if ($sessionId) {
                            session_name($this->sessionName);
                            session_id($sessionId);
                            session_start();
                            $_SESSION['last_message'] = $message;
                            session_write_close();
                        }

                        // Broadcast message to other clients
                        $this->broadcast("[{$sessionId}] $message", $sock);
                    }
                }
            }
        }

        // Clean up all sockets and shutdown
        foreach ($this->clients as $client) {
            if (is_resource($client)) {
                @socket_close($client);
            }
        }
        if (is_resource($this->serverSocket)) {
            @socket_close($this->serverSocket);
        }
        echo "WebSocket server stopped.\n";
    }

    private function isCommandToStop($message) {
        // Check if the message is a command to stop the server
        return trim($message) === 'stop' || trim($message) === 'shutdown';
    }

    /**
     * Performs the WebSocket handshake with a client.
     *
     * @param Socket $client The client socket.
     * @param string $buffer The received data buffer.
     * @return void
     */
    private function performHandshake($client, $buffer) {
        // Get WebSocket Key from headers
        preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $matches);
        $key = trim($matches[1]);

        // Get Cookie from headers
        preg_match("/Cookie: (.*)\r\n/", $buffer, $cookieMatches);
        $cookies = $this->parseCookies($cookieMatches[1] ?? '');
        $sessionId = $cookies[$this->sessionName] ?? null;

        if ($sessionId) {
            session_name($this->sessionName);
            session_id($sessionId);
            session_start();
            echo "Active session with {$this->sessionName} = $sessionId\n";
            if (!isset($_SESSION['connected'])) {
                $_SESSION['connected'] = true;
                $_SESSION['connected_at'] = date('Y-m-d H:i:s');
            }
            session_write_close();
        }

        $this->sessions[(int)$client] = $sessionId;

        // Generate Sec-WebSocket-Accept header
        $accept = base64_encode(pack(
            'H*',
            sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
        ));

        $headers = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $accept\r\n\r\n";

        socket_write($client, $headers, strlen($headers));
    }

    /**
     * Parses the Cookie header into an associative array.
     *
     * @param string $cookieHeader The raw Cookie header string.
     * @return array Associative array of cookie names and values.
     */
    private function parseCookies(string $cookieHeader): array {
        $cookies = [];
        $pairs = explode(';', $cookieHeader);
        foreach ($pairs as $pair) {
            $parts = explode('=', trim($pair), 2);
            if (count($parts) === 2) {
                $cookies[$parts[0]] = urldecode($parts[1]);
            }
        }
        return $cookies;
    }

    /**
     * Decodes a WebSocket frame to extract the payload message.
     *
     * @param string $data The raw WebSocket frame data.
     * @return string The decoded message.
     */
    private function decode($data) {
        $decoded = '';
        if (strlen($data) > 1) {
            // Check if the first byte indicates a text frame
            $length = ord($data[1]) & 127;

            if ($length === 126) {
                $masks = substr($data, 4, 4);
                $payload = substr($data, 8);
            } elseif ($length === 127) {
                $masks = substr($data, 10, 4);
                $payload = substr($data, 14);
            } else {
                $masks = substr($data, 2, 4);
                $payload = substr($data, 6);
            }
            
            for ($i = 0; $i < strlen($payload); ++$i) {
                $decoded .= $payload[$i] ^ $masks[$i % 4];
            }
        }

        return $decoded;
    }

    /**
     * Encodes a message into a WebSocket frame.
     *
     * @param string $text The message to encode.
     * @return string The encoded WebSocket frame.
     */
    private function encode($text) {
        $b1 = 0x81; // FIN + text frame
        $length = strlen($text);

        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length <= 65535) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, 0, $length);
        }

        return $header . $text;
    }

    /**
     * Broadcasts a message to all connected clients.
     *
     * @param string $message The message to broadcast.
     * @return void
     */
    public function broadcastToAll($message) {
        $encoded = $this->encode($message);
        foreach ($this->clients as $client) {
            if ($client !== $this->serverSocket) {
                @socket_write($client, $encoded, strlen($encoded));
            }
        }
    }

    /**
     * Broadcasts a message to all connected clients except the excluded socket.
     *
     * @param string $message The message to broadcast.
     * @param resource|null $excludeSocket The client socket to exclude from broadcasting.
     * @return void
     */
    private function broadcast($message, $excludeSocket = null) {
        $encoded = $this->encode($message);
        foreach ($this->clients as $client) {
            if ($client !== $this->serverSocket && $client !== $excludeSocket) {
                @socket_write($client, $encoded, strlen($encoded));
            }
        }
    }

    /**
     * Disconnects a client and cleans up resources.
     *
     * @param Socket $sock The client socket to disconnect.
     * @return void
     */
    private function disconnect($sock) {
        $index = array_search($sock, $this->clients);
        if ($index !== false) {
            unset($this->clients[$index]);
            unset($this->handshakes[(int)$sock]);
            unset($this->sessions[(int)$sock]);
            socket_close($sock);
            echo "Client disconnected.\n";
        }
    }

    /**
     * Called when a message is received from a client.
     * Override this method to handle custom message logic.
     *
     * @param Socket $client The client socket.
     * @param string $message The received message.
     * @return void
     */
    protected function onMessage($client, $message) // NOSONAR
    {
        // Default: print the message
        echo "Message received from client: $message\n";
    }

    /**
     * Send a message to a specific client.
     *
     * @param Socket $client The client socket to send the message to.
     * @param string $message The message to send.
     * @return void
     */
    public function send($client, $message) {
        $encoded = $this->encode($message);
        @socket_write($client, $encoded, strlen($encoded));
    }
}
