<?php

/**
 * WebSocket Server Runner Script
 * 
 * This script checks whether a WebSocket server is already running on a specific host and port.
 * If so, it attempts to terminate it before starting a new instance in the background.
 * The output is returned in JSON format.
 */

header('Content-Type: application/json');

$host = '127.0.0.1';
$port = 8080;
$timeout = 1;

/**
 * Check if WebSocket server is already running by attempting to connect to the host and port.
 *
 * @param string $host
 * @param int $port
 * @param int $timeout
 * @return bool
 */
function isServerRunning($host, $port, $timeout)
{
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($socket) {
        fclose($socket);
        return true;
    }
    return false;
}

/**
 * Attempt to stop an existing WebSocket server process listening on the specified port.
 *
 * @param string $host Server host
 * @param int $port Server port
 * @return void
 */
function stopServer($host, $port) // NOSONAR
{
    if (PHP_OS === 'WINNT') {
        // Use netstat to find process ID listening on the port (Windows)
        $output = shell_exec("netstat -ano | findstr \":{$port}\" | findstr \"LISTENING\"");
        preg_match('/\s+TCP\s+[^\s]+\s+[^\s]+\s+LISTENING\s+(\d+)/i', $output, $matches);
        if (isset($matches[1])) {
            $pid = $matches[1];
            shell_exec("taskkill /F /PID {$pid}");
        }
    } else {
        // Use pgrep to find matching PHP process (Unix-like systems)
        $output = shell_exec("pgrep -f \"php .*websocket-server.php.*{$port}\"");
        $pids = array_filter(explode("\n", trim($output)));
        foreach ($pids as $pid) {
            shell_exec("kill -9 {$pid}");
        }
    }

    // Wait briefly to allow process to terminate
    sleep(1);
}

/**
 * Broadcast a message to all connected WebSocket clients.
 *
 * @param string $host
 * @param int $port
 * @param string $message
 * @return bool
 */
function broadcastToWebSocketClients($host, $port, $message)
{
    $socket = @fsockopen($host, $port, $errno, $errstr, 2);
    if ($socket) {
        // Perform WebSocket handshake
        $key = base64_encode(random_bytes(16));
        $headers = "GET / HTTP/1.1\r\n"
            . "Host: {$host}:{$port}\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Key: {$key}\r\n"
            . "Sec-WebSocket-Version: 13\r\n\r\n";
        fwrite($socket, $headers);
        stream_set_timeout($socket, 2);
        $response = fread($socket, 2048); // NOSONAR

        // Encode message as WebSocket frame
        $len = strlen($message);
        if ($len <= 125) {
            $frame = chr(0x81) . chr($len) . $message;
        } elseif ($len <= 65535) {
            $frame = chr(0x81) . chr(126) . pack("n", $len) . $message;
        } else {
            $frame = chr(0x81) . chr(127) . pack("J", $len) . $message;
        }

        fwrite($socket, $frame);
        fclose($socket);
        return true;
    }
    return false;
}

function startServer()
{
    if (PHP_OS == 'WINNT') {
        $phpPath = 'php';
        if (strtolower(substr(PHP_BINARY, -7)) === 'php.exe' && file_exists(PHP_BINARY)) {
            $phpPath = escapeshellarg(PHP_BINARY);
        } else {
            $wherePhp = shell_exec('where php');
            if ($wherePhp) {
                $phpPath = escapeshellarg(trim(strtok($wherePhp, "\r\n")));
            } else {
                echo "Peringatan: Tidak dapat menemukan path lengkap php.exe. Pastikan PHP ada di dalam PATH environment Anda.\n";
            }
        }

        $serverScript = escapeshellarg(__DIR__ . '/websocket-server.php');
        $command = "{$phpPath} {$serverScript}";

        $wshell = new COM("WScript.Shell");
        $wshell->Run($command , 0, false);
        $response = [
            'status' => 'success',
            'message' => 'WebSocket server started asynchronously.'
        ];
        echo json_encode($response);
        exit;

    } else {
        $phpPath = (PHP_BINARY !== '' ? PHP_BINARY : 'php');
        $serverScript = __DIR__ . '/websocket-server.php';
        $command = "nohup $phpPath {$serverScript} > /dev/null 2>&1 &";
        exec($command);
    }
}

if (PHP_OS == 'WINNT' && !class_exists('COM')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'COM extension is required to run the WebSocket server on Windows.'
    ]);
    exit;
}

// If a WebSocket server is already running, stop it first
if (isServerRunning($host, $port, $timeout)) {
    stopServer($host, $port);
}
startServer();




