<!DOCTYPE html>
<html lang="fr">
<head>
	<meta content="text/html;charset=UTF-8" http-equiv="Content-Type">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

	<!-- Adaptation automatique sur mobile -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>L'Automaquizz !</title>
</head>
<body>

	<!-- Zone d'entête -->
	<div class="container my-5 p-5 bg-info">
		<img src="https://questionnaires.univ-nantes.fr/upload/surveys/737867/images/LOGO_PRINCIPAL_IUT_NANTES_CMJN.png" style="width:25%;float:right;" />
		<h1><strong>Automaquizz !!!</strong></h1>
		<p>Testez vos connaissances sur les matières de GEII !</p>
	</div>

	<!-- Entête commune -->
	<div class="container my-3 p-2 ">
    	<form action="page_personnelle.php" method="GET" class="d-flex justify-content-end">
        	<input type="text" name="identifiant" class="form-control me-2" placeholder="Votre identifiant" required>
        	<button type="submit" class="btn btn-primary">Voir ma page</button>
    	</form>

	<!-- Formulaire regroupé -->
		<form action="traitement.php" method="POST">
		<div class=' my-3 p-2 bg-warning'><h2><strong>Veuillez renseigner vos informations</strong></h2>
			<label for="nom" class="form-label">Nom du participant :</label>
			<input type="text" id="nom" name="nom" class="form-control" required>
			<label for="prenom" class="form-label">Prénom du participant :</label>
			<input type="text" id="prenom" name="prenom" class="form-control" required>
		</div>
	</div>

		<div class="container my-3 p-2">
			<?php
			try {
				$connexion = new PDO("mysql:host=localhost;dbname=mini_projet", "root", "");
				$connexion->query('SET NAMES utf8');
			} catch (PDOException $e) {
				print "Erreur !: " . $e->getMessage() . "<br/>";
				die();
			}

			// Récupération des questions
			$requetes_questions = "SELECT matiere, enonce FROM questions";
			$reponse_questions = $connexion->query($requetes_questions);

			// Récupération des réponses
			$requetes_answers = "SELECT id_question, reponse FROM reponses";
			$reponse_answers = $connexion->query($requetes_answers);

			// Tableau pour les questions
			$matieres = [];
			$enonces = [];
			$compteur_questions = 0;
			while ($donnees = $reponse_questions->fetch()) {
				$matieres[$compteur_questions] = $donnees['matiere'];
				$enonces[$compteur_questions] = $donnees['enonce'];
				$compteur_questions++;
			}
			$reponse_questions->closeCursor();

			// Tableau pour les réponses
			$id_questions = [];
			$answers = [];
			$compteur_reponses = 0;
			while ($donnees = $reponse_answers->fetch()) {
				$id_questions[$compteur_reponses] = $donnees['id_question'];
				$answers[$compteur_reponses] = $donnees['reponse'];
				$compteur_reponses++;
			}

			// Affichage des questions et des réponses
			$compteur_questions = 1;
			foreach ($matieres as $matiere) {
				echo "<div class='container my-4 p-2 bg-warning'><h2><strong>" . $matiere . "</strong></h2></div>";
				echo "<div class='container my-4 p-2' style='border:1px black solid;'><p>" . $enonces[$compteur_questions-1] . "</p></div>"; 
				for ($i = 0; $i < count($answers); $i++) {
					if ($id_questions[$i] == $compteur_questions) {
						echo "<div class='form-check'>";
						echo "<input type='radio' class='form-check-input' name='reponse".$compteur_questions."' value='" . ($i) . "' required> " . $answers[$i];
						echo "</div>";
					}
				}
				$compteur_questions++;
			}
			?>
		</div>

		<!-- Bouton pour soumettre le formulaire -->
		<div class="text-center">
			<button type="submit" class="btn btn-danger">Valider vos réponses</button>
		</div>
	</form>

</body>
</html>
