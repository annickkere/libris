<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the database
$connection = mysqli_connect("localhost", "root", "", "libris");
// If connection fails, stop execution and show error
if (!$connection) die("Erreur de connexion : " . mysqli_connect_error());

// Add a book if an ID is sent via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_livre'])) {
    $id_livre = intval($_POST['id_livre']);

    // Check if the book exists
    $stmt = mysqli_prepare($connection, "SELECT id FROM livres WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_livre);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        // Borrow and return dates
        $date_emprunt = date("Y-m-d");
        $date_retour = date("Y-m-d", strtotime("+7 days"));

        // Check if the book is already in the wishlist
        $check = mysqli_prepare($connection, "SELECT * FROM liste_lecture WHERE id_livre = ?");
        mysqli_stmt_bind_param($check, "i", $id_livre);
        mysqli_stmt_execute($check);
        $result_check = mysqli_stmt_get_result($check);
        if (mysqli_num_rows($result_check) == 0) {
            // Insert the book into the wishlist
            $insert = mysqli_prepare($connection, "INSERT INTO liste_lecture (id_livre, date_emprunt, date_retour) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($insert, "iss", $id_livre, $date_emprunt, $date_retour);
            mysqli_stmt_execute($insert);
        }
        mysqli_stmt_close($check);
    }

    mysqli_stmt_close($stmt);
}

// Delete a book from the wishlist if requested
if (isset($_GET['supprimer'])) {
    $id_supprimer = intval($_GET['supprimer']);
    $delete = mysqli_prepare($connection, "DELETE FROM liste_lecture WHERE id_livre = ?");
    mysqli_stmt_bind_param($delete, "i", $id_supprimer);
    mysqli_stmt_execute($delete);
    mysqli_stmt_close($delete);
}

// Display the wishlist
$sql = "
    SELECT l.id, l.titre, l.auteur, l.image, ll.date_emprunt, ll.date_retour
    FROM liste_lecture ll
    JOIN livres l ON ll.id_livre = l.id
    ORDER BY ll.date_emprunt DESC
";

$result = mysqli_query($connection, $sql);
$contenu_wishlist = "";
$contenu_wishlist .= "<a href='javascript:history.back()' class='btn-retour'>⬅ Retour</a>";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $contenu_wishlist .= "<div class='livre'>";
        $contenu_wishlist .= "<img src='../images/" . htmlspecialchars($row['image']) . "' alt='Couverture du livre'>";
        $contenu_wishlist .= "<h2>" . htmlspecialchars($row['titre']) . "</h2>";
        $contenu_wishlist .= "<p><strong>Auteur :</strong> " . htmlspecialchars($row['auteur']) . "</p>";
        $contenu_wishlist .= "<p><strong>Emprunté le :</strong> " . htmlspecialchars($row['date_emprunt']) . "</p>";
        $contenu_wishlist .= "<p><strong>Retour prévu :</strong> " . htmlspecialchars($row['date_retour']) . "</p>";
        $contenu_wishlist .= "<form method='GET' action='wishlist.php' onsubmit='return confirm(\"Supprimer ce livre ?\");'>";
        $contenu_wishlist .= "<input type='hidden' name='supprimer' value='" . $row['id'] . "'>";
        $contenu_wishlist .= "<button type='submit class='btn'>🗑 Supprimer</button>";
        $contenu_wishlist .= "</form>";
        $contenu_wishlist .= "</div><br>";
    }
} else {
    $contenu_wishlist = "<p>Aucun livre dans votre liste de lecture pour le moment.</p>";
}

mysqli_close($connection);

// Inject content into the static HTML template
$html = file_get_contents("../pages/wishlist.html");
$html = str_replace("<!--WISHLIST-->", $contenu_wishlist, $html);
echo $html;
?>
