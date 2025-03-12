<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include_once '../backend-php/database.php'; // Include your database connection

// Determine which user role is logged in
$userRole = $_SESSION['type'];
$logged_in_user_id = $_SESSION['id'];

// Fetch users based on user type
if ($userRole === 'librarian') {
    // Get all students for the librarian to chat with
    $result = $conn->query("SELECT id, name FROM users WHERE type = 'student'");
} elseif ($userRole === 'student') {
    // Get librarians for the student to chat with
    $result = $conn->query("SELECT id, name FROM users WHERE type = 'librarian'");
} else {
    // Optionally handle other roles
    $result = [];
}

$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <style>
        /* Add your styles here */
        #chat-window {
            border: 1px solid #ccc;
            padding: 10px;
            height: 400px;
            overflow-y: scroll;
        }
        #messages {
            list-style-type: none;
            padding: 0;
        }
    </style>
</head>
<body>

<h1>Chat System</h1>
<select id="recipient">
    <option value="">Select a user to chat</option>
    <?php foreach ($users as $user): ?>
        <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
    <?php endforeach; ?>
</select>

<div id="chat-window">
    <ul id="messages"></ul>
</div>

<input type="text" id="message" placeholder="Type your message here..." />
<button id="send">Send</button>

<script>
    const sendButton = document.getElementById('send');
    const messageInput = document.getElementById('message');
    const recipientSelect = document.getElementById('recipient');
    const messagesList = document.getElementById('messages');

    function fetchMessages(recipientId) {
        if (recipientId) {
            fetch(`fetch_messages.php?recipient=${recipientId}`)
                .then(response => response.json())
                .then(data => {
                    messagesList.innerHTML = '';
                    data.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg.message + " (from: " + (msg.sender_id === <?php echo $_SESSION['id']; ?> ? 'You' : msg.sender_id) + ")";
                        messagesList.appendChild(li);
                    });
                });
        }
    }

    recipientSelect.addEventListener('change', (event) => {
        const recipientId = event.target.value;
        fetchMessages(recipientId);
    });

    sendButton.addEventListener('click', () => {
        const recipientId = recipientSelect.value;
        const message = messageInput.value;

        if (recipientId && message) {
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `recipient_id=${recipientId}&message=${encodeURIComponent(message)}`
            }).then(() => {
                messageInput.value = '';
                fetchMessages(recipientId);
            });
        }
    });
</script>
</body>
</html>