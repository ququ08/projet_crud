<?php
$serveurname = "localhost";
$username ="root";
$password ="";

//connexion a ma base
$bdd = new PDO("mysql:host=$serveurname;dbname=crud",$username,$password);
$bdd ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//variable du formulaire
$nomform ='';
$contactform = '';
$editMode = false;
$idEdit = null;
$message = '';

//mode edition
if(isset($_POST['edit_id'])){
    $editMode = true;
    $idEdit = intval($_POST['edit_id']);
    $stmt = $bdd->prepare("SELECT * FROM elements WHERE id = ?");
    $stmt->execute([$idEdit]);
    $elementEdit = $stmt->fetch();

    if($elementEdit){
        $nomform = htmlspecialchars($elementEdit['nom']);
        $contactform = htmlspecialchars($elementEdit['contact']);
    }
}
//ajout d'element(create)
//je verifie si mon formulaire est bien envoyer et je les nettoie pour eviter les piratage aek htmlspecialchars
if (isset($_POST['elementName'], $_POST['elementNum']) && $_POST['elementName'] !== '' && (!isset($_POST['modif_id']) || $_POST['modif_id'] === '')) {
    $nom = htmlspecialchars($_POST['elementName']);
    $contact = htmlspecialchars($_POST['elementNum']);
    $bdd->prepare("INSERT INTO elements(nom, contact) VALUES(?, ?)")->execute([$nom, $contact]);//j'insere mes donnée dans ma table
    $message =  "Elément ajouté avec succès";
}

//update
if (isset($_POST['elementName'], $_POST['elementNum'], $_POST['modif_id']) && $_POST['elementName'] !== '' && $_POST['modif_id'] !== '') {
    $nom = htmlspecialchars($_POST['elementName']);
    $contact = htmlspecialchars($_POST['elementNum']);
    $id = intval($_POST['modif_id']);
    $bdd->prepare("UPDATE elements set nom = ?, contact = ? WHERE id = ?")->execute([$nom, $contact, $id]);
    $message = "élément modifié";
}

//supprimer l'élément de la base(delete)
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $bdd->prepare("DELETE FROM elements WHERE id = ?")->execute([$id]);
    $message = "élément supprimer";
}
//affichage des elements(read)
$elements = $bdd->query("SELECT * FROM elements ORDER BY id ASC");//je recupere les donnée entrée et je fais un trie du plus ancien nom au plus recent ajouter

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>crud projet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        body{
            background-color: lavender;
        } 
        
    </style>
</head>
<body>
    <section>
        <div class="container">
            <?php if($message): ?>
                <div class="alert alert-success text-center fw-bold">
                    <?= $message ?>
                </div>
                <?php endif; ?>
            <div class="text-center">
                <h1 class="fw-bold mx-auto text-uppercase">mon gestionnaire de contact</h1>
                <p>Ajouter, supprimer, et modifier vos éléments facilement</p>
            </div>
        </div>
    </section>
    <section>
       <div class="container py-5">
           <div class="row justify-content-center">
               <div class="col-md-10">
                  <button class="btn btn-secondary mb-3" onclick="document.getElementById('formulaireCollapse').classList.toggle('show')" >Afficher le formulaire</button>
                  <div class="collapse <?= $editMode ? 'show' : ''?>" id="formulaireCollapse">
                      <div class="card p-4 rounded">
                          <form method="POST" action="crud.php" >
                              <input type="hidden" name="modif_id" value="<?= $editMode ? $idEdit : '' ?>">
                              <label for="elementName" class="form-label fw-bold">Ajouter un nouvel élément</label>
                              <input type="text" name="elementName" value="<?= $nomform?>" id="elementName" class="form-control mb-3" placeholder="Nom de l'élément...">
                              <input type="text" name="elementNum" value="<?= $contactform?>" id="elementNum" class="form-control mb-3" placeholder="votre numéro de téléphone">
                              <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-uppercase">
                                   <?=$editMode ? 'mettre à jour' : 'Ajouter'?>
                                </button>
                              </div>
                          </form>
                      </div>
                  </div>
               </div>
           </div>
       </div>
    </section>
    <section>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card p-4">
                        <h3>Liste des éléments</h3>
                        <?php
                        if($elements->rowCount() === 0)://si ma base est vide un message est affiché
                        ?>
                        <div class="empty-state" id="message">
                            <div class="empty-title">
                                <h6 class="text-center">aucun élément pour le moment</h6>
                            </div>
                            <div class="empty-text">
                               <p class="text-center">ajouter des éléments</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="list">
                        <ul class="list-group list-group-flush" id="list">
                            <?php foreach($elements as $element): ?>
                               <li class='list-group-item d-flex justify-content-between align-items-start'>
                                <div>
                                  <strong><?= htmlspecialchars($element['nom'])?> </strong><br>
                                  <small>Contact : <?= htmlspecialchars($element['contact']) ?></small>
                                </div>
                                  <div class='d-flex gap-2'>
                                     <a href="?delete=<?= $element['id']?> "class='btn btn-sm btn-danger text-uppercase text-decoration-none'>supprimer</a>
                                  <form method='POST' class='d-flex'>
                                    <input type="hidden" name="edit_id" value="<?= $element['id']?>">
                                       <button type='submit' class='btn btn-sm btn-warning text-uppercase'>Modifier</button>
                                 </form>
                             </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section></section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" ></script>
    <script>
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.style.display = 'none';
  }, 4000);
</script>    
</body>
</html>
