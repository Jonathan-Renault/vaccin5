<?php
include('inc/pdo.php');
include('inc/fonctions.php');
include('dashboard_vaccin/inc/request.php');
$title = 'Ajout de vaccin';
$error = array();

// si la personne est connecté
if(is_logged()){

// requete on selectionne tout de la table v5_users de la personne connecté selon son id
    $id = $_SESSION['user']['id'];
    $sql = " SELECT *
             FROM v5_users
             WHERE id = $id";
    $query =$pdo ->prepare($sql);
    $query -> execute();
    $profil= $query -> fetch();

//requete pour avoir tous les vaccins de la table users
    if(!empty($profil)) {

      $sql = "SELECT *
              FROM v5_vaccin";
      $query = $pdo -> prepare($sql);
      $query -> execute();
      $listVaccins= $query -> fetchAll();

// Requete des vaccins fait par l'user
      $sql = "SELECT * FROM v5_vaccin AS v
              LEFT JOIN v5_relation AS pivot
              ON v.id = pivot.vaccin_id
              WHERE pivot.user_id = $id";
      $query = $pdo -> prepare($sql);
      $query -> execute();
      $vaccinfaits= $query -> fetchall();

//tableau nourrit par la requete précedente
      $tableauId = array();
      foreach ($vaccinfaits as $v) {
            $tableauId[] = $v['vaccin_id'];

      }

    }else {
        header("Location: 404.php");
    }


// ajout d'un vaccin
    if (!empty($_POST['submit_aj'])) {

      $date_injection = clean('date_injection');
      if (!empty($date_injection)) {
        $vaccin_id = clean('idvaccin');

        $validite = '+'.$listVaccins['validite'] . ' year';
        $rappel = date("Y-m-d", strtotime($validite, strtotime($date_injection)));

        $sql = "INSERT INTO v5_relation(user_id, vaccin_id, date_injection, validite, created_at, ) VALUES (:user_id , :vaccin_id , :date_injection, :validite, NOW() )";
        $query= $pdo -> prepare($sql) ;
        $query-> bindvalue(':validite' , $validite , PDO::PARAM_STR );
        $query-> bindvalue(':vaccin_id' , $vaccin_id , PDO::PARAM_STR );
        $query-> bindvalue(':user_id' , $id , PDO::PARAM_STR );
        $query-> bindvalue(':date_injection' , $date_injection , PDO::PARAM_STR );
        $query-> execute();
        header('Location: modif_vaccin.php');
      }else {
        $error['date_injection'] = 'Veuillez entrer une date';
      }
}

// retrait d'un vaccin
if (!empty($_POST['submit_ret'])) {

  $vaccin_id = clean('idvaccin');

  $sql = "DELETE FROM `v5_relation` WHERE user_id = :user_id AND vaccin_id = :vaccin_id";

  $query= $pdo -> prepare($sql) ;
  $query-> bindvalue(':user_id' , $id , PDO::PARAM_STR );
  $query-> bindvalue(':vaccin_id' , $vaccin_id , PDO::PARAM_STR );
  $query-> execute();
  header('Location: modif_vaccin.php');
}

}

else {
    header("Location: 404.php");
}

include('inc/header.php');
?>

<!-- Il y a une id class container autour du body  -->

        <div class="modif_vaccin">
              <h2>Ajouts de vaccins</h2>
              <table class="form table_vaccin">
                <tr>
                  <th class="parent"><p class="enfant">Nom</p></th>
                  <th class="parent"><p class="enfant">Obligatoire / Recommandé</p></th>
                  <th class="parent"><p class="enfant">Effectué</p></th>
                  <th class="parent"><p class="enfant">Fréquence d'injections</p></th>
                  <th class="parent"><p class="enfant">Ajouter</p></th>
                </tr>
                <?php foreach ($listVaccins as $listVaccin) {
                    echo '<tr><td class="parent"><p class="enfant">'.$listVaccin['nom'].'</p></td>';

                    if ($listVaccin['obligatoire'] == 1) {
                       echo '<td class="parent"><p class="enfant">Obligatoire</p></td>';
                    } else {
                      echo '<td class="parent"><p class="enfant">Recommandé</p></td>';
                    }

                    if(in_array($listVaccin['id'],$tableauId)) {
                      echo '<td class="parent"><img class="enfant" src="assets/image/icon_fait.svg" alt="Fait"></td>';
                    } else {
                      echo '<td class="parent"><img class="enfant" src="assets/image/icon_non_fait.svg" alt="Fait"></td>';
                    }

                    echo '</td><td class="parent"><p class="enfant">'.$listVaccin['frequences_injections'].'</p></td>' ;
                    echo '<td>';


                    if(!in_array($listVaccin['id'],$tableauId)) {?>

                    <form action="" method="post">
                      <input type="date" name="date_injection" value="">
                      <input type="hidden" name="idvaccin" value="<?= $listVaccin['id']; ?>">
                      <input class="button" type="submit" name="submit_aj" value="Ajouter">
                      <span><?php spanError($error, '$date_injection') ?></span>
                    </form><?php echo '</td></tr>' ;

                    } elseif (in_array($listVaccin['id'],$tableauId)) {?>
                      <form action="" method="post">
                      <input type="hidden" name="idvaccin" value="<?= $listVaccin['id']; ?>">
                      <input class="button red" type="submit" name="submit_ret" value="Retirer"></form><?php echo '</td></tr>' ;
                      }
                }


                  ?>
              </table>

            <div class="button_div">
              <a class="button" href="profil.php">Retour</a>
            </div>
        </div>


<?php echo $listVaccins['validite'] ;include('inc/footer.php');
