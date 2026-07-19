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

// اگر شناسه تکی ارسال شده باشد
$singleId = $_GET['single_id'] ?? '';

if (!empty($singleId)) {
    $projects = array_filter($allProjects, function($p) use ($singleId) {
        return ($p['id'] ?? '') === $singleId;
    });
    $projects = array_values($projects);
    $filename = 'project_' . $singleId . '_' . date('Y-m-d_His') . '.csv';
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
    
    $filename = 'all_projects_' . date('Y-m-d_His') . '.csv';
}

// تنظیم هدر برای CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// نوشتن BOM برای UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// هدرهای کامل CSV
fputcsv($output, [
    'کد پروژه',
    'نام پروژه',
    'کاربر',
    'نوع پروژه',
    'تاریخ ثبت',
    'استان',
    'شهرستان',
    'بخش',
    'منطقه عشایری',
    'نوع فرعی پروژه',
    'طول مسیر (متر)',
    'طول رایتاپه (متر)',
    'مختصات (X,Y)',
    'نوع خطی',
    'فاصله تا منبع برق (متر)',
    'نوع پمپ',
    'برق مورد نیاز (KW)',
    'نوع لوله',
    'سایز لوله (اینچ)',
    'دبی آب',
    'نوع منبع تأمین آب',
    'نام منبع',
    'شماره مجوز',
    'تاریخ مجوز',
    'ایل',
    'طایفه',
    'تیره',
    'تعداد خانوار',
    'جمعیت',
    'تعداد دام',
    'نیاز آبی',
    'مبلغ تأمین اعتبار (ریال)',
    'محل تأمین اعتبار',
    'شماره قرارداد',
    'تاریخ قرارداد',
    'مبلغ قرارداد (ریال)',
    'نام پیمانکار',
    'شماره صورت‌جلسه ابلاغ ۲۵٪',
    'تاریخ صورت‌جلسه ابلاغ ۲۵٪',
    'صورت وضعیت‌های موقت',
    'مبلغ قطعی (ریال)',
    'تاریخ قطعی',
    'اخطارهای پیمانکار',
    'حجم خاکی (متر مکعب)',
    'حجم سنگی (متر مکعب)',
    'شرح پروژه',
    'وضعیت مجوز (راه)',
    'مختصات بهره‌بردار X',
    'مختصات بهره‌بردار Y',
    'آبروها'
]);

foreach ($projects as $project) {
    // مختصات
    $coordsStr = '';
    if (!empty($project['coordinates'])) {
        $coordsArr = [];
        foreach ($project['coordinates'] as $c) {
            $coordsArr[] = '(' . ($c['x'] ?? '') . ',' . ($c['y'] ?? '') . ')';
        }
        $coordsStr = implode(' | ', $coordsArr);
    }
    
    // صورت وضعیت موقت
    $tempStatusStr = '';
    if (!empty($project['temp_status'])) {
        $tempArr = [];
        foreach ($project['temp_status'] as $ts) {
            $tempArr[] = ($ts['amount'] ?? '') . ' ریال - ' . ($ts['date'] ?? '');
        }
        $tempStatusStr = implode(' | ', $tempArr);
    }
    
    // اخطارها
    $warningsStr = '';
    if (!empty($project['warnings'])) {
        $warnArr = [];
        foreach ($project['warnings'] as $w) {
            $warnArr[] = 'شماره: ' . ($w['number'] ?? '') . ' - تاریخ: ' . ($w['date'] ?? '');
        }
        $warningsStr = implode(' | ', $warnArr);
    }
    
    // آبروها
    $culvertsStr = '';
    if (!empty($project['culverts'])) {
        $culvArr = [];
        foreach ($project['culverts'] as $cul) {
            $culvArr[] = ($cul['type'] ?? '') . ' (طول:' . ($cul['length'] ?? '') . '، X:' . ($cul['x'] ?? '') . '، Y:' . ($cul['y'] ?? '') . ')';
        }
        $culvertsStr = implode(' | ', $culvArr);
    }
    
    // نوع منبع آب
    $sourceTypeText = '';
    if (($project['water_source_type'] ?? '') === 'spring') $sourceTypeText = 'چشمه';
    elseif (($project['water_source_type'] ?? '') === 'well') $sourceTypeText = 'چاه';
    elseif (($project['water_source_type'] ?? '') === 'other') $sourceTypeText = 'سایر';
    
    // نوع فرعی پروژه
    $subTypeText = '';
    if (($project['project_type'] ?? '') === 'water') {
        $subTypeText = ($project['water_type'] ?? '') === 'linear' ? 'خطی' : 'نقطه‌ای';
    } else {
        $subTypeText = ($project['road_type'] ?? '') === 'construction' ? 'احداثی' : 'مرمت و بازگشایی';
    }
    
    // وضعیت مجوز
    $licenseStatus = '';
    if (($project['has_license'] ?? '') === 'yes') $licenseStatus = 'دارد';
    elseif (($project['has_license'] ?? '') === 'no') $licenseStatus = 'ندارد';
    
    fputcsv($output, [
        $project['id'] ?? '',
        $project['project_name'] ?? '',
        $project['full_name'] ?? $project['user'] ?? '',
        ($project['project_type'] ?? '') === 'water' ? 'تأمین آب' : 'تأمین راه',
        $project['date'] ?? '',
        $project['province'] ?? 'چهارمحال و بختیاری',
        $project['city'] ?? '',
        $project['district'] ?? '',
        $project['region'] ?? '',
        $subTypeText,
        $project['route_length'] ?? '',
        $project['rightape_length'] ?? '',
        $coordsStr,
        ($project['linear_type'] ?? '') === 'pumping' ? 'پمپاژ' : (($project['linear_type'] ?? '') === 'gravity' ? 'ثقلی' : ''),
        $project['power_distance'] ?? '',
        $project['pump_type'] ?? '',
        $project['power_needed'] ?? '',
        $project['pipe_type'] ?? '',
        $project['pipe_size'] ?? '',
        $project['water_flow'] ?? '',
        $sourceTypeText,
        $project['water_source_name'] ?? '',
        $project['license_number'] ?? '',
        $project['license_date'] ?? '',
        $project['tribe'] ?? '',
        $project['clan'] ?? '',
        $project['sub_clan'] ?? '',
        $project['households'] ?? '',
        $project['population'] ?? '',
        $project['livestock'] ?? '',
        $project['water_need'] ?? '',
        $project['credit_amount'] ?? '',
        $project['credit_source'] ?? '',
        $project['contract_number'] ?? '',
        $project['contract_date'] ?? '',
        $project['contract_amount'] ?? '',
        $project['contractor_name'] ?? '',
        $project['notification_number'] ?? '',
        $project['notification_date'] ?? '',
        $tempStatusStr,
        $project['final_amount'] ?? '',
        $project['final_date'] ?? '',
        $warningsStr,
        $project['soil_volume'] ?? '',
        $project['rock_volume'] ?? '',
        $project['description'] ?? '',
        $licenseStatus,
        $project['beneficiary_x'] ?? '',
        $project['beneficiary_y'] ?? '',
        $culvertsStr
    ]);
}

fclose($output);
exit();
?>