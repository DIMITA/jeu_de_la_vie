<?php

// Définition des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// Définition de l'adresse IP et du port du serveur WebSocket
$adresse = '0.0.0.0'; // Toutes les adresses IP disponibles
$port = 8088; // Port d'écoute du serveur WebSocket

// Création du socket serveur
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $adresse, $port);
socket_listen($socket);

echo "Serveur WebSocket démarré sur $adresse:$port\n";

// Tableau pour stocker les clients connectés
$clients = [$socket];

// Boucle principale du serveur
while (true) {
    // Gestion des nouveaux clients et des messages reçus
    $changed = $clients;
    socket_select($changed, $null, $null, 0, 10); // Surveillance des sockets

    // Nouvelle connexion
    if (in_array($socket, $changed)) {
        $socket_new = socket_accept($socket);
        $clients[] = $socket_new;

        // Lecture des en-têtes de poignée de main WebSocket
        $request = socket_read($socket_new, 5000);
        preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
        $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        // Envoi des en-têtes de poignée de main WebSocket
        $headers = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Version: 13\r\n"
            . "Sec-WebSocket-Accept: $key\r\n\r\n";
        socket_write($socket_new, $headers, strlen($headers));

        // Notification de connexion au serveur
        socket_getpeername($socket_new, $ip);
        echo "Nouvelle connexion de $ip\n";

        // Suppression du socket d'écoute de la liste des sockets à surveiller
        $index = array_search($socket, $changed);
        unset($changed[$index]);
    }

    try {
        //code...


        // Gestion des messages reçus des clients
        foreach ($changed as $changed_socket) {
            $buf = socket_read($changed_socket, 1024);
            $received_text = unmask($buf);
            if ($buf === false || strcmp($buf, '') == 0) {
                // Déconnexion du client
                $ip = '';
                socket_getpeername($changed_socket, $ip);
                $index = array_search($changed_socket, $clients);
                socket_close($changed_socket);
                unset($clients[$index]);
                echo "Connexion fermée avec $ip\n";
                continue;
            } else {
                // Traitement des données reçues (à implémenter selon le protocole WebSocket)
                // print_r(json_decode($buf));
                // $received_text = unmask($buf);
                $tst_msg = json_decode($received_text, true);
                // $tst_msg = json_decode($received_text, true); //json decode 
                $user_name = $tst_msg['name']; //sender name
                $user_message = $tst_msg['message']; //message text
                $user_color = $tst_msg['color']; //color

                //prepare data to be sent to client
                $response_text = mask(json_encode(array('type' => 'usermsg', 'name' => $user_name, 'message' => $user_message, 'color' => $user_color)));
                send_message($response_text); //send data

            }
        }
    } catch (\Throwable $th) {
        print_r($th);
    }
}
// close the listening socket
function send_message($msg)
{
    global $clients;
    foreach ($clients as $changed_socket) {
        @socket_write($changed_socket, $msg, strlen($msg));
    }
    return true;
}


//Unmask incoming framed message
function unmask($text)
{

    $decoded = '';

    // Vérifier si les données commencent par un caractère d'échappement
    if (substr($text, 0, 1) === "\x81") {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }

        // Appliquer le masque aux données
        for ($i = 0; $i < strlen($data); ++$i) {
            $decoded .= $data[$i] ^ $masks[$i % 4];
        }
    }

    // Retourner uniquement les données JSON décodées
    return $decoded;
}

//Encode message for transfer to client.
function mask($text)
{
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif ($length > 125 && $length < 65536)
        $header = pack('CCn', $b1, 126, $length);
    elseif ($length >= 65536)
        $header = pack('CCNN', $b1, 127, $length);
    return $header . $text;
}
