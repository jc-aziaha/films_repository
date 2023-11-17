<?php
session_start();

    // Etablir une connexion avec la base de données
    require __DIR__ . "/db/connexion.php";

    // Préparer la requête de sélection des données
    $req = $db->prepare("SELECT * FROM film ORDER BY created_at DESC");

    // L'exécuter
    $req->execute();

    // Récupérer les données
    $films = $req->fetchAll();
    
    // Fermer le curseur (Optionnel)
    $req->closeCursor();

    // Générons le jéton de sécurité.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(30));
?>
<?php include __DIR__ . "/partials/head.php"; ?>

    <?php include __DIR__ . "/partials/nav.php"; ?>

    <!-- Le contenu spécifique à la page -->
    <main class="container">
        <h1 class="text-center my-3 display-5">Liste des films</h1>

        <div class="d-flex justify-content-end align-items-center my-3">
            <a href="create.php" class="btn btn-primary">Nouveau film</a>
        </div>

        <?php if(count($films) > 0) : ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-6 mx-auto">

                        <?php if(isset($_SESSION['success']) && !empty($_SESSION['success'])) : ?>
                            <div class="text-center alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['success']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif ?>

                        <?php foreach($films as $film) : ?>
                            <div class="card shadow my-3">
                                <div class="card-body">
                                    <p class="card-text"><strong>Nom du film</strong>: <?php echo htmlspecialchars(stripslashes($film['name'])); ?></p>
                                    <p class="card-text"><strong>Nom du/des acteurs</strong>: <?php echo htmlspecialchars(stripslashes($film['actors'])); ?></p>
                                    <hr>
                                    <a data-bs-toggle="modal" data-bs-target="#modal<?php echo htmlspecialchars($film['id']); ?>" href="#" class="text-dark mx-2"><i class="fa-solid fa-eye"></i></a>
                                    <a href="edit.php?film_id=<?php echo htmlspecialchars($film['id']); ?>" class="text-secondary mx-2"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a onclick="event.preventDefault(); return confirm('Continuer ?') && document.querySelector('#form_delete_film_<?php echo htmlspecialchars($film['id']); ?>').submit();" title="Supprimer" href="#" class="text-danger m-2"><i class="fa-solid fa-trash-can"></i></a>
                                    <form method="POST" id="form_delete_film_<?php echo $film['id']; ?>" action="delete.php">
                                        <input type="hidden" name="film_id" value="<?php echo htmlspecialchars($film['id']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="honey_pot" value="">
                                    </form>
                                </div>
                            </div>

                            <!-- Modal -->
                            <div class="modal fade" id="modal<?php echo htmlspecialchars($film['id']); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5"><?php echo htmlspecialchars(stripslashes($film['name'])); ?></h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>La note : <?php echo isset($film['review']) && $film['review'] !== "" ? $film['review'] : "Non renseignée"; ?></p>
                                            <p class="text-overflow-ellipsis overflow-hidden">Le commentaire : <?php echo isset($film['comment']) && $film['comment'] !== "" ? nl2br(htmlspecialchars(stripslashes($film['comment']))) : "Non renseigné"; ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <p class="text-center mt-5">Aucun film ajouté à la liste pour l'instant</p>
        <?php endif ?>
    </main>

    <?php include __DIR__ . "/partials/footer.php"; ?>

<?php include __DIR__ . "/partials/foot.php"; ?>