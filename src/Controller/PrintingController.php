<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utility\LabelFactory;
use App\Utility\ViewCreator;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Network\Socket;
use Cake\View\ViewBuilder;

/**
 * Printing Controller
 *
 */
class PrintingController extends AppController
{
    public function socket($count = 1)
    {

        $data = (new LabelFactory('socket'))->make($count);

        $content = (new ViewCreator())->csv($data);

        $url = Configure::read('NiceLabel.SOCKET_URL');

        $config = parse_url($url);

        $socket = new Socket($config);

        $socket->setConfig('timeout', 1);

        try {
            $socket->connect();
        } catch (\Cake\Network\Exception\SocketException $e) {
            return $this->getResponse()
                ->withStringBody($e->getMessage())
                ->withType('text');
        }

        $socket->write($content);

        $socket->disconnect();

        return $this->getResponse()
            ->withStringBody("sent to $url\n\n" . $content)
            ->withType('text');
    }

    public function http($count = 1)
    {
        $data = (new LabelFactory('http_client'))->make($count);

        $content = (new ViewCreator())->xml($data);

        $url = Configure::read('NiceLabel.HTTP_CLIENT_URL');

        $client = new Client();

        $client->setConfig('timeout', 2);

        try {
            $response = $client->post($url, $content, ['type' => 'xml']);
        } catch (\Cake\Http\Client\Exception\NetworkException  $e) {
            return $this->getResponse()
                ->withStringBody("Returned Exception Message: " . $e->getMessage())
                ->withType('text');
        }

        if ($response->isOk()) {
            return $this->getResponse()
                ->withStringBody('<pre>' . htmlentities($response->getXml()->asXML()) . '</pre>')
                ->withType('html');
        }

        return $this->getResponse()
            ->withStringBody((string) $response->getStatusCode())
            ->withType('text');
    }
}
