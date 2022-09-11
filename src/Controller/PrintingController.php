<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utility\LabelFactory;
use App\Utility\ViewCreator;
use Cake\Core\Configure;
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

        if (
            $this->request->is("GET") &&
            in_array($this->request->getParam('action'), ['socket', 'http'])
        ) {
            return $this->redirect(['action' => 'label']);
        }
    }

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
        }

        $bytes = $socket->write($content);

        $socket->disconnect();

        $this->Flash->highlight($url, [
            'params' => [
                'code' => print_r($data, true),
                'result' => $bytes
            ]
        ]);

        return $this->redirect(['action' => 'label']);
    }

    public function http($count = 1)
    {
        $this->request->allowMethod('POST');

        $data = (new LabelFactory('http_client'))->make($count);

        $content = (new ViewCreator())->xml($data);

        $url = Configure::read('NiceLabel.HTTP_URL');

        $client = new Client();

        $client->setConfig('timeout', 1);

        try {
            $response = $client->post($url, $content, ['type' => 'xml']);
        } catch (\Cake\Http\Client\Exception\NetworkException  $e) {
            $this->Flash->error("Returned Exception Message: " . $e->getMessage());
        }

        if ($response->isOk()) {
            $hl = new \Highlight\Highlighter();
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($response->getXml()->asXML());
            $dom->formatOutput = true;
            $result = $hl->highlight('xml', $dom->saveXML());

            $php = $hl->highlight('php', print_r($data, true));


            // dd($result);
            $this->Flash->highlight($url, [
                'params' => [
                    'code' => "<code class=\"hljs {$php->language}\">"
                        . $php->value . '</code>',
                    'result' => "<code class=\"hljs {$result->language}\">"
                        . $result->value . '</code>'
                ]
            ]);
        }

        return $this->redirect(['action' => 'label']);
    }
}
