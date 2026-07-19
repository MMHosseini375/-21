// ==================== داده‌های آبشاری ====================
const locationData = {
    'شهرکرد': {
        'بخش مرکزی': ['منطقه عشایری ۱', 'منطقه عشایری ۲', 'منطقه عشایری ۳'],
        'بخش لاران': ['منطقه عشایری ۴', 'منطقه عشایری ۵']
    },
    'بروجن': {
        'بخش مرکزی': ['منطقه عشایری ۶', 'منطقه عشایری ۷'],
        'بخش گندمان': ['منطقه عشایری ۸', 'منطقه عشایری ۹']
    },
    'فارسان': {
        'بخش مرکزی': ['منطقه عشایری ۱۰', 'منطقه عشایری ۱۱'],
        'بخش باباحیدر': ['منطقه عشایری ۱۲'],
        'بخش جونقان': ['منطقه عشایری ۱۳']
    },
    'کوهرنگ': {
        'بخش مرکزی': ['منطقه عشایری ۱۴', 'منطقه عشایری ۱۵'],
        'بخش بازفت': ['منطقه عشایری ۱۶', 'منطقه عشایری ۱۷']
    },
    'اردل': {
        'بخش مرکزی': ['منطقه عشایری ۱۸', 'منطقه عشایری ۱۹'],
        'بخش میانکوه': ['منطقه عشایری ۲۰']
    },
    'لردگان': {
        'بخش مرکزی': ['منطقه عشایری ۲۱', 'منطقه عشایری ۲۲'],
        'بخش خانمیرزا': ['منطقه عشایری ۲۳'],
        'بخش فلارد': ['منطقه عشایری ۲۴']
    },
    'کیار': {
        'بخش مرکزی': ['منطقه عشایری ۲۵'],
        'بخش ناغان': ['منطقه عشایری ۲۶']
    },
    'بن': {
        'بخش مرکزی': ['منطقه عشایری ۲۷'],
        'بخش شیدا': ['منطقه عشایری ۲۸']
    },
    'سامان': {
        'بخش مرکزی': ['منطقه عشایری ۲۹']
    }
};

// پر کردن شهرستان‌ها
const citySelect = document.getElementById('city');
if (citySelect) {
    for (const city in locationData) {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        citySelect.appendChild(option);
    }
}

citySelect.addEventListener('change', function() {
    const districtSelect = document.getElementById('district');
    const regionSelect = document.getElementById('region');

    districtSelect.innerHTML = '<option value="">انتخاب کنید</option>';
    regionSelect.innerHTML = '<option value="">ابتدا بخش را انتخاب کنید</option>';
    regionSelect.disabled = true;

    if (this.value && locationData[this.value]) {
        districtSelect.disabled = false;
        for (const district in locationData[this.value]) {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        }
    } else {
        districtSelect.disabled = true;
    }
});

document.getElementById('district').addEventListener('change', function() {
    const regionSelect = document.getElementById('region');
    const city = document.getElementById('city').value;

    regionSelect.innerHTML = '<option value="">انتخاب کنید</option>';

    if (this.value && city && locationData[city] && locationData[city][this.value]) {
        regionSelect.disabled = false;
        locationData[city][this.value].forEach(region => {
            const option = document.createElement('option');
            option.value = region;
            option.textContent = region;
            regionSelect.appendChild(option);
        });
    } else {
        regionSelect.disabled = true;
    }
});

// ==================== نمایش/مخفی کردن بخش‌ها ====================
document.getElementById('project_type').addEventListener('change', function() {
    const waterSection = document.getElementById('water-section');
    const roadSection = document.getElementById('road-section');

    if (this.value === 'water') {
        waterSection.classList.remove('hidden');
        roadSection.classList.add('hidden');
    } else if (this.value === 'road') {
        roadSection.classList.remove('hidden');
        waterSection.classList.add('hidden');
    } else {
        waterSection.classList.add('hidden');
        roadSection.classList.add('hidden');
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

// ==================== مختصات تأمین آب ====================
let waterCoordCount = 0;

function addWaterCoordinate() {
    waterCoordCount++;
    const container = document.getElementById('water-coordinates-container');
    const div = document.createElement('div');
    div.className = 'coordinate-row';
    div.setAttribute('data-coord-id', waterCoordCount);
    div.innerHTML = `
        <input type="text" name="coord_x_water[]" placeholder="مختصات X" class="form-control coord-x-water">
        <input type="text" name="coord_y_water[]" placeholder="مختصات Y" class="form-control coord-y-water">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">🗑️ حذف</button>
    `;
    container.appendChild(div);
}

// ==================== مختصات تأمین راه ====================
let roadCoordCount = 0;

function addRoadCoordinate() {
    roadCoordCount++;
    const container = document.getElementById('road-coordinates-container');
    const div = document.createElement('div');
    div.className = 'coordinate-row';
    div.setAttribute('data-coord-id', roadCoordCount);
    div.innerHTML = `
        <input type="text" name="coord_x_road[]" placeholder="مختصات X" class="form-control coord-x-road">
        <input type="text" name="coord_y_road[]" placeholder="مختصات Y" class="form-control coord-y-road">
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

    if (projectType === 'water') {
        // جمع‌آوری مختصات آب
        const waterCoords = [];
        document.querySelectorAll('#water-coordinates-container .coordinate-row').forEach(row => {
            const x = row.querySelector('.coord-x-water')?.value || '';
            const y = row.querySelector('.coord-y-water')?.value || '';
            if (x || y) {
                waterCoords.push({ x: x, y: y });
            }
        });
        document.getElementById('coordinates_water_json').value = JSON.stringify(waterCoords);

        // جمع‌آوری صورت وضعیت موقت
        const tempStatuses = [];
        document.querySelectorAll('#temp-status-container .temp-status-row').forEach(row => {
            const amount = row.querySelector('.temp-amount')?.value || '';
            const date = row.querySelector('.temp-date')?.value || '';
            if (amount || date) {
                tempStatuses.push({ amount: amount, date: date });
            }
        });
        document.getElementById('temp_status_json').value = JSON.stringify(tempStatuses);

    } else if (projectType === 'road') {
        // جمع‌آوری مختصات راه
        const roadCoords = [];
        document.querySelectorAll('#road-coordinates-container .coordinate-row').forEach(row => {
            const x = row.querySelector('.coord-x-road')?.value || '';
            const y = row.querySelector('.coord-y-road')?.value || '';
            if (x || y) {
                roadCoords.push({ x: x, y: y });
            }
        });
        document.getElementById('coordinates_road_json').value = JSON.stringify(roadCoords);

        // جمع‌آوری آبروها
        const culverts = [];
        document.querySelectorAll('#culverts-tbody tr').forEach(row => {
            const type = row.querySelector('.culvert-type')?.value || '';
            const length = row.querySelector('.culvert-length')?.value || '';
            const x = row.querySelector('.culvert-x')?.value || '';
            const y = row.querySelector('.culvert-y')?.value || '';
            if (type) {
                culverts.push({ type: type, length: length, x: x, y: y });
            }
        });
        document.getElementById('culverts_json').value = JSON.stringify(culverts);
    }

    return true;
}

// اضافه کردن حداقل یک ردیف مختصات و آبرو به صورت پیش‌فرض
window.onload = function() {
    // می‌توانید به صورت پیش‌فرض یک ردیف اضافه کنید
    // addWaterCoordinate();
    // addRoadCoordinate();
    // addCulvert();
};
