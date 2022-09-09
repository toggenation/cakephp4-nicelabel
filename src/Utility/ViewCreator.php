<?php

namespace App\Utility;

use Cake\View\ViewBuilder;

class ViewCreator
{
    public function csv($data)
    {
        $vb = new ViewBuilder();

        $header = array_keys($data[0]);

        $vb->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'data',
                'header' => $header
            ]);

        $view = $vb->build();

        $view->set(compact('data'));

        return $view->render();;
    }



    public function xml($data): string
    {

        $vb = new ViewBuilder();

        $vb->setClassName('Xml')
            ->setOptions([
                'serialize' => 'data'
            ]);

        $view = $vb->build();

        $view->set(compact('data'));

        return $view->render();
    }
}
