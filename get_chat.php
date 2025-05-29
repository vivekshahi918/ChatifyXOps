<?php
session_start();

if (isset($_POST["senderid"]) && isset($_POST["receiverid"]) && isset($_POST["email"]) && isset($_POST["session"])) {
    $conn = mysqli_connect("mysql", "root", "root", "chatify");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $senderid = mysqli_real_escape_string($conn, $_POST["senderid"]);
    $receiverid = mysqli_real_escape_string($conn, $_POST["receiverid"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $session = mysqli_real_escape_string($conn, $_POST["session"]);
    $lastMessageId = isset($_POST["lastMessageId"]) ? mysqli_real_escape_string($conn, $_POST["lastMessageId"]) : 0;

    $query = "SELECT * FROM message 
              WHERE ((sender_userid = ? AND receiver_userid = ?) 
              OR (sender_userid = ? AND receiver_userid = ?))
              AND sn > ?
              ORDER BY sn ASC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $senderid, $receiverid, $receiverid, $senderid, $lastMessageId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $chat_class = ($row['sender_userid'] == $senderid) ? "outgoing" : "incoming";

            // Determine the tick class based on read status
            $tickClass = $row['read_status'] == 1 ? 'blue-tick' : 'single-tick';

            if (!empty($row['file_path'])) {
                $fileExt = pathinfo($row['file_path'], PATHINFO_EXTENSION);

                if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo '<div class="chat-message ' . $chat_class . '">
                            <img src="uploads/' . htmlspecialchars($row['file_path']) . '" alt="File" style="max-width: 200px;">
                            <span class="tick ' . $tickClass . '"></span>
                          </div>';
                } elseif (in_array($fileExt, ['mp4', 'mov', 'avi'])) {
                    echo '<div class="chat-message ' . $chat_class . '">
                            <video src="uploads/' . htmlspecialchars($row['file_path']) . '" controls style="max-width: 200px;"></video>
                            <span class="tick ' . $tickClass . '"></span>
                          </div>';
                }
            } else {
                echo '<div class="chat-message ' . $chat_class . '">
                        <p>' . htmlspecialchars($row['message']) . '</p>
                        <span class="tick ' . $tickClass . '"></span>
                      </div>';
            }
        }
    } else {
        echo '<div class="no-messages">No messages found.</div>';
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo 'Required parameters are missing.';
}
