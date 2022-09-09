<ul>
    <?php

    use Cake\Utility\Inflector;

    foreach (['socket', 'http'] as $class) : ?>
        <li><?= $this->Form->postLink(
                Inflector::humanize($class),
                [
                    'controller' => 'Printing',
                    'action' => $class
                ],
                ['confirm' => "Do you want to send a label via " . Inflector::humanize($class)]
            ); ?></li>
    <?php endforeach; ?>
</ul>