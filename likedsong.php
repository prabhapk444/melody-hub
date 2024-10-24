<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked Songs</title>
    <link rel="stylesheet" href="./assets/css/like.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   
</head>
<body>

<div class="container">
    <h1>Your Liked Songs</h1>

    <?php
    session_start(); 
    
    include("db.php");

    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        
        $query = "SELECT song_id FROM liked_songs WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $songIds = [];
        while ($row = $result->fetch_assoc()) {
            $songIds[] = $row['song_id'];
        }

        if (count($songIds) > 0) {
         
            $placeholders = implode(',', array_fill(0, count($songIds), '?'));
            $query = "SELECT * FROM songs WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat("i", count($songIds)), ...$songIds); 
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<ul class="song-list" id="songList">';
                $serialNumber = 1;  
                while ($row = $result->fetch_assoc()) {
                    $songTitle = htmlspecialchars($row['title']);
                    $songAuthor = htmlspecialchars($row['author']);
                    $songPath = 'Admin/' . htmlspecialchars($row['song_path']);
                    $songImagePath = 'Admin/' . htmlspecialchars($row['image_path']);
                    $createdAt = htmlspecialchars($row['created_at']);  

                    echo '
                    <li class="song-item" data-title="' . $songTitle . '" data-author="' . $songAuthor . '" data-image="' . $songImagePath . '" data-id="' . $row['id'] . '">
                        <div class="song-img">
                            <img src="' . $songImagePath . '" alt="Song Image">
                        </div>
                        <div class="song-info">
                            <h2>' . $serialNumber . '. ' . $songTitle . '</h2>
                            <p>' . $songAuthor . '</p>
                        </div>
                        <audio id="audio-' . $row['id'] . '" src="' . $songPath . '" onended="playNextSong(' . $row['id'] . ')"></audio>
                        <div class="song-controls">
                            <button class="play-btn" onclick="playSong(' . $row['id'] . ')"><i class="fas fa-play"></i></button>
                            <a href="' . $songPath . '" download class="download-btn"><i class="fas fa-download"></i></a>
                            <button class="share-btn" onclick="shareSong(\'' . $songTitle . '\', \'' . $songAuthor . '\', \'' . $songPath . '\')"><i class="fas fa-share"></i></button>
                        </div>
                    </li>';
                    
                    $serialNumber++;  
                }
                echo '</ul>';
            } else {
                echo "<p>No songs available.</p>";
            }
        } else {
            echo "<p>No liked songs found.</p>";
        }

        $stmt->close();
    } else {
        echo "<p>Please log in to view your liked songs.</p>";
    }

    $conn->close();
    ?>



</div>
<script>
    function loadPlayer() {
        const playerContainer = document.createElement('div');
        playerContainer.id = 'playerContainer';

        fetch('player.html')
            .then(response => response.text())
            .then(data => {
                playerContainer.innerHTML = data;
                document.body.appendChild(playerContainer);
             
                const script = document.createElement('script');
                script.src = './assets/js/script.js';
                document.body.appendChild(script);
            });
    }
    window.onload = loadPlayer; 
</script>



</body>
</html>
