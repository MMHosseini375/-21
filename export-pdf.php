<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$projectsFile = __DIR__ . '/data/projects.json';
$allProjects = [];

if (file_exists($projectsFile)) {
    $allProjects = json_decode(file_get_contents($projectsFile), true) ?: [];
}

$singleId = $_GET['single_id'] ?? '';
$bulkIds = $_GET['ids'] ?? '';

if (!empty($singleId)) {
    $projects = array_filter($allProjects, function($p) use ($singleId) {
        return ($p['id'] ?? '') === $singleId;
    });
    $projects = array_values($projects);
    $reportTitle = 'گزارش تک پروژه';
} elseif (!empty($bulkIds)) {
    // گزارش گروهی بر اساس IDهای انتخاب شده
    $idsArray = explode(',', $bulkIds);
    $projects = array_filter($allProjects, function($p) use ($idsArray) {
        return in_array($p['id'] ?? '', $idsArray);
    });
    $projects = array_values($projects);
    $reportTitle = 'گزارش پروژه‌های انتخاب شده';
} else {
    $search = $_GET['search'] ?? '';
    $projects = $allProjects;
    if ($search) {
        $projects = array_filter($projects, function($p) use ($search) {
            return stripos($p['project_name'] ?? '', $search) !== false ||
                   stripos($p['full_name'] ?? '', $search) !== false ||
                   stripos($p['id'] ?? '', $search) !== false;
        });
        $projects = array_values($projects);
    }
    $reportTitle = 'گزارش کلی پروژه‌ها';
}

$singleProject = (count($projects) === 1) ? $projects[0] : null;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش پروژه - نسخه چاپ</title>
    <link rel="stylesheet" href="assets/css/export-pdf.css">
    <style>
    </style>
</head>
<body>
    <div class="header">
        <h1>اداره کل امور عشایر استان چهارمحال و بختیاری</h1>
        <h2><?php echo $reportTitle; ?></h2>
    </div>

    <div class="info">
        <span>تاریخ گزارش: <?php echo date('Y/m/d'); ?></span>
        <span>تعداد پروژه‌ها: <?php echo count($projects); ?></span>
    </div>

    <?php if (empty($projects)): ?>
        <p style="text-align: center; color: #999;">هیچ پروژه‌ای برای نمایش وجود ندارد.</p>
    <?php elseif ($singleProject): ?>
        <!-- نمایش جزئیات کامل یک پروژه -->
        <div class="project-title">
            🏗️ پروژه: <?php echo htmlspecialchars($singleProject['project_name'] ?? 'بدون نام'); ?>
        </div>
        
        <div class="detail-section">
            <h4>📋 اطلاعات اولیه</h4>
            <div class="detail-row">
                <div class="detail-item"><label>کد پروژه</label><span><?php echo htmlspecialchars($singleProject['id'] ?? '-'); ?></span></div>
                <div class="detail-item"><label>نام کاربر</label><span><?php echo htmlspecialchars($singleProject['full_name'] ?? '-'); ?></span></div>
                <div class="detail-item"><label>استان</label><span><?php echo htmlspecialchars($singleProject['province'] ?? 'چهارمحال و بختیاری'); ?></span></div>
                <div class="detail-item"><label>شهرستان</label><span><?php echo htmlspecialchars($singleProject['city'] ?? '-'); ?></span></div>
                <div class="detail-item"><label>بخش</label><span><?php echo htmlspecialchars($singleProject['district'] ?? '-'); ?></span></div>
                <div class="detail-item"><label>منطقه عشایری</label><span><?php echo htmlspecialchars($singleProject['region'] ?? '-'); ?></span></div>
                <div class="detail-item"><label>نوع پروژه</label><span><?php echo ($singleProject['project_type'] ?? '') === 'water' ? '💧 تأمین آب' : '🛣️ تأمین راه'; ?></span></div>
                <div class="detail-item"><label>تاریخ ثبت</label><span><?php echo htmlspecialchars($singleProject['date'] ?? '-'); ?></span></div>
            </div>
        </div>
        
        <?php if (($singleProject['project_type'] ?? '') === 'water'): ?>
            <!-- ========== پروژه آب ========== -->
            <div class="detail-section">
                <h4>💧 مشخصات پروژه تأمین آب</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>نام پروژه</label><span><?php echo htmlspecialchars($singleProject['project_name'] ?? '-'); ?></span></div>
                    <?php $wt = $singleProject['water_type'] ?? ''; ?>
                    <div class="detail-item"><label>نوع پروژه آب</label><span><?php echo $wt === 'linear' ? 'خطی' : ($wt === 'point' ? 'نقطه‌ای' : '-'); ?></span></div>
                    <?php if ($wt === 'linear'): ?>
                        <?php $lt = $singleProject['linear_type'] ?? ''; ?>
                        <div class="detail-item"><label>نوع خطی</label><span><?php echo $lt === 'pumping' ? 'پمپاژ' : ($lt === 'gravity' ? 'ثقلی' : '-'); ?></span></div>
                    <?php endif; ?>
                    <div class="detail-item"><label>طول مسیر</label><span><?php echo ($singleProject['route_length'] ?? '') ? $singleProject['route_length'] . ' متر' : '-'; ?></span></div>
                    <div class="detail-item"><label>طول رایتاپه</label><span><?php echo ($singleProject['rightape_length'] ?? '') ? $singleProject['rightape_length'] . ' متر' : '-'; ?></span></div>
                </div>
                
                <?php if (!empty($singleProject['coordinates'])): ?>
                <h4 style="margin-top:8px;">📍 مختصات</h4>
                <table class="detail-table">
                    <thead><tr><th>ردیف</th><th>مختصات X</th><th>مختصات Y</th></tr></thead>
                    <tbody>
                        <?php foreach ($singleProject['coordinates'] as $i => $c): ?>
                        <tr><td><?php echo $i+1; ?></td><td><?php echo htmlspecialchars($c['x']??'-'); ?></td><td><?php echo htmlspecialchars($c['y']??'-'); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <?php if ($wt === 'linear' && ($singleProject['linear_type'] ?? '') === 'pumping'): ?>
            <div class="detail-section">
                <h4>🔌 اطلاعات پمپاژ</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>فاصله تا منبع برق</label><span><?php echo ($singleProject['power_distance']??'') ? $singleProject['power_distance'].' متر' : '-'; ?></span></div>
                    <div class="detail-item"><label>نوع پمپ</label><span><?php echo htmlspecialchars($singleProject['pump_type']??'-'); ?></span></div>
                    <div class="detail-item"><label>برق مورد نیاز</label><span><?php echo ($singleProject['power_needed']??'') ? $singleProject['power_needed'].' KW' : '-'; ?></span></div>
                    <div class="detail-item"><label>نوع لوله</label><span><?php echo htmlspecialchars($singleProject['pipe_type']??'-'); ?></span></div>
                    <div class="detail-item"><label>سایز لوله</label><span><?php echo ($singleProject['pipe_size']??'') ? $singleProject['pipe_size'].' اینچ' : '-'; ?></span></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="detail-section">
                <h4>💦 منبع تأمین آب</h4>
                <div class="detail-row">
                    <?php $st = $singleProject['water_source_type'] ?? ''; ?>
                    <div class="detail-item"><label>نوع منبع</label><span><?php echo $st==='spring'?'چشمه':($st==='well'?'چاه':($st==='other'?'سایر':'-')); ?></span></div>
                    <div class="detail-item"><label>نام منبع</label><span><?php echo htmlspecialchars($singleProject['water_source_name']??'-'); ?></span></div>
                    <div class="detail-item"><label>دبی آب</label><span><?php echo htmlspecialchars($singleProject['water_flow']??'-'); ?></span></div>
                    <div class="detail-item"><label>شماره مجوز</label><span><?php echo htmlspecialchars($singleProject['license_number']??'-'); ?></span></div>
                    <div class="detail-item"><label>تاریخ مجوز</label><span><?php echo htmlspecialchars($singleProject['license_date']??'-'); ?></span></div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>👥 اطلاعات تکمیلی (بهره‌برداران)</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>ایل</label><span><?php echo htmlspecialchars($singleProject['tribe']??'-'); ?></span></div>
                    <div class="detail-item"><label>طایفه</label><span><?php echo htmlspecialchars($singleProject['clan']??'-'); ?></span></div>
                    <div class="detail-item"><label>تیره</label><span><?php echo htmlspecialchars($singleProject['sub_clan']??'-'); ?></span></div>
                    <div class="detail-item"><label>تعداد خانوار</label><span><?php echo htmlspecialchars($singleProject['households']??'-'); ?></span></div>
                    <div class="detail-item"><label>جمعیت</label><span><?php echo htmlspecialchars($singleProject['population']??'-'); ?></span></div>
                    <div class="detail-item"><label>تعداد دام</label><span><?php echo htmlspecialchars($singleProject['livestock']??'-'); ?></span></div>
                    <div class="detail-item"><label>نیاز آبی</label><span><?php echo htmlspecialchars($singleProject['water_need']??'-'); ?></span></div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>💰 اطلاعات مالی و قرارداد</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>مبلغ تأمین اعتبار</label><span><?php echo ($singleProject['credit_amount']??'') ? number_format($singleProject['credit_amount']).' ریال' : '-'; ?></span></div>
                    <div class="detail-item"><label>محل تأمین اعتبار</label><span><?php echo htmlspecialchars($singleProject['credit_source']??'-'); ?></span></div>
                    <div class="detail-item"><label>شماره قرارداد</label><span><?php echo htmlspecialchars($singleProject['contract_number']??'-'); ?></span></div>
                    <div class="detail-item"><label>تاریخ قرارداد</label><span><?php echo htmlspecialchars($singleProject['contract_date']??'-'); ?></span></div>
                    <div class="detail-item"><label>مبلغ قرارداد</label><span><?php echo ($singleProject['contract_amount']??'') ? number_format($singleProject['contract_amount']).' ریال' : '-'; ?></span></div>
                    <div class="detail-item"><label>نام پیمانکار</label><span><?php echo htmlspecialchars($singleProject['contractor_name']??'-'); ?></span></div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>📄 صورت‌جلسه ابلاغ ۲۵٪</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>شماره</label><span><?php echo htmlspecialchars($singleProject['notification_number']??'-'); ?></span></div>
                    <div class="detail-item"><label>تاریخ</label><span><?php echo htmlspecialchars($singleProject['notification_date']??'-'); ?></span></div>
                </div>
            </div>
            
            <?php if (!empty($singleProject['temp_status'])): ?>
            <div class="detail-section">
                <h4>📝 صورت وضعیت موقت</h4>
                <table class="detail-table">
                    <thead><tr><th>ردیف</th><th>مبلغ (ریال)</th><th>تاریخ</th></tr></thead>
                    <tbody>
                        <?php foreach ($singleProject['temp_status'] as $i => $ts): ?>
                        <tr><td><?php echo $i+1; ?></td><td><?php echo ($ts['amount']??'') ? number_format($ts['amount']) : '-'; ?></td><td><?php echo htmlspecialchars($ts['date']??'-'); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="detail-section">
                <h4>✅ صورت وضعیت قطعی</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>مبلغ</label><span><?php echo ($singleProject['final_amount']??'') ? number_format($singleProject['final_amount']).' ریال' : '-'; ?></span></div>
                    <div class="detail-item"><label>تاریخ</label><span><?php echo htmlspecialchars($singleProject['final_date']??'-'); ?></span></div>
                </div>
            </div>
            
            <?php if (!empty($singleProject['warnings'])): ?>
            <div class="detail-section">
                <h4>⚠️ اخطارهای پیمانکار</h4>
                <table class="detail-table">
                    <thead><tr><th>ردیف</th><th>شماره اخطار</th><th>تاریخ اخطار</th></tr></thead>
                    <tbody>
                        <?php foreach ($singleProject['warnings'] as $i => $w): ?>
                        <tr><td><?php echo $i+1; ?></td><td><?php echo htmlspecialchars($w['number']??'-'); ?></td><td><?php echo htmlspecialchars($w['date']??'-'); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="detail-section">
                <h4>📐  برآورد حجم رایتاپه</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>حجم خاکی</label><span><?php echo ($singleProject['soil_volume']??'') ? $singleProject['soil_volume'].' متر مکعب' : '-'; ?></span></div>
                    <div class="detail-item"><label>حجم سنگی</label><span><?php echo ($singleProject['rock_volume']??'') ? $singleProject['rock_volume'].' متر مکعب' : '-'; ?></span></div>
                </div>
            </div>
            
            <?php if (!empty($singleProject['description'])): ?>
            <div class="detail-section">
                <h4>📝 شرح پروژه</h4>
                <p style="font-size:10px; line-height:1.6;"><?php echo nl2br(htmlspecialchars($singleProject['description'])); ?></p>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- ========== پروژه راه ========== -->
            <div class="detail-section">
                <h4>🛣️ مشخصات پروژه تأمین راه</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>نام پروژه</label><span><?php echo htmlspecialchars($singleProject['project_name'] ?? '-'); ?></span></div>
                    <?php $rt = $singleProject['road_type'] ?? ''; ?>
                    <div class="detail-item"><label>نوع پروژه راه</label><span><?php echo $rt === 'construction' ? 'احداثی' : ($rt === 'maintenance' ? 'مرمت و بازگشایی' : '-'); ?></span></div>
                    <div class="detail-item"><label>طول مسیر</label><span><?php echo ($singleProject['route_length']??'') ? $singleProject['route_length'].' متر' : '-'; ?></span></div>
                    <div class="detail-item"><label>طول رایتاپه</label><span><?php echo ($singleProject['rightape_length']??'') ? $singleProject['rightape_length'].' متر' : '-'; ?></span></div>
                </div>
                
                <?php if (!empty($singleProject['coordinates'])): ?>
                <h4 style="margin-top:8px;">📍 مختصات</h4>
                <table class="detail-table">
                    <thead><tr><th>ردیف</th><th>مختصات X</th><th>مختصات Y</th></tr></thead>
                    <tbody>
                        <?php foreach ($singleProject['coordinates'] as $i => $c): ?>
                        <tr><td><?php echo $i+1; ?></td><td><?php echo htmlspecialchars($c['x']??'-'); ?></td><td><?php echo htmlspecialchars($c['y']??'-'); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <?php if ($rt === 'construction'): ?>
            <div class="detail-section">
                <h4>📋 اطلاعات احداثی</h4>
                <div class="detail-row">
                    <?php $hl = $singleProject['has_license'] ?? ''; ?>
                    <div class="detail-item"><label>وضعیت مجوز</label><span><?php echo $hl==='yes'?'✅ دارد':($hl==='no'?'❌ ندارد':'-'); ?></span></div>
                    <div class="detail-item"><label>شماره مجوز</label><span><?php echo htmlspecialchars($singleProject['license_number']??'-'); ?></span></div>
                    <div class="detail-item"><label>تاریخ مجوز</label><span><?php echo htmlspecialchars($singleProject['license_date']??'-'); ?></span></div>
                </div>
                
                <h4 style="margin-top:8px;">👥 مشخصات بهره‌برداران</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>مختصات X</label><span><?php echo htmlspecialchars($singleProject['beneficiary_x']??'-'); ?></span></div>
                    <div class="detail-item"><label>مختصات Y</label><span><?php echo htmlspecialchars($singleProject['beneficiary_y']??'-'); ?></span></div>
                    <div class="detail-item"><label>ایل</label><span><?php echo htmlspecialchars($singleProject['tribe']??'-'); ?></span></div>
                    <div class="detail-item"><label>طایفه</label><span><?php echo htmlspecialchars($singleProject['clan']??'-'); ?></span></div>
                    <div class="detail-item"><label>تیره</label><span><?php echo htmlspecialchars($singleProject['sub_clan']??'-'); ?></span></div>
                    <div class="detail-item"><label>تعداد خانوار</label><span><?php echo htmlspecialchars($singleProject['households']??'-'); ?></span></div>
                    <div class="detail-item"><label>تعداد دام</label><span><?php echo htmlspecialchars($singleProject['livestock']??'-'); ?></span></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($singleProject['culverts'])): ?>
            <div class="detail-section">
                <h4>🌊 بخش ابنیه فنی (آبروها)</h4>
                <table class="detail-table">
                    <thead><tr><th>ردیف</th><th>نوع آبرو</th><th>طول دهانه</th><th>مختصات X</th><th>مختصات Y</th></tr></thead>
                    <tbody>
                        <?php foreach ($singleProject['culverts'] as $i => $cul): ?>
                        <tr><td><?php echo $i+1; ?></td><td><?php echo htmlspecialchars($cul['type']??'-'); ?></td><td><?php echo htmlspecialchars($cul['length']??'-'); ?></td><td><?php echo htmlspecialchars($cul['x']??'-'); ?></td><td><?php echo htmlspecialchars($cul['y']??'-'); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="detail-section">
                <h4>📐 برآورد حجم رایناپه</h4>
                <div class="detail-row">
                    <div class="detail-item"><label>حجم خاکی</label><span><?php echo ($singleProject['soil_volume']??'') ? $singleProject['soil_volume'].' متر مکعب' : '-'; ?></span></div>
                    <div class="detail-item"><label>حجم سنگی</label><span><?php echo ($singleProject['rock_volume']??'') ? $singleProject['rock_volume'].' متر مکعب' : '-'; ?></span></div>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- نمایش جدول چند پروژه -->
        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>کد پروژه</th>
                    <th>نام پروژه</th>
                    <th>کاربر</th>
                    <th>نوع پروژه</th>
                    <th>تاریخ ثبت</th>
                    <th>شهرستان</th>
                    <th>بخش</th>
                    <th>ایل</th>
                    <th>طایفه</th>
                    <th>تیره</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $i => $project): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($project['id'] ?? ''); ?></td>
                    <td><strong><?php echo htmlspecialchars($project['project_name'] ?? ''); ?></strong></td>
                    <td><?php echo htmlspecialchars($project['user'] ?? ''); ?></td>
                    <td><?php echo ($project['project_type'] ?? '') === 'water' ? 'تأمین آب' : 'تأمین راه'; ?></td>
                    <td><?php echo htmlspecialchars($project['date'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($project['city'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($project['district'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($project['tribe'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($project['clan'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($project['sub_clan'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <button class="print-btn no-print" onclick="window.print()">🖨️ چاپ گزارش</button>
</body>
</html>