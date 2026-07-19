let currentProjectId = null;

function viewProject(project) {
    const modal = document.getElementById('project-modal');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    // ذخیره شناسه پروژه جاری
    currentProjectId = project.id;

    // تنظیم لینک‌های خروجی
    document.getElementById('modal-excel-btn').href = `export-excel.php?single_id=${encodeURIComponent(project.id)}`;
    document.getElementById('modal-pdf-btn').href = `export-pdf.php?single_id=${encodeURIComponent(project.id)}`;

    // عنوان مودال با نام پروژه
    title.textContent = '📋 ' + (project.project_name || 'بدون نام');

    let html = '';

    // ========== نام پروژه به صورت برجسته در بالای مودال ==========
    html += '<div class="project-name-highlight">';
    html += '🏗️ ' + (project.project_name || 'بدون نام');
    html += '</div>';

    // ========== بخش A: اطلاعات اولیه ==========
    html += '<div class="detail-section">';
    html += '<h4>📋 اطلاعات اولیه</h4>';
    html += '<div class="detail-row">';
    html += createDetailItem('کد پروژه', project.id);
    html += createDetailItem('نام و نام خانوادگی', project.full_name);
    html += createDetailItem('استان', project.province || 'چهارمحال و بختیاری');
    html += createDetailItem('شهرستان', project.city);
    html += createDetailItem('بخش', project.district);
    html += createDetailItem('منطقه عشایری', project.region);
    html += createDetailItem('نوع پروژه', project.project_type === 'water' ? '💧 تأمین آب' : '🛣️ تأمین راه');
    html += createDetailItem('تاریخ ثبت', project.date);
    html += createDetailItem('کاربر ثبت‌کننده', project.user);
    html += '</div></div>';

    // ========== اگر پروژه تأمین آب است ==========
    if (project.project_type === 'water') {

        // اطلاعات پروژه آب
        html += '<div class="detail-section">';
        html += '<h4>💧 مشخصات پروژه تأمین آب</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('نام پروژه', project.project_name, true);

        const waterTypeText = project.water_type === 'linear' ? 'خطی' : (project.water_type === 'point' ? 'نقطه‌ای' : '-');
        html += createDetailItem('نوع پروژه آب', waterTypeText);

        if (project.water_type === 'linear') {
            const linearTypeText = project.linear_type === 'pumping' ? 'پمپاژ' : (project.linear_type === 'gravity' ? 'ثقلی' : '-');
            html += createDetailItem('نوع خطی', linearTypeText);
        }

        html += createDetailItem('طول مسیر خط انتقال آب', project.route_length ? project.route_length + ' متر' : '-');
        html += createDetailItem('طول رایتاپه', project.rightape_length ? project.rightape_length + ' متر' : '-');
        html += '</div>';

        // مختصات
        if (project.coordinates && project.coordinates.length > 0) {
            html += '<h5 style="margin-top: 15px; color: #1a237e;">📍 مختصات</h5>';
            html += '<table class="detail-table"><thead><tr><th>ردیف</th><th>مختصات X</th><th>مختصات Y</th></tr></thead><tbody>';
            project.coordinates.forEach((coord, i) => {
                html += `<tr><td>${i + 1}</td><td>${coord.x || '-'}</td><td>${coord.y || '-'}</td></tr>`;
            });
            html += '</tbody></table>';
        }
        html += '</div>';

        // اگر پمپاژ است
        if (project.water_type === 'linear' && project.linear_type === 'pumping') {
            html += '<div class="detail-section">';
            html += '<h4>🔌 اطلاعات پمپاژ</h4>';
            html += '<div class="detail-row">';
            html += createDetailItem('فاصله تا منبع برق', project.power_distance ? project.power_distance + ' متر' : '-');
            html += createDetailItem('نوع پمپ', project.pump_type);
            html += createDetailItem('میزان برق مورد نیاز', project.power_needed ? project.power_needed + ' KW' : '-');
            html += createDetailItem('نوع لوله', project.pipe_type);
            html += createDetailItem('سایز لوله', project.pipe_size ? project.pipe_size + ' اینچ' : '-');
            html += '</div></div>';
        }

        // منبع تأمین آب
        html += '<div class="detail-section">';
        html += '<h4>💦 منبع تأمین آب</h4>';
        html += '<div class="detail-row">';
        const sourceTypeText = project.water_source_type === 'spring' ? 'چشمه' : (project.water_source_type === 'well' ? 'چاه' : (project.water_source_type === 'other' ? 'سایر' : '-'));
        html += createDetailItem('نوع منبع', sourceTypeText);
        html += createDetailItem('نام منبع', project.water_source_name);
        html += createDetailItem('دبی آب', project.water_flow);
        html += createDetailItem('شماره مجوز', project.license_number);
        html += createDetailItem('تاریخ مجوز', project.license_date);
        html += '</div></div>';

        // اطلاعات تکمیلی - بهره‌برداران
        html += '<div class="detail-section">';
        html += '<h4>👥 اطلاعات تکمیلی (بهره‌برداران)</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('ایل', project.tribe);
        html += createDetailItem('طایفه', project.clan);
        html += createDetailItem('تیره', project.sub_clan);
        html += createDetailItem('تعداد خانوار', project.households);
        html += createDetailItem('جمعیت', project.population);
        html += createDetailItem('تعداد دام', project.livestock);
        html += createDetailItem('نیاز آبی', project.water_need);
        html += '</div></div>';

        // اطلاعات مالی و قرارداد
        html += '<div class="detail-section">';
        html += '<h4>💰 اطلاعات مالی و قرارداد</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('مبلغ تأمین اعتبار', project.credit_amount ? Number(project.credit_amount).toLocaleString() + ' ریال' : '-');
        html += createDetailItem('محل تأمین اعتبار', project.credit_source);
        html += createDetailItem('شماره قرارداد', project.contract_number);
        html += createDetailItem('تاریخ قرارداد', project.contract_date);
        html += createDetailItem('مبلغ قرارداد', project.contract_amount ? Number(project.contract_amount).toLocaleString() + ' ریال' : '-');
        html += createDetailItem('نام پیمانکار', project.contractor_name);
        html += '</div></div>';

        // صورت‌جلسه ابلاغ ۲۵٪
        html += '<div class="detail-section">';
        html += '<h4>📄 صورت‌جلسه ابلاغ ۲۵٪</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('شماره صورت‌جلسه', project.notification_number);
        html += createDetailItem('تاریخ صورت‌جلسه', project.notification_date);
        html += '</div></div>';

        // صورت وضعیت موقت
        html += '<div class="detail-section">';
        html += '<h4>📝 صورت وضعیت موقت</h4>';
        if (project.temp_status && project.temp_status.length > 0) {
            html += '<table class="detail-table"><thead><tr><th>ردیف</th><th>مبلغ (ریال)</th><th>تاریخ</th></tr></thead><tbody>';
            project.temp_status.forEach((status, i) => {
                html += `<tr><td>${i + 1}</td><td>${status.amount ? Number(status.amount).toLocaleString() : '-'}</td><td>${status.date || '-'}</td></tr>`;
            });
            html += '</tbody></table>';
        } else {
            html += '<p class="empty-message">موردی ثبت نشده است</p>';
        }
        html += '</div>';

        // صورت وضعیت قطعی
        html += '<div class="detail-section">';
        html += '<h4>✅ صورت وضعیت قطعی</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('مبلغ (ریال)', project.final_amount ? Number(project.final_amount).toLocaleString() : '-');
        html += createDetailItem('تاریخ', project.final_date);
        html += '</div></div>';

        // اخطارهای پیمانکار
        html += '<div class="detail-section">';
        html += '<h4>⚠️ اخطارهای پیمانکار</h4>';
        if (project.warnings && project.warnings.length > 0) {
            html += '<table class="detail-table"><thead><tr><th>ردیف</th><th>شماره اخطار</th><th>تاریخ اخطار</th></tr></thead><tbody>';
            project.warnings.forEach((warning, i) => {
                html += `<tr><td>${i + 1}</td><td>${warning.number || '-'}</td><td>${warning.date || '-'}</td></tr>`;
            });
            html += '</tbody></table>';
        } else {
            html += '<p class="empty-message">اخطاری ثبت نشده است</p>';
        }
        html += '</div>';

        // برآورد حجم
        html += '<div class="detail-section">';
        html += '<h4>📐 برآورد حجم رایتاپه</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('حجم خاکی', project.soil_volume ? project.soil_volume + ' متر مکعب' : '-');
        html += createDetailItem('حجم سنگی', project.rock_volume ? project.rock_volume + ' متر مکعب' : '-');
        html += '</div></div>';

        // شرح پروژه
        if (project.description) {
            html += '<div class="detail-section">';
            html += '<h4>📝 شرح پروژه</h4>';
            html += `<p style="line-height: 1.8; white-space: pre-wrap;">${project.description}</p>`;
            html += '</div>';
        }

    // ========== اگر پروژه تأمین راه است ==========
    } else if (project.project_type === 'road') {

        // اطلاعات پروژه راه
        html += '<div class="detail-section">';
        html += '<h4>🛣️ مشخصات پروژه تأمین راه</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('نام پروژه', project.project_name, true);

        const roadTypeText = project.road_type === 'construction' ? 'احداثی' : (project.road_type === 'maintenance' ? 'مرمت و بازگشایی' : '-');
        html += createDetailItem('نوع پروژه راه', roadTypeText);

        html += createDetailItem('طول مسیر', project.route_length ? project.route_length + ' متر' : '-');
        html += createDetailItem('طول رایتاپه', project.rightape_length ? project.rightape_length + ' متر' : '-');
        html += '</div>';

        // مختصات
        if (project.coordinates && project.coordinates.length > 0) {
            html += '<h5 style="margin-top: 15px; color: #1a237e;">📍 مختصات</h5>';
            html += '<table class="detail-table"><thead><tr><th>ردیف</th><th>مختصات X</th><th>مختصات Y</th></tr></thead><tbody>';
            project.coordinates.forEach((coord, i) => {
                html += `<tr><td>${i + 1}</td><td>${coord.x || '-'}</td><td>${coord.y || '-'}</td></tr>`;
            });
            html += '</tbody></table>';
        }
        html += '</div>';

        // اگر احداثی است
        if (project.road_type === 'construction') {
            html += '<div class="detail-section">';
            html += '<h4>📋 اطلاعات احداثی</h4>';
            html += '<div class="detail-row">';

            const licenseText = project.has_license === 'yes' ? '✅ دارد' : (project.has_license === 'no' ? '❌ ندارد' : '-');
            html += createDetailItem('وضعیت مجوز', licenseText);
            html += createDetailItem('شماره مجوز', project.license_number);
            html += createDetailItem('تاریخ مجوز', project.license_date);
            html += '</div>';

            // مشخصات بهره‌برداران
            html += '<h5 style="margin-top: 15px; color: #1a237e;">👥 مشخصات بهره‌برداران</h5>';
            html += '<div class="detail-row">';
            html += createDetailItem('مختصات X', project.beneficiary_x);
            html += createDetailItem('مختصات Y', project.beneficiary_y);
            html += createDetailItem('ایل', project.tribe);
            html += createDetailItem('طایفه', project.clan);
            html += createDetailItem('تیره', project.sub_clan);
            html += createDetailItem('تعداد خانوار', project.households);
            html += createDetailItem('تعداد دام', project.livestock);
            html += '</div></div>';
        }

        // آبروها
        html += '<div class="detail-section">';
        html += '<h4>🌊 بخش ابنیه فنی (آبروها)</h4>';
        if (project.culverts && project.culverts.length > 0) {
            html += '<table class="detail-table"><thead><tr><th>ردیف</th><th>نوع آبرو</th><th>طول دهانه</th><th>مختصات X</th><th>مختصات Y</th></tr></thead><tbody>';
            project.culverts.forEach((culvert, i) => {
                html += `<tr><td>${i + 1}</td><td>${culvert.type || '-'}</td><td>${culvert.length || '-'}</td><td>${culvert.x || '-'}</td><td>${culvert.y || '-'}</td></tr>`;
            });
            html += '</tbody></table>';
        } else {
            html += '<p class="empty-message">آبرویی ثبت نشده است</p>';
        }
        html += '</div>';

        // برآورد حجم
        html += '<div class="detail-section">';
        html += '<h4>📐 برآورد حجم رایتاپه</h4>';
        html += '<div class="detail-row">';
        html += createDetailItem('حجم خاکی', project.soil_volume ? project.soil_volume + ' متر مکعب' : '-');
        html += createDetailItem('حجم سنگی', project.rock_volume ? project.rock_volume + ' متر مکعب' : '-');
        html += '</div></div>';
    }

    body.innerHTML = html;
    modal.classList.add('active');

    // اسکرول به بالای مودال
    document.querySelector('.modal-body').scrollTop = 0;
}

// تابع کمکی برای ایجاد آیتم‌های جزئیات
function createDetailItem(label, value, isImportant = false) {
    const displayValue = value || '-';
    const style = isImportant ? 'style="font-size: 16px; color: #1a237e;"' : '';
    return `<div class="detail-item"><label>${label}</label><span ${style}>${displayValue}</span></div>`;
}

function closeModal() {
    document.getElementById('project-modal').classList.remove('active');
    currentProjectId = null;
}

// بستن مودال با کلیک خارج از آن
window.onclick = function(event) {
    const modal = document.getElementById('project-modal');
    if (event.target === modal) {
        closeModal();
    }
}

// بستن مودال با کلید Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

