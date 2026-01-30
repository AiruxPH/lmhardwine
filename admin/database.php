<?php
include 'auth.php';
include '../includes/db.php';

// Only Super Admins should ideally have full DB access, but for now we follow general admin access.
// Optimization: You might want to restrict this page to Super Admins only in the future.

// Handle Actions (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['table']) && isset($_GET['pk']) && isset($_GET['val'])) {
    $table = $_GET['table'];
    $pk = $_GET['pk'];
    $val = $_GET['val'];

    try {
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $stmt->execute([$val]);
        $msg = "Row deleted successfully.";
        $msg_type = "success";
    } catch (PDOException $e) {
        $msg = "Error deleting row: " . $e->getMessage();
        $msg_type = "error";
    }
}

// Handle SQL Execution
$sql_result = null;
$sql_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_query'])) {
    $sql = $_POST['sql_query'];
    try {
        if (stripos(trim($sql), 'SELECT') === 0 || stripos(trim($sql), 'SHOW') === 0 || stripos(trim($sql), 'DESCRIBE') === 0) {
            $stmt = $pdo->query($sql);
            $sql_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $msg = "Query executed successfully. " . count($sql_result) . " rows returned.";
            $msg_type = "success";
        } else {
            $pdo->exec($sql);
            $msg = "Query executed successfully.";
            $msg_type = "success";
        }
    } catch (PDOException $e) {
        $sql_error = $e->getMessage();
        $msg = "SQL Error: " . $e->getMessage();
        $msg_type = "error";
    }
}

// Get all tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selected_table = $_GET['table'] ?? ($tables[0] ?? null);
$view = $_GET['view'] ?? 'browse'; // 'browse', 'structure', 'sql'
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$data = [];
$columns_info = [];
$columns = [];
$primary_key = null;
$total_rows = 0;

if ($selected_table && $view !== 'sql') {
    // Get full column information
    $stmt = $pdo->query("DESCRIBE `$selected_table`");
    $columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columns = array_column($columns_info, 'Field');

    // Find Primary Key
    foreach ($columns_info as $col) {
        if ($col['Key'] === 'PRI') {
            $primary_key = $col['Field'];
            break;
        }
    }
    // Fallback if no PRI key (use first column)
    if (!$primary_key && !empty($columns)) {
        $primary_key = $columns[0];
    }

    if ($view === 'browse') {
        // Build query for browsing data
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
        // Explicitly order by PK descending if usually logical, but default is fine
        if ($primary_key) {
            $query .= " ORDER BY `$primary_key` DESC";
        }
        $query .= " LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$total_pages = $limit > 0 ? ceil($total_rows / $limit) : 1;
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

        .table-item:hover,
        .table-item.active {
            background: rgba(114, 14, 30, 0.1);
            color: var(--color-accent);
            border-left: 3px solid var(--color-accent);
        }

        .data-view {
            overflow-x: hidden;
        }

        .view-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.5rem;
        }

        .tab-btn {
            padding: 0.5rem 1.5rem;
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: var(--color-accent);
            background: rgba(114, 14, 30, 0.1);
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

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .db-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .db-table th,
        .db-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            white-space: nowrap;
        }

        .db-table th {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
            position: sticky;
            top: 0;
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

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-pri {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
        }

        .badge-uni {
            background: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #81c784;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #e57373;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .sql-editor {
            width: 100%;
            height: 200px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #444;
            color: #0f0;
            font-family: 'Consolas', monospace;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            resize: vertical;
        }
    </style>
</head>

<body>
    <div class="db-container">
        <aside class="table-list glass-card">
            <h3
                style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: #666; margin-bottom: 1.5rem; padding-left: 0.5rem;">
                Tables</h3>
            <?php foreach ($tables as $table): ?>
                <a href="?table=<?php echo urlencode($table); ?>&view=browse"
                    class="table-item <?php echo $selected_table === $table ? 'active' : ''; ?>">
                    📦 <?php echo htmlspecialchars($table); ?>
                </a>
            <?php endforeach; ?>
        </aside>

        <main class="data-view">
            <header style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <p
                        style="color: var(--color-accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 2px; margin-bottom: 5px; font-weight: 600;">
                        System Insight</p>
                    <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem;">Database <span
                            class="text-accent">Explorer</span></h1>
                    <p style="color: #666; margin-top: 5px;">Table: <strong
                            style="color: #fff;"><?php echo htmlspecialchars($selected_table); ?></strong></p>
                </div>
                <a href="index.php" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.9rem;">←
                    Back to Dashboard</a>
            </header>

            <?php if (isset($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?>">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <div class="view-tabs">
                <a href="?table=<?php echo urlencode($selected_table); ?>&view=browse"
                    class="tab-btn <?php echo $view === 'browse' ? 'active' : ''; ?>">Browse</a>
                <a href="?table=<?php echo urlencode($selected_table); ?>&view=structure"
                    class="tab-btn <?php echo $view === 'structure' ? 'active' : ''; ?>">Structure</a>
                <a href="?table=<?php echo urlencode($selected_table); ?>&view=sql"
                    class="tab-btn <?php echo $view === 'sql' ? 'active' : ''; ?>">SQL Query</a>
            </div>

            <?php if ($view === 'browse'): ?>
                <form class="search-bar" method="GET">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>">
                    <input type="hidden" name="view" value="browse">
                    <input type="text" name="search" placeholder="Smart Search: Type anything (ID, name, etc.)..."
                        value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                    <button type="submit" class="btn">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="?table=<?php echo urlencode($selected_table); ?>&view=browse" class="btn"
                            style="background: #444;">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-wrapper">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <th><?php echo htmlspecialchars($column); ?></th>
                                <?php endforeach; ?>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="<?php echo count($columns) + 1; ?>" class="no-data">No records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $key => $val): ?>
                                            <td><?php echo htmlspecialchars($val ?? 'NULL'); ?></td>
                                        <?php endforeach; ?>
                                        <td style="text-align: right;">
                                            <?php if ($primary_key && isset($row[$primary_key])): ?>
                                                <a href="?table=<?php echo urlencode($selected_table); ?>&view=browse&action=delete&pk=<?php echo $primary_key; ?>&val=<?php echo urlencode($row[$primary_key]); ?>"
                                                    onclick="return confirm('Are you sure you want to delete this row?');"
                                                    style="color: #f44336; text-decoration: none; font-weight: bold; font-size: 0.8rem;">
                                                    DELETE
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?table=<?php echo urlencode($selected_table); ?>&view=browse&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                                class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($view === 'sql'): ?>
                <!-- SQL View -->
                <div class="glass-card">
                    <h3 style="margin-top: 0;">Execute SQL Query</h3>
                    <p style="color: #888; margin-bottom: 1rem;">Enter your custom SQL query below. Use carefully.</p>
                    <form method="POST">
                        <textarea name="sql_query" class="sql-editor" placeholder="SELECT * FROM products WHERE ..."
                            required><?php echo htmlspecialchars($_POST['sql_query'] ?? ''); ?></textarea>
                        <button type="submit" class="btn btn-primary">Execute Query</button>
                    </form>

                    <?php if ($sql_result): ?>
                        <div style="margin-top: 2rem;">
                            <h4>Query Results:</h4>
                            <div class="table-wrapper">
                                <table class="db-table">
                                    <thead>
                                        <tr>
                                            <?php if (!empty($sql_result)): ?>
                                                <?php foreach (array_keys($sql_result[0]) as $col): ?>
                                                    <th><?php echo htmlspecialchars($col); ?></th>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sql_result as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $val): ?>
                                                    <td><?php echo htmlspecialchars($val ?? 'NULL'); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Structure View -->
                <div class="table-wrapper">
                    <table class="db-table">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($columns_info as $col): ?>
                                <tr>
                                    <td style="font-weight: bold; color: #fff;"><?php echo htmlspecialchars($col['Field']); ?>
                                    </td>
                                    <td style="color: var(--color-accent);"><?php echo htmlspecialchars($col['Type']); ?></td>
                                    <td><?php echo htmlspecialchars($col['Null']); ?></td>
                                    <td>
                                        <?php if ($col['Key'] === 'PRI'): ?>
                                            <span class="badge badge-pri">Primary</span>
                                        <?php elseif ($col['Key'] === 'UNI'): ?>
                                            <span class="badge badge-uni">Unique</span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($col['Key']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($col['Default'] ?? 'None'); ?></td>
                                    <td style="font-style: italic; color: #666;"><?php echo htmlspecialchars($col['Extra']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>