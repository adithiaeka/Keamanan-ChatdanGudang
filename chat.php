<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Logout jika diminta
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chat Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="text-center">Simple Chat Room</h1>
            <a href="?logout=true" class="btn btn-danger">Logout</a>
        </div>
        <div class="card">
            <div class="card-body" id="chat-box" style="height: 300px; overflow-y: scroll;">
                <!-- Pesan akan ditampilkan di sini -->
            </div>
        </div>
        <form id="chat-form" class="mt-3">
            <div class="input-group">
                <input type="text" id="username" class="form-control" placeholder="Username" value="<?php echo $_SESSION['username']; ?>" readonly required>
                <input type="text" id="message" class="form-control" placeholder="Message" required>
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Fungsi untuk mengambil pesan secara real-time
        function fetchMessages() {
            $.ajax({
                url: 'fetch_messages.php',
                method: 'GET',
                success: function(data) {
                    $('#chat-box').html(data);
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                }
            });
        }

        // Jalankan fungsi fetchMessages setiap 2 detik
        setInterval(fetchMessages, 2000);

        // Event untuk mengirim pesan
        $('#chat-form').submit(function(e) {
            e.preventDefault();
            const username = $('#username').val();
            const message = $('#message').val();

            $.ajax({
                url: 'send_message.php',
                method: 'POST',
                data: { username: username, message: message },
                success: function() {
                    $('#message').val('');
                    fetchMessages();
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + error);
                }
            });            
        });
    </script>
</body>
</html>
