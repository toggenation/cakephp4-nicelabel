<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utility\LabelFactory;
use App\Utility\ViewCreator;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventInterface;
use Cake\Http\Client;
use Cake\Network\Socket;
use Cake\View\ViewBuilder;
use DOMDocument;

/**
 * Printing Controller
 *
 */
class PrintingController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $redirect = $this->request->is('GET') &&
            in_array($this->request->getParam('action'), ['socket', 'http']);

        if ($redirect) {
            return $this->redirect(['action' => 'label']);
        }
    }
    public function label($count = 1)
    {
        $data = (new LabelFactory('socket'))->make($count);

        $this->set(compact('data'));
    }
    public function socket($count = 1)
    {
        $this->request->allowMethod('POST');

        $content = (new ViewCreator())
            ->csv($this->request->getData());

        $url = Configure::read('NiceLabel.SOCKET_URL');

        $config = parse_url($url);

        $socket = new Socket($config);

        $socket->setConfig('timeout', 1);

        try {
            $socket->connect();
        } catch (\Cake\Network\Exception\SocketException $e) {
            $this->Flash->error($e->getMessage());

            return $this->redirect(['action' => 'label']);
        }


        $socket->write($content);

        $socket->disconnect();

        $this->Flash->success("Sent to $url\n\n" . $content);

        return $this->redirect(['action' => 'label']);
    }

    public function http($count = 1)
    {
        $this->request->allowMethod('POST');

        $content = (new ViewCreator())
            ->xml($this->request->getData());

        $url = Configure::read('NiceLabel.HTTP_CLIENT_URL');

        $client = new Client();

        $client->setConfig('timeout', 1);

        try {
            $response = $client->post($url, $content, ['type' => 'xml']);
            $message = '<pre>' . htmlentities($response->getXml()->asXML()) . '</pre>';
            $this->Flash->success($message);
        } catch (\Cake\Http\Client\Exception\NetworkException  $e) {
            $this->Flash->error($e->getMessage());
        }

        return $this->redirect(['action' => 'label']);
    }
}
