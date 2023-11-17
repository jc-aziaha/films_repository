<?php
session_start();

    if ( $_SERVER['REQUEST_METHOD'] !== "POST" )
    {
        return header("Location: index.php");
    }

    $postClean = [];

    // Pensons à la cybersécurité
    // Protéger le serveur contre les failles de type Xss
    foreach ($_POST as $key => $value) 
    {
        $postClean[$key] = htmlspecialchars(trim(addslashes($value)));
    }

    if ( !isset($postClean['_method']) || empty($postClean['_method']) || $postClean['_method'] !== "DELETE")
    {
        // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
        // Arrêter l'exécution du script
        return header("Location: index.php");
    }

    if ( 
        !isset($_SESSION['csrf_token']) || !isset($postClean['csrf_token']) ||
        empty($_SESSION['csrf_token'])  || empty($postClean['csrf_token']) ||
        $_SESSION['csrf_token'] !== $postClean['csrf_token']
    ) 
    {
        // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
        // Arrêter l'exécution du script
        unset($_SESSION['csrf_token']);
        return header("Location: index.php");
    }
    unset($_SESSION['csrf_token']);
    

    // Protéger le serveur contre les robots spameurs
    if ( isset($postClean['honey_pot']) && !empty($postClean['honey_pot'])  ) 
    {
        // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
        // Arrêter l'exécution du script
        return header("Location: index.php");
    }


    // Si l'identifiant du film à supprimer n'existe pas ou qu'il n'a pas de valeur,
    if ( !isset($postClean['film_id']) || empty($postClean['film_id']) ) 
    {
        // Rediriger l'utilisateur vers la page d'accueil.
        // Puis, arrêter l'exécution du script
        return header("Location: index.php");
    }


    // Dans le cas contraire,
    
        // Etablir une connexion avec la base de données.
        require __DIR__ . "/db/connexion.php";

        // Effectuer la requête permettant de sélectionner le film dont l'identifiant a été récupéré depuis l'url.
        $req = $db->prepare("SELECT * FROM film WHERE id=:id");
        
        $req->bindValue(":id", $postClean['film_id']);

        $req->execute();

        // Compte le nombre d'enregistrement récupéré de la base
        $row = $req->rowCount();

        // Si ce nombre n'est pas égal à un, c'est que film n'existe pas.
        if ($row != 1)
        {
            // Rediriger l'utilisateur vers la page d'accueil,
            // Puis, arrêter l'exécution du script.
            return header("Location: index.php");
        }

        // Dans le contraire,

        // Effectuer la requête de suppresion du film en base.
        $deleteRequest = $db->prepare("DELETE FROM film WHERE id=:id");
        $deleteRequest->bindValue(":id", $postClean['film_id']);
        $deleteRequest->execute();

        $deleteRequest->closeCursor(); // Non obligatoire.
        
        // Générer le message flash de succès de l'opération de suppression
        $_SESSION['success'] = "Le film a été supprimé.";

        // Rediriger vers la page d'accueil et arrêter l'exécution du script.
        return header("Location: index.php");