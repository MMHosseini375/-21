<?php
session_start();

// بررسی دسترسی مدیر
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// خواندن پروژه‌ها
$projectsFile = __DIR__ . '/data/projects.json';
$projects = [];

if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true) ?: [];
}

// حذف پروژه
if (isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $projects = array_filter($projects, function($p) use ($deleteId) {
        return $p['id'] !== $deleteId;
    });
    $projects = array_values($projects);
    file_put_contents($projectsFile, json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: admin-panel.php?deleted=1');
    exit();
}

// جستجوی پیشرفته
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $projects = array_filter($projects, function($p) use ($search) {
        // جستجو در نام پروژه
        if (isset($p['project_name']) && stripos($p['project_name'], $search) !== false) {
            return true;
        }
        // جستجو در نام کاربر
        if (isset($p['full_name']) && stripos($p['full_name'], $search) !== false) {
            return true;
        }
        // جستجو در کد پروژه
        if (isset($p['id']) && stripos($p['id'], $search) !== false) {
            return true;
        }
        // جستجو در شهرستان
        if (isset($p['city']) && stripos($p['city'], $search) !== false) {
            return true;
        }
        // جستجو در نام پیمانکار (برای پروژه‌های آب)
        if (isset($p['contractor_name']) && stripos($p['contractor_name'], $search) !== false) {
            return true;
        }
        return false;
    });
    $projects = array_values($projects);
}

// آمار
$totalProjects = count($projects);
$waterProjects = count(array_filter($projects, fn($p) => ($p['project_type'] ?? '') === 'water'));
$roadProjects = count(array_filter($projects, fn($p) => ($p['project_type'] ?? '') === 'road'));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت - اداره کل امور عشایر چهارمحال و بختیاری</title>
    <link rel="stylesheet" href="assets/css/admin-panel.css">
    <style>
        .checkbox-cell {
            text-align: center;
            width: 50px;
        }
        .project-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .bulk-actions {
            display: none;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            align-items: center;
            gap: 15px;
            border: 2px solid #dee2e6;
        }
        .bulk-actions.active {
            display: flex;
        }
        .bulk-actions .selected-count {
            font-weight: bold;
            color: #495057;
        }
        .bulk-actions .btn {
            white-space: nowrap;
        }
        .select-all-header {
            text-align: center;
            width: 50px;
        }
        #selectAllCheckbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            <span>🏛️</span>
            پنل مدیریت - اداره کل امور عشایر چهارمحال و بختیاری
        </h1>
        <div class="header-actions">
            <span class="user-info">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> (مدیر)</span>
            <a href="export-excel.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" class="btn btn-success">📊 خروجی Excel (همه)</a>
            <a href="export-pdf.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" class="btn btn-info">📄 خروجی PDF (همه)</a>
            <a href="logout.php" class="btn btn-red">خروج</a>
        </div>
    </div>

    <div class="container">
        <!-- کارت‌های آمار -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $totalProjects; ?></div>
                <div class="stat-label">کل پروژه‌ها</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💧</div>
                <div class="stat-number"><?php echo $waterProjects; ?></div>
                <div class="stat-label">پروژه‌های تأمین آب</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🛣️</div>
                <div class="stat-number"><?php echo $roadProjects; ?></div>
                <div class="stat-label">پروژه‌های تأمین راه</div>
            </div>
        </div>

        <!-- نوار جستجو -->
        <div class="search-bar">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input"
                       placeholder="🔍 جستجو بر اساس نام پروژه، کد، شهرستان، نام کاربر یا پیمانکار..."
                       value="<?php echo htmlspecialchars($search); ?>"
                       autofocus>
                <button type="submit" class="btn btn-gold">🔍 جستجو</button>
                <?php if ($search): ?>
                    <a href="admin-panel.php" class="btn btn-red">✕ حذف فیلتر</a>
                <?php endif; ?>
            </form>
            <small class="search-hint">
                💡 می‌توانید بر اساس <strong>نام پروژه</strong>، کد پروژه، شهرستان، نام کاربر یا نام پیمانکار جستجو کنید
            </small>
        </div>

        <!-- عملیات گروهی -->
        <div id="bulkActions" class="bulk-actions">
            <span class="selected-count">📋 <span id="selectedCount">0</span> پروژه انتخاب شده است</span>
            <a href="#" id="bulkExcelBtn" class="btn btn-success" onclick="exportBulk('excel')">📊 تبدیل به اکسل</a>
            <a href="#" id="bulkPdfBtn" class="btn btn-info" onclick="exportBulk('pdf')">📄 تبدیل به PDF</a>
            <button type="button" class="btn btn-red" onclick="clearSelection()">✕ لغو انتخاب</button>
        </div>

        <!-- جدول پروژه‌ها -->
        <div class="projects-table">
            <div class="table-header">
                <span>📋 لیست پروژه‌های ثبت شده</span>
                <?php if ($search): ?>
                    <span class="result-count"><?php echo count($projects); ?> نتیجه برای "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            <div class="table-wrapper">
                <?php if (empty($projects)): ?>
                    <div class="no-data">
                        <?php if ($search): ?>
                            ❌ هیچ پروژه‌ای با عبارت "<?php echo htmlspecialchars($search); ?>" یافت نشد
                        <?php else: ?>
                            📭 هیچ پروژه‌ای ثبت نشده است
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th class="select-all-header">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                </th>
                                <th>ردیف</th>
                                <th>کد پروژه</th>
                                <th>نام پروژه</th>
                                <th>کاربر</th>
                                <th>شهرستان</th>
                                <th>نوع پروژه</th>
                                <th>تاریخ ثبت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $index => $project): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="project-checkbox" value="<?php echo htmlspecialchars($project['id']); ?>" data-project-id="<?php echo htmlspecialchars($project['id']); ?>" onchange="updateBulkActions()">
                                </td>
                                <td><?php echo $index + 1; ?></td>
                                <td><small><?php echo htmlspecialchars($project['id'] ?? '-'); ?></small></td>
                                <td>
                                    <span class="project-name">
                                        <?php
                                        $projectName = $project['project_name'] ?? 'بدون نام';
                                        if ($search) {
                                            // هایلایت کردن عبارت جستجو در نام پروژه
                                            $projectName = preg_replace(
                                                '/(' . preg_quote($search, '/') . ')/iu',
                                                '<span class="highlight-search">$1</span>',
                                                htmlspecialchars($projectName)
                                            );
                                        } else {
                                            $projectName = htmlspecialchars($projectName);
                                        }
                                        echo $projectName;
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($project['full_name'] ?? $project['user'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($project['city'] ?? '-'); ?></td>
                                <td>
                                    <?php if (($project['project_type'] ?? '') === 'water'): ?>
                                        <span class="project-type-badge badge-water">💧 تأمین آب</span>
                                    <?php else: ?>
                                        <span class="project-type-badge badge-road">🛣️ تأمین راه</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars($project['date'] ?? '-'); ?></small></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick='viewProject(<?php echo json_encode($project, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn btn-info btn-sm">👁️ مشاهده</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('آیا از حذف این پروژه اطمینان دارید؟\nنام پروژه: <?php echo htmlspecialchars(addslashes($project['project_name'] ?? 'بدون نام')); ?>');">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                                            <button type="submit" class="btn btn-red btn-sm">🗑️ حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal جزئیات پروژه -->
    <div id="project-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">جزئیات پروژه</h2>
                <div class="modal-header-actions">
                    <a href="#" id="modal-excel-btn" class="btn btn-success btn-sm" target="_blank">📊 خروجی Excel</a>
                    <a href="#" id="modal-pdf-btn" class="btn btn-warning btn-sm" target="_blank">📄 خروجی PDF</a>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- محتوای پویا -->
            </div>
        </div>
    </div>
    
    <script>
        // تابع برای انتخاب/لغو انتخاب همه
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.project-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkActions();
        }
        
        // تابع برای به‌روزرسانی وضعیت عملیات گروهی
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.project-checkbox:checked');
            const count = checkboxes.length;
            const bulkActionsDiv = document.getElementById('bulkActions');
            const selectedCountSpan = document.getElementById('selectedCount');
            
            selectedCountSpan.textContent = count;
            
            if (count > 0) {
                bulkActionsDiv.classList.add('active');
            } else {
                bulkActionsDiv.classList.remove('active');
                document.getElementById('selectAllCheckbox').checked = false;
            }
            
            // به‌روزرسانی چک‌باکس انتخاب همه
            const allCheckboxes = document.querySelectorAll('.project-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            selectAllCheckbox.checked = count === allCheckboxes.length && count > 0;
            selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
        }
        
        // تابع برای پاک کردن انتخاب
        function clearSelection() {
            const checkboxes = document.querySelectorAll('.project-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            document.getElementById('selectAllCheckbox').checked = false;
            document.getElementById('selectAllCheckbox').indeterminate = false;
            updateBulkActions();
        }
        
        // تابع برای صادرات گروهی
        function exportBulk(type) {
            const checkboxes = document.querySelectorAll('.project-checkbox:checked');
            const ids = [];
            checkboxes.forEach(cb => {
                ids.push(cb.value);
            });
            
            if (ids.length === 0) {
                alert('هیچ پروژه‌ای انتخاب نشده است!');
                return;
            }
            
            const idsParam = ids.join(',');
            if (type === 'excel') {
                window.open('export-excel.php?ids=' + encodeURIComponent(idsParam), '_blank');
            } else if (type === 'pdf') {
                window.open('export-pdf.php?ids=' + encodeURIComponent(idsParam), '_blank');
            }
        }
    </script>
    <script src="assets/js/admin-panel.js"></script>
</body>
</html>
