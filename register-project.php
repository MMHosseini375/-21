<?php
session_start();

// بررسی دسترسی کاربر عادی
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'user') {
    header('Location: index.php');
    exit();
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project = [];
    
    // بخش A: اطلاعات اولیه
    $project['full_name'] = trim($_POST['full_name'] ?? '');
    $project['province'] = 'چهارمحال و بختیاری';
    $project['city'] = trim($_POST['city'] ?? '');
    $project['district'] = trim($_POST['district'] ?? '');
    $project['region'] = trim($_POST['region'] ?? '');
    
    // بخش B: نوع پروژه
    $project['project_type'] = $_POST['project_type'] ?? '';
    
    // ============ فیلدهای مشترک بین آب و راه ============
    $project['project_name'] = trim($_POST['project_name'] ?? '');
    $project['route_length'] = trim($_POST['route_length'] ?? '');
    $project['rightape_length'] = trim($_POST['rightape_length'] ?? '');
    
    // مختصات (مشترک)
    if (!empty($_POST['coordinates_json'])) {
        $project['coordinates'] = json_decode($_POST['coordinates_json'], true) ?: [];
    } else {
        $project['coordinates'] = [];
        $coordX = $_POST['coord_x'] ?? [];
        $coordY = $_POST['coord_y'] ?? [];
        if (is_array($coordX) && is_array($coordY)) {
            foreach ($coordX as $i => $x) {
                if (!empty($x) || !empty($coordY[$i] ?? '')) {
                    $project['coordinates'][] = ['x' => trim($x), 'y' => trim($coordY[$i] ?? '')];
                }
            }
        }
    }
    
    // ============ اطلاعات تکمیلی (بهره‌برداران) - مشترک ============
    $project['tribe'] = trim($_POST['tribe'] ?? '');
    $project['clan'] = trim($_POST['clan'] ?? '');
    $project['sub_clan'] = trim($_POST['sub_clan'] ?? '');
    $project['households'] = trim($_POST['households'] ?? '');
    $project['population'] = trim($_POST['population'] ?? '');
    $project['livestock'] = trim($_POST['livestock'] ?? '');
    
    // ============ اطلاعات مالی و قرارداد - مشترک ============
    $project['credit_amount'] = trim($_POST['credit_amount'] ?? '');
    $project['credit_source'] = trim($_POST['credit_source'] ?? '');
    $project['contract_number'] = trim($_POST['contract_number'] ?? '');
    $project['contract_date'] = trim($_POST['contract_date'] ?? '');
    $project['contract_amount'] = trim($_POST['contract_amount'] ?? '');
    $project['contractor_name'] = trim($_POST['contractor_name'] ?? '');
    $project['contract_duration'] = trim($_POST['contract_duration'] ?? ''); // جدید: مدت قرارداد به روز
    
    // ============ صورت‌جلسه ابلاغ ۲۵٪ - مشترک ============
    $project['notification_number'] = trim($_POST['notification_number'] ?? '');
    $project['notification_date'] = trim($_POST['notification_date'] ?? '');
    
    // ============ صورت وضعیت موقت - مشترک ============
    if (!empty($_POST['temp_status_json'])) {
        $project['temp_status'] = json_decode($_POST['temp_status_json'], true) ?: [];
    } else {
        $project['temp_status'] = [];
        $tempAmount = $_POST['temp_amount'] ?? [];
        $tempDate = $_POST['temp_date'] ?? [];
        if (is_array($tempAmount) && is_array($tempDate)) {
            foreach ($tempAmount as $i => $amount) {
                if (!empty($amount) || !empty($tempDate[$i] ?? '')) {
                    $project['temp_status'][] = ['amount' => trim($amount), 'date' => trim($tempDate[$i] ?? '')];
                }
            }
        }
    }
    
    // ============ صورت وضعیت قطعی - مشترک ============
    $project['final_amount'] = trim($_POST['final_amount'] ?? '');
    $project['final_date'] = trim($_POST['final_date'] ?? '');
    
    // ============ اخطارهای پیمانکار - مشترک ============
    $project['warnings'] = [];
    for ($i = 1; $i <= 3; $i++) {
        $warnNum = trim($_POST["warning_number_$i"] ?? '');
        $warnDate = trim($_POST["warning_date_$i"] ?? '');
        if (!empty($warnNum) || !empty($warnDate)) {
            $project['warnings'][] = ['number' => $warnNum, 'date' => $warnDate];
        }
    }
    
    // ============ برآورد حجم - مشترک ============
    $project['soil_volume'] = trim($_POST['soil_volume'] ?? '');
    $project['rock_volume'] = trim($_POST['rock_volume'] ?? '');
    
    if ($project['project_type'] === 'water') {
        // ============ اطلاعات خاص پروژه آب ============
        $project['water_type'] = $_POST['water_type'] ?? '';
        
        if ($project['water_type'] === 'linear') {
            $project['linear_type'] = $_POST['linear_type'] ?? '';
            if ($project['linear_type'] === 'pumping') {
                $project['power_distance'] = trim($_POST['power_distance'] ?? '');
                $project['pump_type'] = trim($_POST['pump_type'] ?? '');
                $project['power_needed'] = trim($_POST['power_needed'] ?? '');
                $project['pipe_type'] = trim($_POST['pipe_type'] ?? '');
                $project['pipe_size'] = trim($_POST['pipe_size'] ?? '');
            }
        }
        
        $project['water_flow'] = trim($_POST['water_flow'] ?? '');
        $project['water_source_type'] = $_POST['water_source_type'] ?? '';
        $project['water_source_name'] = trim($_POST['water_source_name'] ?? '');
        $project['license_number'] = trim($_POST['license_number'] ?? '');
        $project['license_date'] = trim($_POST['license_date'] ?? '');
        $project['water_need'] = trim($_POST['water_need'] ?? '');
        $project['description'] = trim($_POST['description'] ?? '');
        
    } elseif ($project['project_type'] === 'road') {
        // ============ اطلاعات خاص پروژه راه ============
        $project['road_type'] = $_POST['road_type'] ?? '';
        
        if ($project['road_type'] === 'construction') {
            $project['has_license'] = $_POST['has_license'] ?? '';
            $project['license_number'] = trim($_POST['license_number'] ?? '');
            $project['license_date'] = trim($_POST['license_date'] ?? '');
            $project['beneficiary_x'] = trim($_POST['beneficiary_x'] ?? '');
            $project['beneficiary_y'] = trim($_POST['beneficiary_y'] ?? '');
        }
        
        // آبروها
        if (!empty($_POST['culverts_json'])) {
            $project['culverts'] = json_decode($_POST['culverts_json'], true) ?: [];
        } else {
            $project['culverts'] = [];
            $culvertType = $_POST['culvert_type'] ?? [];
            $culvertLength = $_POST['culvert_length'] ?? [];
            $culvertX = $_POST['culvert_x'] ?? [];
            $culvertY = $_POST['culvert_y'] ?? [];
            if (is_array($culvertType)) {
                foreach ($culvertType as $i => $type) {
                    if (!empty($type)) {
                        $project['culverts'][] = [
                            'type' => trim($type),
                            'length' => trim($culvertLength[$i] ?? ''),
                            'x' => trim($culvertX[$i] ?? ''),
                            'y' => trim($culvertY[$i] ?? '')
                        ];
                    }
                }
            }
        }
    }
    
    // ذخیره پروژه
    $project['id'] = uniqid('PRJ-');
    $project['date'] = date('Y-m-d H:i:s');
    $project['user'] = $_SESSION['username'];
    
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    
    $projectsFile = $dataDir . '/projects.json';
    $projects = [];
    if (file_exists($projectsFile)) {
        $projects = json_decode(file_get_contents($projectsFile), true) ?: [];
    }
    
    $projects[] = $project;
    
    if (file_put_contents($projectsFile, json_encode($projects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        $success = true;
        $_POST = [];
    } else {
        $error = 'خطا در ذخیره‌سازی پروژه.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت پروژه - اداره کل امور عشایر چهارمحال و بختیاری</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tahoma', 'IRANSans', Arial, sans-serif; background: #f5f5f5; color: #333; }
        .header { background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); flex-wrap: wrap; }
        .header h1 { font-size: 20px; display: flex; align-items: center; gap: 10px; }
        .header-actions { display: flex; gap: 10px; align-items: center; }
        .user-info { color: #c9a84c; font-weight: bold; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold; text-decoration: none; transition: all 0.3s; font-family: 'Tahoma', 'IRANSans', Arial, sans-serif; display: inline-block; }
        .btn-gold { background: #c9a84c; color: #1a237e; }
        .btn-gold:hover { background: #b8943d; transform: translateY(-2px); }
        .btn-red { background: #dc3545; color: white; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 30px; }
        .card-header { background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 20px; border-radius: 12px; margin: -30px -30px 30px -30px; font-size: 18px; font-weight: bold; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #1a237e; font-weight: bold; font-size: 14px; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.3s; font-family: 'Tahoma', 'IRANSans', Arial, sans-serif; }
        .form-control:focus { outline: none; border-color: #c9a84c; box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        select.form-control { background: white; cursor: pointer; }
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .btn-add { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; margin: 10px 0; transition: all 0.3s; font-family: 'Tahoma', 'IRANSans', Arial, sans-serif; }
        .btn-add:hover { background: #218838; }
        .btn-remove { background: #dc3545; color: white; padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; transition: all 0.3s; font-family: 'Tahoma', 'IRANSans', Arial, sans-serif; }
        .btn-remove:hover { background: #c82333; }
        .btn-submit { background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 15px 40px; border: none; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 20px; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(26,35,126,0.3); }
        .coordinate-row, .temp-status-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
        .coordinate-row input, .temp-status-row input { flex: 1; min-width: 150px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background: #1a237e; color: white; }
        .success-message { background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; text-align: center; font-size: 18px; margin-bottom: 20px; border-right: 5px solid #28a745; }
        .error-message { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; text-align: center; font-size: 18px; margin-bottom: 20px; border-right: 5px solid #dc3545; }
        .hidden { display: none; }
        h4 { color: #1a237e; margin: 30px 0 15px 0; padding: 10px 15px; background: #f0f0f0; border-radius: 8px; border-right: 4px solid #c9a84c; }
        @media (max-width: 768px) { .header { flex-direction: column; text-align: center; gap: 15px; } .card { padding: 20px; } .card-header { margin: -20px -20px 20px -20px; padding: 15px; font-size: 16px; } .row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1><span>🏛️</span> اداره کل امور عشایر استان چهارمحال و بختیاری</h1>
        <div class="header-actions">
            <span class="user-info">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn btn-red">خروج</a>
        </div>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="success-message">
                ✅ پروژه با موفقیت ثبت شد!
                <br><br>
                <a href="register-project.php" class="btn btn-gold" style="margin-top: 10px;">📝 ثبت پروژه جدید</a>
                <a href="index.php" class="btn btn-red" style="margin-top: 10px;">🏠 بازگشت به صفحه اصلی</a>
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error); ?>
                <br><br>
                <a href="register-project.php" class="btn btn-gold" style="margin-top: 10px;">🔄 تلاش مجدد</a>
            </div>
        <?php else: ?>
            <form method="POST" action="" id="project-form" onsubmit="return prepareFormData()">
                
                <!-- ============ بخش A: اطلاعات اولیه ============ -->
                <div class="card">
                    <div class="card-header">📋 بخش A: اطلاعات اولیه</div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label>نام و نام خانوادگی کاربر *</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="نام و نام خانوادگی خود را وارد کنید">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group">
                            <label>استان</label>
                            <input type="text" class="form-control" value="چهارمحال و بختیاری" readonly style="background: #f0f0f0; font-weight: bold;">
                        </div>
                        
                        <div class="form-group">
                            <label>شهرستان *</label>
                            <select name="city" id="city" class="form-control" required>
                                <option value="">انتخاب کنید</option>
                                <option value="شهرکرد">شهرکرد</option>
                                <option value="لردگان">لردگان</option>
                                <option value="بروجن">بروجن</option>
                                <option value="فارسان">فارسان</option>
                                <option value="خانمیرزا">خانمیرزا</option>
                                <option value="کیار">کیار</option>
                                <option value="اردل">اردل</option>
                                <option value="کوهرنگ">کوهرنگ</option>
                                <option value="فرخشهر">فرخشهر</option>
                                <option value="سامان">سامان</option>
                                <option value="فلارد">فلارد</option>
                                <option value="بن">بن</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>بخش *</label>
                            <input type="text" name="district" class="form-control" required placeholder="نام بخش را وارد کنید">
                        </div>
                        
                        <div class="form-group">
                            <label>منطقه عشایری *</label>
                            <input type="text" name="region" class="form-control" required placeholder="نام منطقه عشایری را وارد کنید">
                        </div>
                    </div>
                </div>

                <!-- ============ بخش B: نوع پروژه ============ -->
                <div class="card">
                    <div class="card-header">⚙️ بخش B: نوع پروژه</div>
                    
                    <div class="form-group">
                        <label>نوع پروژه *</label>
                        <select name="project_type" id="project_type" class="form-control" required>
                            <option value="">انتخاب کنید</option>
                            <option value="water">💧 تأمین آب</option>
                            <option value="road">🛣️ تأمین راه</option>
                        </select>
                    </div>
                    
                    <!-- ============ فیلدهای مشترک (همیشه نمایش داده می‌شوند) ============ -->
                    <div id="common-section" class="hidden">
                        
                        <h4>📝 مشخصات پروژه</h4>
                        
                        <div class="form-group">
                            <label>نام پروژه *</label>
                            <input type="text" name="project_name" id="project_name" class="form-control required-field" placeholder="نام پروژه را وارد کنید">
                        </div>
                        
                        <div class="row">
                            <div class="form-group">
                                <label id="route_length_label">طول مسیر (متر)</label>
                                <input type="text" name="route_length" class="form-control" placeholder="مثال: 1500">
                            </div>
                            <div class="form-group">
                                <label id="rightape_length_label">طول رایتاپه (متر)</label>
                                <input type="text" name="rightape_length" class="form-control" placeholder="مثال: 800">
                            </div>
                        </div>
                        
                        <!-- مختصات پویا -->
                        <div class="form-group">
                            <label>📍 مختصات</label>
                            <div id="coordinates-container"></div>
                            <button type="button" class="btn-add" onclick="addCoordinate()">+ افزودن مختصات</button>
                            <input type="hidden" name="coordinates_json" id="coordinates_json">
                        </div>
                        
                        <!-- ========== بخش خاص آب ========== -->
                        <div id="water-specific-section" class="hidden">
                            <div class="form-group">
                                <label>نوع پروژه آب *</label>
                                <select name="water_type" id="water_type" class="form-control water-required">
                                    <option value="">انتخاب کنید</option>
                                    <option value="point">نقطه‌ای</option>
                                    <option value="linear">خطی</option>
                                </select>
                            </div>
                            
                            <div id="linear-section" class="hidden">
                                <div class="form-group">
                                    <label>نوع خطی *</label>
                                    <select name="linear_type" id="linear_type" class="form-control water-required">
                                        <option value="">انتخاب کنید</option>
                                        <option value="gravity">ثقلی</option>
                                        <option value="pumping">پمپاژ</option>
                                    </select>
                                </div>
                                
                                <div id="pumping-section" class="hidden">
                                    <h4>🔌 اطلاعات پمپاژ</h4>
                                    <div class="row">
                                        <div class="form-group"><label>فاصله تا منبع برق (متر)</label><input type="text" name="power_distance" class="form-control"></div>
                                        <div class="form-group"><label>نوع پمپ</label><input type="text" name="pump_type" class="form-control" placeholder="مثال: شناور"></div>
                                        <div class="form-group"><label>میزان برق مورد نیاز (KW)</label><input type="text" name="power_needed" class="form-control" placeholder="مثال: 15"></div>
                                        <div class="form-group"><label>نوع لوله</label><input type="text" name="pipe_type" class="form-control" placeholder="مثال: پلی اتیلن"></div>
                                        <div class="form-group"><label>سایز لوله (اینچ)</label><input type="text" name="pipe_size" class="form-control" placeholder="مثال: 4"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <h4>💦 منبع تأمین آب</h4>
                            <div class="form-group"><label>دبی آب</label><input type="text" name="water_flow" class="form-control" placeholder="مثال: 10 لیتر بر ثانیه"></div>
                            <div class="row">
                                <div class="form-group"><label>نوع منبع تأمین آب</label><select name="water_source_type" class="form-control"><option value="">انتخاب کنید</option><option value="spring">چشمه</option><option value="well">چاه</option><option value="other">سایر</option></select></div>
                                <div class="form-group"><label>نام منبع</label><input type="text" name="water_source_name" class="form-control" placeholder="نام چشمه یا چاه"></div>
                                <div class="form-group"><label>شماره مجوز</label><input type="text" name="license_number" class="form-control" placeholder="شماره مجوز بهره‌برداری"></div>
                                <div class="form-group"><label>تاریخ مجوز</label><input type="date" name="license_date" class="form-control"></div>
                            </div>
                            <div class="form-group"><label>نیاز آبی</label><input type="text" name="water_need" class="form-control" placeholder="مثال: 5000 متر مکعب"></div>
                            
                            <div class="form-group">
                                <label>📝 شرح پروژه</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="توضیحات کامل پروژه را وارد کنید..."></textarea>
                            </div>
                        </div>
                        
                        <!-- ========== بخش خاص راه ========== -->
                        <div id="road-specific-section" class="hidden">
                            <div class="form-group">
                                <label>نوع پروژه راه *</label>
                                <select name="road_type" id="road_type" class="form-control road-required">
                                    <option value="">انتخاب کنید</option>
                                    <option value="maintenance">مرمت و بازگشایی</option>
                                    <option value="construction">احداثی</option>
                                </select>
                            </div>
                            
                            <div id="construction-section" class="hidden">
                                <h4>📋 اطلاعات احداثی</h4>
                                <div class="row">
                                    <div class="form-group"><label>وضعیت مجوز</label><select name="has_license" class="form-control"><option value="">انتخاب کنید</option><option value="yes">دارد</option><option value="no">ندارد</option></select></div>
                                    <div class="form-group"><label>شماره مجوز</label><input type="text" name="license_number" class="form-control" placeholder="شماره مجوز"></div>
                                    <div class="form-group"><label>تاریخ مجوز</label><input type="date" name="license_date" class="form-control"></div>
                                </div>
                                
                                <h4>👥 مشخصات بهره‌برداران</h4>
                                <div class="row">
                                    <div class="form-group"><label>مختصات X</label><input type="text" name="beneficiary_x" class="form-control" placeholder="مختصات X"></div>
                                    <div class="form-group"><label>مختصات Y</label><input type="text" name="beneficiary_y" class="form-control" placeholder="مختصات Y"></div>
                                </div>
                            </div>
                            
                            <h4>🌊 بخش ابنیه فنی (آبروها)</h4>
                            <table id="culverts-table">
                                <thead><tr><th>نوع آبرو</th><th>طول دهانه</th><th>مختصات X</th><th>مختصات Y</th><th>عملیات</th></tr></thead>
                                <tbody id="culverts-tbody"></tbody>
                            </table>
                            <button type="button" class="btn-add" onclick="addCulvert()">+ افزودن آبرو</button>
                            <input type="hidden" name="culverts_json" id="culverts_json">
                        </div>
                        
                        <!-- ========== اطلاعات تکمیلی - مشترک ========== -->
                        <h4>👥 اطلاعات تکمیلی (بهره‌برداران)</h4>
                        <div class="row">
                            <div class="form-group"><label>ایل</label><input type="text" name="tribe" class="form-control" placeholder="نام ایل"></div>
                            <div class="form-group"><label>طایفه</label><input type="text" name="clan" class="form-control" placeholder="نام طایفه"></div>
                            <div class="form-group"><label>تیره</label><input type="text" name="sub_clan" class="form-control" placeholder="نام تیره"></div>
                            <div class="form-group"><label>تعداد خانوار</label><input type="number" name="households" class="form-control" placeholder="مثال: 50"></div>
                            <div class="form-group"><label>جمعیت</label><input type="number" name="population" class="form-control" placeholder="مثال: 250"></div>
                            <div class="form-group"><label>تعداد دام</label><input type="number" name="livestock" class="form-control" placeholder="مثال: 1000"></div>
                        </div>
                        
                        <!-- ========== اطلاعات مالی و قرارداد - مشترک ========== -->
                        <h4>💰 اطلاعات مالی و قرارداد</h4>
                        <div class="row">
                            <div class="form-group"><label>مبلغ تأمین اعتبار (ریال)</label><input type="text" name="credit_amount" class="form-control" placeholder="مبلغ به ریال"></div>
                            <div class="form-group"><label>محل تأمین اعتبار</label><input type="text" name="credit_source" class="form-control" placeholder="منبع اعتبار"></div>
                            <div class="form-group"><label>شماره قرارداد</label><input type="text" name="contract_number" class="form-control" placeholder="شماره قرارداد"></div>
                            <div class="form-group"><label>تاریخ قرارداد</label><input type="date" name="contract_date" class="form-control"></div>
                            <div class="form-group"><label>مبلغ قرارداد (ریال)</label><input type="text" name="contract_amount" class="form-control" placeholder="مبلغ قرارداد"></div>
                            <div class="form-group"><label>مدت قرارداد (روز)</label><input type="text" name="contract_duration" class="form-control" placeholder="مدت قرارداد به روز"></div>
                            <div class="form-group"><label>نام پیمانکار</label><input type="text" name="contractor_name" class="form-control" placeholder="نام کامل پیمانکار"></div>
                        </div>
                        
                        <!-- ========== صورت‌جلسه ابلاغ ۲۵٪ - مشترک ========== -->
                        <h4>📄 صورت‌جلسه ابلاغ ۲۵٪</h4>
                        <div class="row">
                            <div class="form-group"><label>شماره صورت‌جلسه</label><input type="text" name="notification_number" class="form-control" placeholder="شماره"></div>
                            <div class="form-group"><label>تاریخ صورت‌جلسه</label><input type="date" name="notification_date" class="form-control"></div>
                        </div>
                        
                        <!-- ========== صورت وضعیت موقت - مشترک ========== -->
                        <h4>📝 صورت وضعیت موقت</h4>
                        <div id="temp-status-container"></div>
                        <button type="button" class="btn-add" onclick="addTempStatus()">+ افزودن صورت وضعیت</button>
                        <input type="hidden" name="temp_status_json" id="temp_status_json">
                        
                        <!-- ========== صورت وضعیت قطعی - مشترک ========== -->
                        <h4>✅ صورت وضعیت قطعی</h4>
                        <div class="row">
                            <div class="form-group"><label>مبلغ (ریال)</label><input type="text" name="final_amount" class="form-control" placeholder="مبلغ نهایی"></div>
                            <div class="form-group"><label>تاریخ</label><input type="date" name="final_date" class="form-control"></div>
                        </div>
                        
                        <!-- ========== اخطارهای پیمانکار - مشترک ========== -->
                        <h4>⚠️ اخطارهای پیمانکار</h4>
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="form-group"><label>شماره اخطار <?php echo $i; ?></label><input type="text" name="warning_number_<?php echo $i; ?>" class="form-control" placeholder="شماره اخطار"></div>
                            <div class="form-group"><label>تاریخ اخطار <?php echo $i; ?></label><input type="date" name="warning_date_<?php echo $i; ?>" class="form-control"></div>
                        </div>
                        <?php endfor; ?>
                        
                        <!-- ========== برآورد حجم - مشترک ========== -->
                        <h4>📐 برآورد حجم <span id="volume_title_suffix">عملیات خاکی</span></h4>
                        <div class="row">
                            <div class="form-group"><label>حجم خاکی (متر مکعب)</label><input type="text" name="soil_volume" class="form-control" placeholder="حجم خاکی"></div>
                            <div class="form-group"><label>حجم سنگی (متر مکعب)</label><input type="text" name="rock_volume" class="form-control" placeholder="حجم سنگی"></div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">📝 ثبت پروژه</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // ==================== نمایش/مخفی کردن بخش‌ها ====================
        document.getElementById('project_type').addEventListener('change', function() {
            const commonSection = document.getElementById('common-section');
            const waterSpecific = document.getElementById('water-specific-section');
            const roadSpecific = document.getElementById('road-specific-section');
            const linearSection = document.getElementById('linear-section');
            const pumpingSection = document.getElementById('pumping-section');
            const constructionSection = document.getElementById('construction-section');
            const routeLabel = document.getElementById('route_length_label');
            const rightapeLabel = document.getElementById('rightape_length_label');
            const volumeTitle = document.getElementById('volume_title_suffix');
            
            if (this.value === 'water') {
                commonSection.classList.remove('hidden');
                waterSpecific.classList.remove('hidden');
                roadSpecific.classList.add('hidden');
                linearSection.classList.add('hidden');
                pumpingSection.classList.add('hidden');
                routeLabel.textContent = 'طول مسیر خط انتقال آب (متر)';
                rightapeLabel.textContent = 'طول رایتاپه (متر)';
                volumeTitle.textContent = 'رایتاپه';
            } else if (this.value === 'road') {
                commonSection.classList.remove('hidden');
                roadSpecific.classList.remove('hidden');
                waterSpecific.classList.add('hidden');
                constructionSection.classList.add('hidden');
                routeLabel.textContent = 'طول مسیر (متر)';
                rightapeLabel.textContent = 'طول رایتاپه (متر)';
                volumeTitle.textContent = 'عملیات خاکی';
            } else {
                commonSection.classList.add('hidden');
                waterSpecific.classList.add('hidden');
                roadSpecific.classList.add('hidden');
            }
        });

        document.getElementById('water_type').addEventListener('change', function() {
            const linearSection = document.getElementById('linear-section');
            const pumpingSection = document.getElementById('pumping-section');
            if (this.value === 'linear') {
                linearSection.classList.remove('hidden');
            } else {
                linearSection.classList.add('hidden');
                pumpingSection.classList.add('hidden');
                document.getElementById('linear_type').value = '';
            }
        });

        document.getElementById('linear_type').addEventListener('change', function() {
            const pumpingSection = document.getElementById('pumping-section');
            if (this.value === 'pumping') {
                pumpingSection.classList.remove('hidden');
            } else {
                pumpingSection.classList.add('hidden');
            }
        });

        document.getElementById('road_type').addEventListener('change', function() {
            const constructionSection = document.getElementById('construction-section');
            if (this.value === 'construction') {
                constructionSection.classList.remove('hidden');
            } else {
                constructionSection.classList.add('hidden');
            }
        });

        // ==================== مختصات ====================
        function addCoordinate() {
            const container = document.getElementById('coordinates-container');
            const div = document.createElement('div');
            div.className = 'coordinate-row';
            div.innerHTML = `
                <input type="text" name="coord_x[]" placeholder="مختصات X" class="form-control coord-x">
                <input type="text" name="coord_y[]" placeholder="مختصات Y" class="form-control coord-y">
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">🗑️ حذف</button>
            `;
            container.appendChild(div);
        }

        // ==================== صورت وضعیت موقت ====================
        function addTempStatus() {
            const container = document.getElementById('temp-status-container');
            const div = document.createElement('div');
            div.className = 'temp-status-row';
            div.innerHTML = `
                <input type="text" name="temp_amount[]" placeholder="مبلغ (ریال)" class="form-control temp-amount">
                <input type="date" name="temp_date[]" class="form-control temp-date">
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()">🗑️ حذف</button>
            `;
            container.appendChild(div);
        }

        // ==================== آبروها ====================
        function addCulvert() {
            const tbody = document.getElementById('culverts-tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="culvert_type[]" class="form-control culvert-type" placeholder="نوع آبرو"></td>
                <td><input type="text" name="culvert_length[]" class="form-control culvert-length" placeholder="طول دهانه"></td>
                <td><input type="text" name="culvert_x[]" class="form-control culvert-x" placeholder="X"></td>
                <td><input type="text" name="culvert_y[]" class="form-control culvert-y" placeholder="Y"></td>
                <td><button type="button" class="btn-remove" onclick="this.closest('tr').remove()">🗑️ حذف</button></td>
            `;
            tbody.appendChild(tr);
        }

        // ==================== آماده‌سازی داده‌ها قبل از ارسال ====================
        function prepareFormData() {
            const projectType = document.getElementById('project_type').value;
            
            // جمع‌آوری مختصات
            const coords = [];
            document.querySelectorAll('#coordinates-container .coordinate-row').forEach(row => {
                const x = row.querySelector('.coord-x')?.value || '';
                const y = row.querySelector('.coord-y')?.value || '';
                if (x || y) coords.push({ x: x, y: y });
            });
            document.getElementById('coordinates_json').value = JSON.stringify(coords);
            
            // جمع‌آوری صورت وضعیت موقت
            const tempStatuses = [];
            document.querySelectorAll('#temp-status-container .temp-status-row').forEach(row => {
                const amount = row.querySelector('.temp-amount')?.value || '';
                const date = row.querySelector('.temp-date')?.value || '';
                if (amount || date) tempStatuses.push({ amount: amount, date: date });
            });
            document.getElementById('temp_status_json').value = JSON.stringify(tempStatuses);
            
            if (projectType === 'road') {
                // جمع‌آوری آبروها
                const culverts = [];
                document.querySelectorAll('#culverts-tbody tr').forEach(row => {
                    const type = row.querySelector('.culvert-type')?.value || '';
                    const length = row.querySelector('.culvert-length')?.value || '';
                    const x = row.querySelector('.culvert-x')?.value || '';
                    const y = row.querySelector('.culvert-y')?.value || '';
                    if (type) culverts.push({ type: type, length: length, x: x, y: y });
                });
                document.getElementById('culverts_json').value = JSON.stringify(culverts);
            }
            
            return true;
        }
    </script>
</body>
</html>