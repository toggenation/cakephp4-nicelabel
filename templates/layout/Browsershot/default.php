<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browsershot</title>
    <?= $this->fetch('css'); ?>
</head>

<body>
    <?= $this->fetch('content'); ?>
</body>

</html>