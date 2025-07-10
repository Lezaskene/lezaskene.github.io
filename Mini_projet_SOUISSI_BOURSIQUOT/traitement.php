<html>
<head>
    <meta content="text/html;charset=UTF-8" http-equiv="Content-Type">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Adaptation automatique sur mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <title>L'Automaquizz !</title>
    <style>
        /* Style pour les réponses correctes */
        .correct {
            background-color: #d4edda;
            color: #155724;
        }

        /* Style pour les réponses incorrectes */
        .incorrect {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Style pour le Top 10 des scores */
        .top-scores {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            height: 80%; /* Ajuste la hauteur */
        }

        .top-scores h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .top-scores ul {
            list-style: none;
            padding: 0;
        }

        .top-scores li {
            margin: 10px 0;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <!-- Zone d'entête -->
    <div class="container my-5 p-5 bg-info">
        <img src="https://questionnaires.univ-nantes.fr/upload/surveys/737867/images/LOGO_PRINCIPAL_IUT_NANTES_CMJN.png" style="width:25%;float:right;" />
        <h1><strong>Automaquizz !!!</strong></h1>
        <p>Découvrez vos résultats !</p>
    </div>
    
    <!-- Entête commune -->
    <div class="container my-3 p-2">
        <form action="page_personnelle.php" method="GET" class="d-flex justify-content-end">
            <input type="text" name="identifiant" class="form-control me-2" placeholder="Votre identifiant" required>
            <button type="submit" class="btn btn-primary">Voir ma page</button>
        </form>
    </div>

    <div class="container my-3 p-5">
        <?php
        try {
            $connexion = new PDO("mysql:host=localhost;dbname=mini_projet", "root", "");
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }

        // Récupération du nombre de réponses dans la BDD
        $req_nb_rep = "SELECT COUNT(*) AS nb_rep FROM reponses";
        $rep_nb_rep = $connexion->query($req_nb_rep);
        $donnees = $rep_nb_rep->fetch();
        $nb_rep = $donnees['nb_rep'];
        $rep_nb_rep->closeCursor();

        // Récupération du nombre de questions dans la BDD
        $req_nb_que = "SELECT COUNT(*) AS nb_que FROM questions";
        $rep_nb_que = $connexion->query($req_nb_que);
        $donnees = $rep_nb_que->fetch();
        $nb_que = $donnees['nb_que'];
        $rep_nb_que->closeCursor();

        // Récupération du score associé à chaque réponse
        $req_scores = "SELECT points FROM reponses";
        $rep_scores = $connexion->query($req_scores);
        $compteur_reponses = 0;
        while ($donnees = $rep_scores->fetch()) {
            $scores[$compteur_reponses] = $donnees['points'];
            $compteur_reponses++;
        }
        $rep_scores->closeCursor();

        // Partie pour gérer les participants
        if (isset($_POST['nom'])) {
            $nom = trim($_POST['nom']);
        } else {
            $nom = '';
        }

        if (isset($_POST['prenom'])) {
            $prenom = trim($_POST['prenom']);
        } else {
            $prenom = '';
        }

        if (!empty($nom) && !empty($prenom)) {
            // Vérifier si le participant existe déjà
            $query_verif = "SELECT id FROM participants WHERE nom = :nom AND prenom = :prenom";
            $stmt_verif = $connexion->prepare($query_verif);
            $stmt_verif->execute(['nom' => $nom, 'prenom' => $prenom]);

            if ($row = $stmt_verif->fetch(PDO::FETCH_ASSOC)) {
                // Si l'utilisateur existe déjà, récupérer son ID
                $participant_id = $row['id'];
                echo "<p>Le participant <strong>{$nom} {$prenom}</strong> existe déjà.</p>";
            } else {
                // Sinon, insérer un nouveau participant et récupérer son ID
                $query_insert = "INSERT INTO participants (nom, prenom) VALUES (:nom, :prenom)";
                $stmt_insert = $connexion->prepare($query_insert);
                $stmt_insert->execute(['nom' => $nom, 'prenom' => $prenom]);

                $participant_id = $connexion->lastInsertId(); // Récupère l'ID du dernier enregistrement inséré
                echo "<p>Le participant <strong>{$nom} {$prenom}</strong> a été ajouté avec succès.</p>";
            }

            // Afficher l'ID du participant
            echo "<p>ID du participant : <strong>{$participant_id}</strong></p>";
            echo "Votre score a bien été sauvegardé</p>";
        } else {
            echo "<p>Veuillez renseigner le nom et le prénom.</p>";
        }

        // Calcul du score de la personne pour chaque réponse sélectionnée
        $score_final = 0; // Initialisation du score final
        $reponses_final = array(); // Tableau pour stocker les réponses choisies

        echo "<div class='flex-container'>";
        echo "<div class='main-content'>"; // Section pour les résultats

        echo "<h2>Vos résultats</h2>";
        echo "<ul>";

        for ($i = 0; $i < $nb_que; $i++) {
            $champ = 'reponse' . ($i + 1); // Nom du champ POST correspondant à la question
            if (isset($_POST[$champ])) { 
                $id_reponse_choisie = $_POST[$champ] - 1; // Soustrait 1 pour ajuster l'index
                $reponses_final[$i] = $id_reponse_choisie;

                $requete = $connexion->prepare("
                    SELECT 
                        reponses.reponse AS texte_reponse, 
                        reponses.points AS score_reponse, 
                        questions.enonce AS enonce_question,
                        questions.id AS id_question
                    FROM reponses
                    JOIN questions ON reponses.id_question = questions.id
                    WHERE reponses.id = :id_reponse
                ");
                $requete->execute([':id_reponse' => $id_reponse_choisie + 2]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);

                if ($resultat) {
                    $texte_reponse = $resultat['texte_reponse']; 
                    $score_reponse = $resultat['score_reponse']; 
                    $enonce_question = $resultat['enonce_question']; 
                    $id_question = $resultat['id_question']; 

                    // Calcul du score final
                    $score_final += $score_reponse;

                    // Affichage structuré des résultats
                    echo "<li>";
                    echo "<p><strong>Question " . ($i + 1) . " :</strong> " . htmlspecialchars($enonce_question) . "</p>";

                    // Affichage de la réponse choisie par l'utilisateur avec une couleur
                    if ($score_reponse == 1) {
                        echo "<p class='correct'><strong>Votre réponse :</strong> " . htmlspecialchars($texte_reponse) . "</p>";
                    } else {
                        echo "<p class='incorrect'><strong>Votre réponse :</strong> " . htmlspecialchars($texte_reponse) . "</p>";

                        // Récupération et affichage de la bonne réponse
                        $requete_bonne_reponse = $connexion->prepare("
                            SELECT reponse 
                            FROM reponses 
                            WHERE id_question = :id_question AND points = 1
                        ");
                        $requete_bonne_reponse->execute([':id_question' => $id_question]);
                        $bonne_reponse = $requete_bonne_reponse->fetchColumn();

                        echo "<p class='correct'><strong>Bonne réponse :</strong> " . htmlspecialchars($bonne_reponse) . "</p>";
                    }
                    echo "</li>";
                }
            }
        }

        echo "</ul>";

        // Affichage du score final
        echo "<p><strong>Votre score final est : " . $score_final . " / " . $nb_que . "</strong></p>";

        // Message personnalisé en fonction du score
        if ($score_final == $nb_que) {
            echo "<p>Parfait !</p>";
        } elseif ($score_final > ($nb_que / 2)) {
            echo "<p>Au-dessus de la moyenne !</p>";
        } elseif ($score_final == 0) {
            echo "<p>Tout faux !</p>";
        } elseif ($score_final < ($nb_que / 2)) {
            echo "<p>En-dessous de la moyenne !</p>";
        } else {
            echo "<p>Il y a encore du travail à faire.</p>";
        }

        echo "</div>"; // Fin de la section principale
        echo "</div>"; // Fin du conteneur global

        // Partie Formulaire avec les Noms
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = htmlspecialchars(trim($_POST['nom']));
            $prenom = htmlspecialchars(trim($_POST['prenom']));
            $sql_insertion = "INSERT INTO scores (nom, prenom, score, dates) VALUES ('$nom', '$prenom', '$score_final', NOW())";
            $sql_insertion = $connexion->exec($sql_insertion);

            $req_participant = "SELECT id FROM participants WHERE nom = ? AND prenom = ?";
            $stmt_participant = $connexion->prepare($req_participant);
            $stmt_participant->execute([$nom, $prenom]);
            $participant = $stmt_participant->fetch();

            if ($participant) {
                $id_participant = $participant['id'];

                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'reponse') === 0) {
                        $num_question = intval(substr($key, 7));
                        $req_insert = "
                            INSERT INTO reponses_participants (id_participant, id_question, reponse) 
                            VALUES (:id_participant, :id_question, :reponse)";
                        $stmt_insert = $connexion->prepare($req_insert);
                        $stmt_insert->execute([
                            'id_participant' => $id_participant,
                            'id_question' => $num_question,
                            'reponse' => htmlspecialchars($value)
                        ]);
                    }
                }
            }
        }

        // Partie pour le Top 10
        echo "<div class='top-scores bg-light p-3 rounded shadow'>";
        echo "<h3 class='text-center'>Top 10 des Scores</h3>";
        echo "<ul class='list-group'>";

        $sql_top10 = "SELECT nom, prenom, score, dates FROM scores ORDER BY score DESC, dates ASC LIMIT 10";
        $result_top10 = $connexion->query($sql_top10);

        if ($result_top10) {
            while ($row = $result_top10->fetch()) {
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <div>
                            <span>{$row['nom']} {$row['prenom']}</span>
                            <small class='text-muted'>le " . date('d/m/Y à H:i', strtotime($row['dates'])) . "</small>
                        </div>
                        <span class='badge bg-success rounded-pill'>{$row['score']} pts</span>
                      </li>";
            }
        } else {
            echo "<li>Erreur lors de la récupération des scores.</li>";
        }

        echo "</ul>";
        echo "</div>"; // Fin du Top 10
        echo "</div>"; 

        $connexion = null; // Déconnexion
        ?>
    </div>

</body>
</html>
