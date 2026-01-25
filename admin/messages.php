<?php
include 'auth.php';
include '../includes/db.php';

// Fetch Messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Messages - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .messages-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .message-card {
            padding: 1.5rem;
            border-left: 4px solid var(--color-accent);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.5rem;
        }

        .message-meta {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        .message-content {
            white-space: pre-wrap;
            line-height: 1.6;
            color: #ccc;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <header
            style="margin-bottom: 2rem; border-bottom: 1px solid #333; padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Customer Enquiry</h1>
                <a href="index.php" style="color: var(--color-text-muted);">← Back to Dashboard</a>
            </div>
        </header>

        <div class="messages-list">
            <?php if (empty($messages)): ?>
                <div class="glass-card text-center" style="padding: 3rem;">
                    <p style="color: var(--color-text-muted);">No messages yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="glass-card message-card">
                        <div class="message-header">
                            <div>
                                <h3 style="margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                </h3>
                                <div class="message-meta">
                                    From: <strong>
                                        <?php echo htmlspecialchars($msg['name']); ?>
                                    </strong>
                                    (
                                    <?php echo htmlspecialchars($msg['email']); ?>)
                                </div>
                            </div>
                            <div class="message-meta">
                                <?php echo date('M d, Y | h:i A', strtotime($msg['created_at'])); ?>
                            </div>
                        </div>
                        <div class="message-content">
                            <?php echo htmlspecialchars($msg['message']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>