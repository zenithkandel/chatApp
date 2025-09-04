<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include_once "config.php";
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $output = "";
        $sql = "SELECT * FROM messages LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
                WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
                OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) ORDER BY msg_id";
        $query = mysqli_query($conn, $sql);
        // Helper to escape and convert URLs to clickable links
        function linkify($text){
            $escaped = htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
            // http/https links
            $escaped = preg_replace(
                '~(https?://[^\s<]+)~i',
                '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
                $escaped
            );
            // www. links (add http://) without lookbehind; ensure not preceded by : or /
            $escaped = preg_replace(
                '~(^|[^:/])(www\.[^\s<]+)~i',
                '$1<a href="http://$2" target="_blank" rel="noopener noreferrer">$2</a>',
                $escaped
            );
            return $escaped;
        }

        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                $hasImage = isset($row['image']) && $row['image'] !== null && $row['image'] !== '';
                $msgHtml = '';
                if($row['msg'] !== ''){
                    $msgHtml .= '<p>'. linkify($row['msg']) .'</p>';
                }
                if($hasImage){
                    $msgHtml .= '<div class="msg-image"><img src="php/images/messages/'. htmlspecialchars($row['image']) .'" alt="attachment"></div>';
                }

                if($row['outgoing_msg_id'] === $outgoing_id){
                    $output .= '<div class="chat outgoing"><div class="details">'. $msgHtml .'</div></div>';
                }else{
                    $output .= '<div class="chat incoming"><img src="php/images/'.$row['img'].'" alt=""><div class="details">'. $msgHtml .'</div></div>';
                }
            }
        }else{
            $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
        }
        echo $output;
    }else{
        header("location: ../login.php");
    }

?>