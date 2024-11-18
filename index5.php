<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'songs';
$user = 'taylor';
$pass = '1655897';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle book search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT id, title, artist, release_year FROM my_songs WHERE title LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['title']) && isset($_POST['artist']) && isset($_POST['release_year'])) {
        // Insert new entry
        $title = htmlspecialchars($_POST['title']);
        $artist = htmlspecialchars($_POST['artist']);
        $release_year = htmlspecialchars($_POST['release_year']);
        
        $insert_sql = 'INSERT INTO my_songs (title, artist, release_year) VALUES (:title, :artist, :release_year)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['title' => $title, 'artist' => $artist, 'release_year' => $release_year]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM my_songs WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    }
}

// Get all books for main table
$sql = 'SELECT id, title, artist, release_year FROM my_songs';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Song Curator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">The Playlist Maker</h1>
        <p class="hero-subtitle">"We let you make your own playlist! But that doesnt mean your music taste is good!"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for your saved songs</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Title:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Artist</th>
                                    <th>Release Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['artist']); ?></td>
                                    <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <input type="submit" value="Remove">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>This song doesn't exist in your playlist</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Songs in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Release Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['artist']); ?></td>
                    <td><?php echo htmlspecialchars($row['release_year']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Remove">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Add Songs!</h2>
        <form action="index5.php" method="post">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            <br><br>
            <label for="artist">Artist:</label>
            <input type="text" id="artist" name="artist" required>
            <br><br>
            <label for="release_year">Release Date:</label>
            <input type="text" id="release_year" name="release_year" required>
            <br><br>
            <input type="submit" value="Add to playlist">
        </form>
    </div>
</body>
</html>