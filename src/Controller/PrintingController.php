<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utility\LabelFactory;
use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Network\Socket;
use Cake\Routing\Router;
use Cake\View\ViewBuilder;
use CsvView\View\CsvView;
use Faker\Factory;

/**
 * Printing Controller
 *
 */
class PrintingController extends AppController
{
    public function socket($count = 1)
    {

        [$header, $data] = (new LabelFactory('socket'))->make($count);

        $vb = new ViewBuilder();

        $vb->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'data',
                'header' => $header
            ]);

        $view = $vb->build();

        $view->set(compact('data'));

        $send = $view->render();

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

        $socket->write($send);

        $socket->disconnect();

        return $this->getResponse()
            ->withStringBody("sent to $url\n\n" . $send)
            ->withType('text');
    }

    public function http($count = 1)
    {
        [$header, $data] = (new LabelFactory('http_client'))->make($count);

        $vb = new ViewBuilder();

        $vb->setClassName('Xml')
            ->setOptions([
                'serialize' => 'data'
            ]);

        $view = $vb->build();

        $view->set(compact('data'));

        $send = $view->render();

        $url = Configure::read('NiceLabel.HTTP_CLIENT_URL');

        $client = new Client();

        $client->setConfig('timeout', 2);

        try {
            $response = $client->post($url, $send, ['type' => 'xml']);
        } catch (\Cake\Http\Client\Exception\NetworkException  $e) {
            return $this->getResponse()
                ->withStringBody("Returned Exception Message: " . $e->getMessage())
                ->withType('text');
        }

        if ($response->isOk()) {
            return $this->getResponse()
                ->withStringBody($response->getXml()->asXML())
                ->withType('xml');
        }

        return $this->getResponse()
            ->withStringBody((string) $response->getStatusCode())
            ->withType('text');
    }
}
