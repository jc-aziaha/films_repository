<?php
session_start();

    // Mon serveur

    // Si les données arrivent au serveur via la méthode POST
    if ( $_SERVER['REQUEST_METHOD'] === "POST" )
    {

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
        $req = $db->prepare("INSERT INTO film (name, actors, review, comment, created_at, updated_at) VALUES (:name, :actors, :review, :comment, now(), now() ) ");

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

        // Exécutons la requête.
        $req->execute();

        // Fermons la connexion établie avec la base de données (Non obligatoire).
        $req->closeCursor();
        
        // Créer un message flash de succès
        $_SESSION['success'] = "Le film a été ajouté avec succès.";
        
        // Rediriger l'utilisateur vers la page d'accueil
        // Arrêter l'exécution du script
        return header("Location: index.php");
    }

    // Générons le jéton de sécurité.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(30));  
?>
<?php
    // Définition du titre de cette page
    $title = "Nouveau film"; 

    // Définition de la description de la page
    $description = "Ajout d'un nouveau film à la liste";

    // Mots clés
    $keywords="Ajout, Insertion, film";
?>
<?php include __DIR__ . "/partials/head.php"; ?>

    <?php include __DIR__ . "/partials/nav.php"; ?>

    <!-- Le contenu spécifique à la page -->
    <main class="container my-5">
        <h1 class="text-center my-3 display-5">Nouveau film</h1>

        
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
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo isset($_SESSION['old']['name']) ? htmlspecialchars(stripslashes($_SESSION['old']['name'])) : ''; unset($_SESSION['old']['name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="actors">Le nom du/des acteurs <span class="text-danger">*</span></label>
                            <input type="text" name="actors" id="actors" class="form-control" value="<?php echo isset($_SESSION['old']['actors']) ? htmlspecialchars(stripslashes($_SESSION['old']['actors'])) : ''; unset($_SESSION['old']['actors']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="review">La note / 5</label>
                            <input type="number" min="0" max="5" step=".1" name="review" id="review" class="form-control" value="<?php echo isset($_SESSION['old']['review']) ? htmlspecialchars(stripslashes($_SESSION['old']['review'])) : ''; unset($_SESSION['old']['review']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="comment">Laissez un commentaire</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4"><?php echo isset($_SESSION['old']['comment']) ? htmlspecialchars(stripslashes($_SESSION['old']['comment'])) : ''; unset($_SESSION['old']['comment']); ?></textarea>
                        </div>
                        <div class="mb-3 d-none">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        </div>
                        <div class="mb-3 d-none">
                            <input type="hidden" name="honey_pot" value="">
                        </div>
                        <div>
                            <input formnovalidate type="submit" class="btn btn-primary" value="Créer">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . "/partials/footer.php"; ?>

<?php include __DIR__ . "/partials/foot.php"; ?>