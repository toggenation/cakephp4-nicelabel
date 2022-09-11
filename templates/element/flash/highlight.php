<?php

/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div>
    <p>Sent to <?= $message ?></p>
    <pre style="padding-left: 30px;"><?= $params['code']; ?></pre>
    <pre style="padding-left: 30px;"><?= $params['result']; ?></pre>
</div>