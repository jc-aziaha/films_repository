<?php
session_start();

    // Si l'identifiant du film à modifier n'existe pas ou qu'elle n'a pas de valeur,
    if ( !isset($_GET['film_id']) || empty($_GET['film_id']) ) 
    {
        // Rediriger l'utilisateur vers la page d'accueil.
        // Puis, arrêter l'exécution du script
        return header("Location: index.php");
    }

    // Dans le cas contraire,

        // Protéger le serveur contre les failles de type XSS.
        $filmId = (int) htmlspecialchars($_GET['film_id']);
    
        // Etablir une connexion avec la base de données.
        require __DIR__ . "/db/connexion.php";

        // Effectuer la requête permettant de sélectionner le film dont l'identifiant a été récupéré depuis l'url.
        $req = $db->prepare("SELECT * FROM film WHERE id=:id");
        
        $req->bindValue(":id", $filmId);

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
            // Récupérons les données de ce film afin de les afficher dans le formulaire de modification.
        $film = $req->fetch();

    // Si les données arrivent au serveur via la méthode POST
    if ( $_SERVER['REQUEST_METHOD'] === "POST" )
    {

        if ( !isset($_POST['_method']) || empty($_POST['_method']) || $_POST['_method'] !== "PUT")
        {
            // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
            // Arrêter l'exécution du script
            return header("Location: $_SERVER[HTTP_REFERER]");
        }

        $postClean = [];
        $errors    = [];

        // Pensons à la cybersécurité
        // Protéger le serveur contre les failles de type Xss
        foreach ($_POST as $key => $value) 
        {
            $postClean[$key] = htmlspecialchars(trim(addslashes($value)));
            // $postClean[$key] = strip_tags($value);
            // $postClean[$key] = htmlentities($value);
        }

        // Protéger le serveur contre les failles de type Csrf
        if ( 
            !isset($_SESSION['csrf_token']) || !isset($postClean['csrf_token']) ||
            empty($_SESSION['csrf_token'])  || empty($postClean['csrf_token']) ||
            $_SESSION['csrf_token'] !== $postClean['csrf_token']
        ) 
        {
            // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
            // Arrêter l'exécution du script
            return header("Location: $_SERVER[HTTP_REFERER]");
        }
        
        // Protéger le serveur contre les robots spameurs
        if ( isset($postClean['honey_pot']) && !empty($postClean['honey_pot'])  ) 
        {
            // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
            // Arrêter l'exécution du script
            return header("Location: $_SERVER[HTTP_REFERER]");
        }


        // Mettre en place les contraintes de validation des données provenant du formulaire

        // Commençons par le nom du film
        if ( isset($postClean['name']) ) 
        {
            if ( empty($postClean['name']) ) 
            {
                $errors['name'] = "Le nom du film est obligatoire.";
            }
            else if ( mb_strlen($postClean['name']) > 255 )
            {
                $errors['name'] = "Le nom ne doit pas dépasser 255 caractères.";
            }
        }

        // Commençons par le nom du/des acteurs
        if ( isset($postClean['actors']) ) 
        {
            if ( empty($postClean['actors']) ) 
            {
                $errors['actors'] = "Le nom du/des acteurs du film est obligatoire.";
            }
            else if ( mb_strlen($postClean['actors']) > 255 )
            {
                $errors['actors'] = "Le nom du/des acteurs ne doit pas dépasser 255 caractères.";
            }
        }

        if ( isset($postClean['review']) )
        {
            if ( $postClean['review'] != "" )
            {
                if ( !is_numeric($postClean['review']) ) 
                {
                    $errors['review'] = "La note doit être un nombre.";
                }
                else if( $postClean['review']<'0' || $postClean['review']>'5' )
                {
                    $errors['review'] = "La note doit être comprise entre 0 et 5.";
                }
            }
        }
        
        // S'il y a des erreurs
        if ( count($errors) > 0 ) 
        {
            
            // Sauvegarder les messages d'erreur en session
            $_SESSION['form_errors'] = $errors;
            
            // Sauvegarder les données précedemment envoyées en session
            $_SESSION['old'] = $postClean;
            // var_dump($_SESSION); die();

            // Rediriger l'utilisateur vers la page de laquelle proviennent les informations
            // Arrêter l'exécution du script
            return header("Location: $_SERVER[HTTP_REFERER]");
        }
        
        
        // Dans le cas contraire,
        
        // Arrondir la note à un chiffre après la virgule
        if ( isset($postClean['review']) && $postClean['review'] !== "" ) 
        {
            $reviewRounded = round($postClean['review'], 1);
        }
        
        // Etablir une connexion avec la base de données
        require __DIR__ . "/db/connexion.php";

        // var_dump($reviewRounded); die();



        // Effectuer la requête d'insertion des données en base

        // Préparons la requête avant de l'exécuter afin de nous protéger contre les failles de type "Injection de code SQL".
        $req = $db->prepare("UPDATE film SET name=:name, actors=:actors, review=:review, comment=:comment, updated_at=now() WHERE id=:id");

        // Passons les vraies valeurs
        $req->bindValue(":name",    $postClean['name']);
        $req->bindValue(":actors",  $postClean['actors']);

        if ( isset($reviewRounded) && $reviewRounded==0 )
        {
            $req->bindValue(":review",  $reviewRounded, PDO::PARAM_INT);
        }
        else
        {
            $req->bindValue(":review",  $reviewRounded);
        }

        // $req->bindValue(":review",  $reviewRounded ? strval($reviewRounded) : '');
        $req->bindValue(":comment", $postClean['comment']);
        $req->bindValue(":id", $film['id']);

        // Exécutons la requête.
        $req->execute();

        // Fermons la connexion établie avec la base de données (Non obligatoire).
        $req->closeCursor();
        
        // Créer un message flash de succès
        $_SESSION['success'] = "Le film a été modifié.";
        
        // Rediriger l'utilisateur vers la page d'accueil
        // Arrêter l'exécution du script
        return header("Location: index.php");
    }

    // Générons le jéton de sécurité.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(30));

    
?>
<?php
    // Définition du titre de cette page
    $title = "Modification de ce film"; 

    // Définition de la description de la page
    $description = "Modification des informations de ce film et mise à jour dans la base de données.";

    // Mots clés
    $keywords="Modification, Mise à jour";
?>
<?php include __DIR__ . "/partials/head.php"; ?>

    <?php include __DIR__ . "/partials/nav.php"; ?>

    <!-- Le contenu spécifique à la page -->
    <main class="container my-5">
        <h1 class="text-center my-3 display-5">Modifier ce film</h1>

        
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <?php if(isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) : ?>
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                <?php foreach($_SESSION['form_errors'] as $error) : ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                    <?php endif ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="name">Le nom du film <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo isset($_SESSION['old']['name']) ? htmlspecialchars(stripslashes($_SESSION['old']['name'])) : htmlspecialchars(stripslashes($film['name'])); unset($_SESSION['old']['name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="actors">Le nom du/des acteurs <span class="text-danger">*</span></label>
                            <input type="text" name="actors" id="actors" class="form-control" value="<?php echo isset($_SESSION['old']['actors']) ? htmlspecialchars(stripslashes($_SESSION['old']['actors'])) : htmlspecialchars(stripslashes($film['actors'])); unset($_SESSION['old']['actors']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="review">La note / 5</label>
                            <input type="number" min="0" max="5" step=".1" name="review" id="review" class="form-control" value="<?php echo isset($_SESSION['old']['review']) ? htmlspecialchars($_SESSION['old']['review']) : htmlspecialchars($film['review']); unset($_SESSION['old']['review']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="comment">Laissez un commentaire</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4"><?php echo isset($_SESSION['old']['comment']) ? htmlspecialchars(stripslashes($_SESSION['old']['comment'])) : htmlspecialchars(stripslashes($film['comment'])); unset($_SESSION['old']['comment']); ?></textarea>
                        </div>
                        <div class="mb-3 d-none">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        </div>
                        <div class="mb-3 d-none">
                            <input type="hidden" name="honey_pot" value="">
                            <input type="hidden" name="_method" value="PUT">
                        </div>
                        <div>
                            <input formnovalidate type="submit" class="btn btn-primary" value="Modifier">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . "/partials/footer.php"; ?>

<?php include __DIR__ . "/partials/foot.php"; ?>