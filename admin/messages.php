<?php
include 'auth.php';
include '../includes/db.php';

// Handle Reply Sending
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_reply') {
    $msg_id = (int)$_POST['message_id'];
    $to_email = $_POST['to_email'];
    $reply_body = trim($_POST['reply_body']);
    $original_subject = $_POST['original_subject'];

    if ($msg_id && $to_email && $reply_body) {
        $subject = "Re: " . $original_subject;
        $headers = "From: info@lmhardwine.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($to_email, $subject, $reply_body, $headers)) {
            // Update status to 'replied'
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
            $stmt->execute([$msg_id]);
            $success_msg = "Reply sent successfully.";
        } else {
            $error_msg = "Failed to send email. Please check server configuration.";
        }
    } else {
        $error_msg = "Please fill in all fields.";
    }
}

// Pagination & Search Logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build Query
$where_sql = "";
$params = [];

if ($search) {
    $where_sql = "WHERE name LIKE ? OR email LIKE ? OR subject LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Count Total for Pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $where_sql");
$count_stmt->execute($params);
$total_msgs = $count_stmt->fetchColumn();
$total_pages = ceil($total_msgs / $per_page);

// Fetch Messages
$sql = "SELECT * FROM contact_messages $where_sql ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - LM Hard Wine Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 80px;
        }

        /* Inbox Toolbar */
        .inbox-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .search-box input {
            background: transparent;
            border: none;
            color: #fff;
            padding: 5px;
            outline: none;
        }

        /* Message Table */
        .inbox-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inbox-table th,
        .inbox-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .inbox-table th {
            color: var(--color-text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .inbox-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
            cursor: pointer;
        }

        .msg-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-new { background: var(--color-accent); box-shadow: 0 0 5px var(--color-accent); }
        .status-read { background: #666; }
        .status-replied { background: #4caf50; }

        .preview-text {
            color: #888;
            font-size: 0.9rem;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            width: 90%;
            max-width: 600px;
            background: #1a1a1a;
            padding: 2rem;
            border: 1px solid var(--color-accent);
            border-radius: 8px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            cursor: pointer;
            font-size: 1.5rem;
            color: #888;
        }

        .msg-detail-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <header style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <p style="color: var(--color-accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; margin-bottom: 5px; font-weight: 600;">Communications</p>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Message <span class="text-accent">Inbox</span></h1>
            </div>
            <a href="index.php" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem;">← Back to Dashboard</a>
        </header>

        <?php if ($success_msg): ?>
            <div class="alert alert-success" style="background: rgba(76, 175, 80, 0.1); color: #4caf50; padding: 1rem; margin-bottom: 1rem; border: 1px solid #4caf50; border-radius: 4px;"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-error" style="background: rgba(244, 67, 54, 0.1); color: #f44336; padding: 1rem; margin-bottom: 1rem; border: 1px solid #f44336; border-radius: 4px;"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="inbox-toolbar">
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" style="background: none; border: none; color: #fff; cursor: pointer;">🔍</button>
            </form>
            
            <div style="color: var(--color-text-muted); font-size: 0.9rem;">
                Showing <?php echo count($messages); ?> of <?php echo $total_msgs; ?> messages
            </div>
        </div>

        <div class="glass-card" style="padding: 0;">
            <table class="inbox-table">
                <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Preview</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 3rem; color: #666;">No messages found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr onclick='openMessage(<?php echo json_encode($msg); ?>)'>
                                <td style="text-align: center;">
                                    <span class="msg-status status-<?php echo $msg['status'] ?? 'new'; ?>" title="<?php echo ucfirst($msg['status'] ?? 'new'); ?>"></span>
                                </td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                <td class="preview-text"><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . '...'; ?></td>
                                <td style="color: #666; font-size: 0.85rem;"><?php echo date('M d', strtotime($msg['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       style="padding: 5px 10px; border: 1px solid rgba(255,255,255,0.1); color: <?php echo $i === $page ? 'var(--color-accent)' : '#fff'; ?>; text-decoration: none;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Message Modal -->
    <div id="msgModal" class="modal-overlay">
        <div class="modal-content glass-card">
            <span class="close-modal" onclick="closeModal()">×</span>
            
            <div class="msg-detail-header">
                <h2 id="modalSubject" style="margin-bottom: 0.5rem;">Subject</h2>
                <div style="display: flex; justify-content: space-between; color: #888; font-size: 0.9rem;">
                    <span id="modalSender">From: Name (email)</span>
                    <span id="modalDate">Date</span>
                </div>
            </div>

            <div id="modalBody" style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; white-space: pre-wrap; max-height: 200px; overflow-y: auto;">
                Message content...
            </div>

            <div id="replySection">
                <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--color-accent);">Reply</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="send_reply">
                    <input type="hidden" name="message_id" id="replyMsgId">
                    <input type="hidden" name="to_email" id="replyEmail">
                    <input type="hidden" name="original_subject" id="replySubject">
                    
                    <textarea name="reply_body" rows="4" class="form-control" placeholder="Type your reply here..." required
                        style="width: 100%; background: #111; border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px; margin-bottom: 1rem;"></textarea>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reply</button>
                </form>
            </div>
            
            <div id="repliedBadge" style="display: none; text-align: center; padding: 1rem; background: rgba(76, 175, 80, 0.1); color: #4caf50; border: 1px solid #4caf50; border-radius: 4px;">
                ✓ This message has been replied to.
            </div>
        </div>
    </div>

    <script>
        function openMessage(msg) {
            document.getElementById('modalSubject').textContent = msg.subject;
            document.getElementById('modalSender').textContent = `From: ${msg.name} <${msg.email}>`;
            document.getElementById('modalDate').textContent = new Date(msg.created_at).toLocaleString();
            document.getElementById('modalBody').textContent = msg.message;
            
            // Reply Form Data
            document.getElementById('replyMsgId').value = msg.id;
            document.getElementById('replyEmail').value = msg.email;
            document.getElementById('replySubject').value = msg.subject;

            // Show/Hide Reply section based on status
            if (msg.status === 'replied') {
                document.getElementById('replySection').style.display = 'none';
                document.getElementById('repliedBadge').style.display = 'block';
            } else {
                document.getElementById('replySection').style.display = 'block';
                document.getElementById('repliedBadge').style.display = 'none';
                
                // If status is new, we could AJAX update it to 'read' here, but for now let's just show it.
            }

            document.getElementById('msgModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('msgModal').style.display = 'none';
        }

        // Close on click outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('msgModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>
