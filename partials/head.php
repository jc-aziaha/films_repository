<!DOCTYPE html>
<html lang="fr">
    <head>
        <!-- Encodage des caractères -->
        <meta charset="UTF-8">

        <!-- Minimum de compatibilité avec Internet Explorer -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <!-- Minimum de Responsive design à toujours mettre en place -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Le titre de la page -->
        <title>Cinema<?= isset($title) ? " - $title" : ""; ?></title>
        
        <?php if(isset($description) && !empty($description)) : ?>
            <!-- La description de la page -->
            <meta name="description" content="<?= $description; ?>">
        <?php endif ?>

        <?php if(isset($keywords) && !empty($keywords)) : ?>
            <!-- Mots clés -->
            <meta name="keywords" content="<?= $keywords; ?>">
        <?php endif ?>

        <!-- Les familles de polices depuis Google -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins&display=swap" rel="stylesheet">

        <!-- Ma favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="assets/images/favicon/site.webmanifest">
        <link rel="mask-icon" href="assets/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="theme-color" content="#ffffff">

        <?php if(isset($font_awesome) && !empty($font_awesome)) : ?>
            <!-- Font awesome -->
            <?= $font_awesome; ?>
        <?php endif ?>

        <!-- Le cadre de travail Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

        <!-- La feuille de style -->
        <link rel="stylesheet" href="assets/styles/app.css">
    </head>
    <body class="bg-light">