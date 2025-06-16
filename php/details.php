<?php
// Connect to the database
$connection = mysqli_connect("localhost", "root", "", "libris");
// If connection fails, stop execution and show error
if (!$connection) die("Connexion échouée : " . mysqli_connect_error());

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$contenu_details = "";

if ($id > 0) {
    // Prepare and execute the query to fetch book details by ID
    $stmt = mysqli_prepare($connection, "SELECT titre, auteur, description, image FROM livres WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // If the book is found, display its details
    if ($row = mysqli_fetch_assoc($result)) {
        $contenu_details .= "<a href='javascript:history.back()' class='btn-retour'>⬅ Retour</a>";
        $contenu_details .= "<h1>" . htmlspecialchars($row['titre']) . "</h1>";
        $contenu_details .= "<img src='../images/" . htmlspecialchars($row['image']) . "' alt='Image du livre'>";
        $contenu_details .= "<h3>Auteur : " . htmlspecialchars($row['auteur']) . "</h3>";
        $contenu_details .= "<p>" . nl2br(htmlspecialchars($row['description'])) . "</p>";
        $contenu_details .= "<form action='../php/wishlist.php' method='post'>
                            <input type='hidden' name='id_livre' value='" . htmlspecialchars($id) . "'>
                            <button type='submit' class='btn'>Ajouter à ma liste de lecture</button>
                            </form>";
    } else {
        // Book not found in database
        $contenu_details .= "<p>Livre introuvable.</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    // Invalid or missing ID
    $contenu_details .= "<p>ID invalide.</p>";
}

mysqli_close($connection);

// Load the static HTML template
$html = file_get_contents("../pages/details.html");

// Inject book details into the HTML
$html = str_replace("<!--DETAILS-->", $contenu_details, $html);

// Display the complete page
echo $html;
?>
