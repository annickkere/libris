<?php
// Connect to the database
$connection = mysqli_connect("localhost", "root", "", "libris");
if (!$connection) {
    // If connection fails, stop execution and show error
    die("Échec de la connexion à la base de données : " . mysqli_connect_error());
}

// Get the search term from the URL (GET parameter)
$titre = isset($_GET['search-box']) ? trim($_GET['search-box']) : '';

// Initialize the variable that will contain the search results
$contenu_resultats = "";
$contenu_resultats = "<a href='../index.html' class='btn-retour'>⬅Retour</a>";

if (!empty($titre)) {
    // Prepare the SQL statement to search for books with titles similar to the search term
    $stmt = mysqli_prepare($connection, "SELECT id, titre, auteur, image FROM livres WHERE titre LIKE ?");
    // Use wildcards for partial match
    $recherche = "%" . $titre . "%";
    // Bind the search term as a string
    mysqli_stmt_bind_param($stmt, "s", $recherche);
    // Execute the query
    mysqli_stmt_execute($stmt);
    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    // If results were found, format and display them
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $contenu_resultats .= "<div class='resultat'>";
            $contenu_resultats .= "<h2>Titre : " . htmlspecialchars($row['titre']) . "</h2>";
            $contenu_resultats .= "<img src='../images/" . htmlspecialchars($row['image']) . "' alt='Image du livre'/>";
            $contenu_resultats .= "<h2>Auteur : " . htmlspecialchars($row['auteur']) . "</h2>";
            $contenu_resultats .= "<a href='details.php?id=" . urlencode($row['id']) . "' class='btn'>Details</a>";
            $contenu_resultats .= "</div><hr>";
        }
    // If no results were found, show a message
    } else {
        $contenu_resultats = "<p>Aucun livre trouvé pour le titre : <strong>" . htmlspecialchars($titre) . "</strong>.</p>";
    }
    // Close the prepared statement
    mysqli_stmt_close($stmt);
} else {
    // If search term is empty, prompt the user
    $contenu_resultats = "<p>Veuillez entrer un titre.</p>";
}
// Close the database connection
mysqli_close($connection);

// Load the HTML template file (adjust path as needed)
$html = file_get_contents("../pages/results.html");

// Replace the placeholder with the actual results "<!--RESULTATS-->"
$html = str_replace("<!--RESULTATS-->", $contenu_resultats, $html);

// Output the final HTML
echo $html;
?>
