<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utility\LabelFactory;
use Cake\Chronos\Chronos;
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
    public function socket()
    {
        [$header, $data] = (new LabelFactory())->make();

        $vb = new ViewBuilder();

        $vb->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'data',
                'header' => $header
            ]);

        $view = $vb->build();

        $view->set(compact('data'));

        $send = $view->render();

        $url = 'tcp://10.197.3.140:11973';

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

    public function http()
    {

        [$header, $data] = (new LabelFactory())->make(1);

        $vb = new ViewBuilder();

        $vb->setClassName('Xml')
            ->setOptions([
                // 'rootNode' => 'hiJames',
                'serialize' => 'data'
            ]);

        $view = $vb->build();

        $view->set(compact('data'));

        $send = $view->render();

        $url = 'http://10.197.3.140:56425/palletPrint';


        $client = new Client();

        $response = $client->post($url, $send, ['type' => 'xml']);

        return $this->getResponse()
            ->withStringBody($response->getXml()->asXML())
            ->withType('xml');
    }
}
