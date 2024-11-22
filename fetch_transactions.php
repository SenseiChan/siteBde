<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$transactionsPerPage = 10;
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

// Database connection
$host = 'localhost';
$dbname = 'sae';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $offset = $page * $transactionsPerPage;

    $query = $pdo->prepare("
        SELECT t.Montant_trans AS amount, t.Date_trans AS date,
               COALESCE(
                   e.Nom_event, 
                   p.Nom_prod, 
                   g.Nom_grade,
                   'Grade'
               ) AS description
        FROM transactions t
        LEFT JOIN evenement e ON t.Id_event = e.Id_event
        LEFT JOIN produit p ON t.Id_prod = p.Id_prod
        LEFT JOIN grade g ON t.Id_grade = g.Id_grade
        WHERE t.Id_user = :userId
        AND (
            e.Nom_event LIKE :searchQuery 
            OR p.Nom_prod LIKE :searchQuery 
            OR g.Nom_grade LIKE :searchQuery
            OR 'Grade' LIKE :searchQuery
        )
        ORDER BY t.Date_trans DESC
        LIMIT :limit OFFSET :offset
    ");
    $query->bindValue(':userId', $userId, PDO::PARAM_INT);
    $query->bindValue(':searchQuery', "%$searchQuery%", PDO::PARAM_STR);
    $query->bindValue(':limit', $transactionsPerPage, PDO::PARAM_INT);
    $query->bindValue(':offset', $offset, PDO::PARAM_INT);
    $query->execute();

    $transactions = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'transactions' => $transactions]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
