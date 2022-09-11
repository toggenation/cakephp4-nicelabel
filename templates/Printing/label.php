<?php

/**
 * @var \App\View\AppView $this
 */

use Cake\Utility\Inflector;
?>
<ul>
    <?php foreach (['socket', 'http'] as $class) : ?>
        <li style="display: inline-block; width: 20rem; padding: 20px;">
            <?= $this->Form->postLink(
                Inflector::humanize($class),
                [
                    'controller' => 'Printing',
                    'action' => $class
                ],
                [
                    'data' => $data,
                    'confirm' => "Do you want to send a label via " . Inflector::humanize($class)
                ]
            ); ?>
        </li>
    <?php endforeach; ?>
</ul>