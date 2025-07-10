<?php
// Connexion à la base de données
try {
    $connexion = new PDO("mysql:host=localhost;dbname=mini_projet", "root", "");
    $connexion->query('SET NAMES utf8');
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Initialisation des variables
$user = null;
$scores = [];
$reponses = [];

// Vérification de l'identifiant
if (isset($_GET['identifiant'])) {
    $identifiant = htmlspecialchars($_GET['identifiant']);

    // Récupérer les informations du participant
    $req_user = "SELECT * FROM participants WHERE id = ?";
    $stmt_user = $connexion->prepare($req_user);
    $stmt_user->execute([$identifiant]);
    $user = $stmt_user->fetch();

    if ($user) {
        // Récupérer l'historique des scores
        $req_scores = "SELECT score, dates FROM scores WHERE nom = ? ORDER BY dates DESC LIMIT 15";
        $stmt_scores = $connexion->prepare($req_scores);
        $stmt_scores->execute([$user['nom']]);
        $scores = $stmt_scores->fetchAll();

        // Si un formulaire de mise à jour de photo a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url_photo'])) {
            $url_photo = trim($_POST['url_photo']);
            if (!empty($url_photo)) {
                $update_photo = "UPDATE participants SET url_photo = :url_photo WHERE id = :id";
                $stmt_update = $connexion->prepare($update_photo);
                $stmt_update->execute(['url_photo' => $url_photo, 'id' => $identifiant]);
                $user['url_photo'] = $url_photo; // Mettre à jour localement
                echo '<div>Photo mise à jour avec succès !</div>';
            } else {
                echo '<div>L\'URL de la photo ne peut pas être vide.</div>';
            }
        }
    }
}

// Récupérer les réponses précédentes
if ($user) {
    $req_reponses = "
        SELECT 
            q.enonce AS question, 
            rp.reponse AS reponse, 
            rp.date_reponse AS date_reponse
        FROM 
            reponses_participants rp
        JOIN 
            questions q ON rp.id_question = q.id
        WHERE 
            rp.id_participant = ?
        ORDER BY 
            rp.date_reponse DESC LIMIT 4";
    
    $stmt_reponses = $connexion->prepare($req_reponses);
    $stmt_reponses->execute([$user['id']]);
    $reponses = $stmt_reponses->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page personnelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Zone d'entête -->
    <div class="container my-5 p-5 bg-info">
        <img src="https://questionnaires.univ-nantes.fr/upload/surveys/737867/images/LOGO_PRINCIPAL_IUT_NANTES_CMJN.png" style="width:25%;float:right;" />
        <h1><strong>Page personnelle</strong></h1>
        <p>Voici vos informations et votre historique.</p>
    </div>

    <!-- Entête commune -->
    <div class="container my-3 p-2 bg-light">
        <form action="page_personnelle.php" method="GET" class="d-flex justify-content-end">
            <input type="text" name="identifiant" class="form-control me-2" placeholder="Votre identifiant" required>
            <button type="submit" class="btn btn-primary">Voir ma page</button>
        </form>
    </div>

    <?php
    if ($user) {
        // Affichage des informations de l'utilisateur
        echo '<div class="container my-3 p-3 bg-light text-center">';
        echo '<h2>Bienvenue, ' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' !</h2>';
        echo '<div class="mb-3">';
        echo '<img src="' . htmlspecialchars(!empty($user['url_photo']) ? $user['url_photo'] : 'https://via.placeholder.com/150') . '" alt="Photo de profil" class="rounded-circle border" style="width:150px;height:150px;">';
        echo '</div>';
        echo '<p><strong>Identifiant</strong>: ' . htmlspecialchars($user['id']) . '</p>';

        // Formulaire de mise à jour de l'URL de la photo
        echo '<form action="page_personnelle.php?identifiant=' . htmlspecialchars($identifiant) . '" method="POST" class="mt-3">';
        echo '<label for="url_photo" class="form-label">Mettez à jour votre photo :</label>';
        echo '<input type="text" name="url_photo" id="url_photo" class="form-control mb-2" placeholder="URL de votre photo" value="' . htmlspecialchars($user['url_photo'] ?? '') . '">';
        echo '<button type="submit" class="btn btn-success">Mettre à jour</button>';
        echo '</form>';
        echo '</div>';

        // Affichage de l'historique des scores
        echo '<div class="container my-3 p-3 bg-light">';
        echo '<h3>Historique de vos scores</h3>';
        if (count($scores) > 0) {
            echo '<ul class="list-group">';
            foreach ($scores as $score) {
                echo '<li class="list-group-item">';
                echo '<strong>Score</strong>: ' . htmlspecialchars($score['score']) . ' - <strong>Date</strong>: ' . htmlspecialchars($score['dates']);
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Aucun score enregistré.</p>';
        }
        echo '</div>';

        // Affichage des réponses précédentes
        echo '<div class="container my-3 p-3 bg-light">';
        echo '<h3>Vos réponses précédentes</h3>';
        if (count($reponses) > 0) {
            echo '<ul class="list-group">';
            foreach ($reponses as $reponse) {
                echo '<li class="list-group-item">';
                echo '<strong>Question :</strong> ' . htmlspecialchars($reponse['question']) . '<br>';
                echo '<strong>Réponse :</strong> ' . htmlspecialchars($reponse['reponse']) . '<br>';
                echo '<strong>Date :</strong> ' . htmlspecialchars($reponse['date_reponse']);
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Aucune réponse enregistrée.</p>';
        }
        echo '</div>';
    } else {
        // Message d'erreur si utilisateur non trouvé
        echo '<div class="container my-3 p-3 bg-warning">';
        echo '<p>Identifiant non trouvé ou non spécifié.</p>';
        echo '</div>';
    }
    ?>

</body>

</html>
