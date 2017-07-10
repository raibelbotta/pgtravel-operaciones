<?php

exec("cd ../ && git pull origin master && php app/console doctrine:schema:update --force && del /s /q app\cache\prod", $output);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    <code>
        <?php foreach ($output as $line) : ?>
            <?php echo $line ?><br>
        <?php endforeach ?>
    </code>
</body>
</html>
