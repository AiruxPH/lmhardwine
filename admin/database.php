<?php
include 'auth.php';
include '../includes/db.php';

// Get all tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selected_table = $_GET['table'] ?? ($tables[0] ?? null);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$data = [];
$columns = [];
$total_rows = 0;

if ($selected_table) {
    // Get columns
    $stmt = $pdo->query("DESCRIBE `$selected_table` status");
    $columns = $pdo->query("DESCRIBE `$selected_table`")->fetchAll(PDO::FETCH_COLUMN);

    // Build query
    $query = "SELECT * FROM `$selected_table`";
    $params = [];

    if (!empty($search)) {
        $where_clauses = [];
        foreach ($columns as $column) {
            $where_clauses[] = "`$column` LIKE :search";
        }
        $query .= " WHERE " . implode(" OR ", $where_clauses);
        $params['search'] = "%$search%";
    }

    // Count total rows for pagination
    $count_query = "SELECT COUNT(*) FROM ($query) AS t";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_rows = $stmt->fetchColumn();

    // Fetch data
    $query .= " LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Explorer - Admin Portal</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .db-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            padding-top: 80px;
        }

        .table-list {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1rem;
            height: fit-content;
        }

        .table-item {
            display: block;
            padding: 0.8rem 1rem;
            color: var(--color-text-muted);
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .table-item:hover, .table-item.active {
            background: rgba(114, 14, 30, 0.1);
            color: var(--color-accent);
            border-left: 3px solid var(--color-accent);
        }

        .data-view {
            overflow-x: auto;
        }

        .search-bar {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
        }

        .search-bar input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.8rem 1.2rem;
            color: #fff;
            border-radius: 8px;
        }

        .db-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            overflow: hidden;
            font-size: 0.85rem;
        }

        .db-table th, .db-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .db-table th {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
        }

        .db-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .page-link {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .page-link.active {
            background: var(--color-accent);
            border-color: var(--color-accent);
        }

        .no-data {
            padding: 3rem;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="db-container">
        <aside class="table-list glass-card">
            <h3 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 1.5rem; padding-left: 0.5rem;">Tables</h3>
            <?php foreach ($tables as $table): ?>
                <a href="?table=<?php echo $table; ?>" class="table-item <?php echo $selected_table === $table ? 'active' : ''; ?>">
                    📦 <?php echo htmlspecialchars($table); ?>
                </a>
            <?php endforeach; ?>
        </aside>

        <main class="data-view">
            <header style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <p style="color: var(--color-accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; margin-bottom: 5px; font-weight: 600;">System Insight</p>
                    <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Database <span class="text-accent">Explorer</span></h1>
                    <p style="color: #666; margin-top: 5px;">Viewing table: <strong style="color: #fff;"><?php echo htmlspecialchars($selected_table); ?></strong></p>
                </div>
                <a href="index.php" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem;">← Back to Dashboard</a>
            </header>

            <form class="search-bar" method="GET">
                <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>">
                <input type="text" name="search" placeholder="Search in all columns..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="?table=<?php echo $selected_table; ?>" class="btn" style="background: #444;">Clear</a>
                <?php endif; ?>
            </form>

            <div class="glass-card" style="padding: 0; overflow: hidden;">
                <table class="db-table">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="<?php echo count($columns); ?>" class="no-data">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($row as $val): ?>
                                        <td><?php echo htmlspecialchars($val ?? 'NULL'); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?table=<?php echo $selected_table; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
