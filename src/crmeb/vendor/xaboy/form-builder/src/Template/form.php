<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $form->getTitle() ?></title>
    <?= $form->parseDependScript() ?>
</head>
<body>
<script>
    (function () {
        var create = <?=$form->formScript()?>;
        create();
    })();
</script>
</body>
</html>