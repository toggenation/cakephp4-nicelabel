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
    public function label()
    {
    }
    public function socket($count = 1)
    {
        $this->request->allowMethod('POST');
        $data = (new LabelFactory('socket'))->make($count);

        $content = (new ViewCreator())->csv($data);

        $url = Configure::read('NiceLabel.SOCKET_URL');

        $config = parse_url($url);

        $socket = new Socket($config);

        $socket->setConfig('timeout', 1);

        try {
            $socket->connect();
        } catch (\Cake\Network\Exception\SocketException $e) {
            $this->Flash->error("Returned Exception Message: " . $e->getMessage());
            return $this->redirect(['action' => 'label']);
        }

        $socket->write($content);

        $socket->disconnect();

        $this->Flash->success("sent to $url\n\n" . $content);

        $this->viewBuilder()->setTemplate('socket_http');
    }

    public function http($count = 1)
    {
        $this->request->allowMethod('POST');

        $data = (new LabelFactory('http_client'))->make($count);

        $content = (new ViewCreator())->xml($data);

        $url = Configure::read('NiceLabel.HTTP_CLIENT_URL');

        $client = new Client();

        $client->setConfig('timeout', 1);

        try {
            $response = $client->post($url, $content, ['type' => 'xml']);
        } catch (\Cake\Http\Client\Exception\NetworkException  $e) {
            $this->Flash->error("Returned Exception Message: " . $e->getMessage());
            return $this->redirect(['action' => 'label']);
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
