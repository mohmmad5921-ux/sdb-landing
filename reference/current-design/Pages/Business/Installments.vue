<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ contracts: Object, stats: Object, filters: Object });
const showCreate = ref(false);
const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
const statusLabel = (s) => ({ pending: 'بانتظار الموافقة', active: 'نشط', completed: 'مكتمل', defaulted: 'متعثر', cancelled: 'ملغي' }[s] || s);
const statusClass = (s) => ({ pending: 'sc-yellow', active: 'sc-green', completed: 'sc-blue', defaulted: 'sc-red', cancelled: 'sc-gray' }[s] || '');

const form = useForm({
    product_name: '', product_description: '', total_price: '', down_payment: 0,
    total_installments: 12, late_fee_percentage: 0, late_fee_fixed: 0, currency: 'USD',
    start_date: '', customer_name: '', customer_phone: '', customer_email: '',
    customer_user_id: '', notes: '', interest_rate: 0,
    contract_type: '', contract_terms: '',
});

// === Contract Type Templates ===
const contractTypes = [
    { key: 'product', icon: '📦', label: 'منتج', desc: 'أجهزة، إلكترونيات، أثاث...', defaultPeriod: 12, defaultInterest: 0, defaultDp: 10, defaultTerms: 'يلتزم المشتري بسداد الأقساط الشهرية في موعدها.\nيبقى المنتج ملكاً للبائع حتى سداد كامل المبلغ.\nفي حال التأخير عن السداد يترتب غرامة تأخير حسب الاتفاق.\nلا يحق للمشتري بيع أو التصرف بالمنتج قبل إتمام السداد.' },
    { key: 'vehicle', icon: '🚗', label: 'سيارة', desc: 'سيارات، دراجات، مركبات', defaultPeriod: 36, defaultInterest: 5, defaultDp: 20, defaultTerms: 'عقد بيع سيارة بالتقسيط\nيلتزم المشتري بسداد الأقساط الشهرية المحددة.\nتبقى ملكية السيارة باسم البائع حتى سداد آخر قسط.\nيتحمل المشتري جميع مصاريف الصيانة والتأمين خلال فترة التقسيط.\nفي حال التخلف عن سداد 3 أقساط متتالية يحق للبائع استرداد السيارة.\nلا يحق للمشتري بيع السيارة أو رهنها قبل إتمام السداد.' },
    { key: 'property', icon: '🏠', label: 'عقار', desc: 'شقق، منازل، أراضي', defaultPeriod: 60, defaultInterest: 7, defaultDp: 30, defaultTerms: 'عقد بيع عقار بالتقسيط\nيلتزم المشتري بسداد الأقساط الشهرية في الموعد المحدد.\nيتم تسجيل العقار باسم المشتري بعد سداد كامل المبلغ.\nللبائع حق حبس التسجيل حتى إتمام السداد.\nيتحمل المشتري رسوم التسجيل والنقل.\nفي حال التخلف عن السداد لمدة 6 أشهر يحق للبائع فسخ العقد مع الاحتفاظ بنسبة من المبالغ المدفوعة كتعويض.\nجميع الإصلاحات والصيانة على عاتق المشتري من تاريخ التسليم.' },
    { key: 'service', icon: '🔧', label: 'خدمة', desc: 'تعليم، علاج، استشارات', defaultPeriod: 6, defaultInterest: 0, defaultDp: 0, defaultTerms: 'عقد تقديم خدمة بالتقسيط\nيلتزم مقدم الخدمة بتقديم الخدمة المتفق عليها بالكامل.\nيلتزم العميل بسداد الأقساط الشهرية في موعدها.\nلا يحق للعميل المطالبة باسترداد المبالغ المدفوعة بعد بدء تقديم الخدمة.\nفي حال توقف العميل عن السداد يحق لمقدم الخدمة إيقاف الخدمة.' },
];
const selectedType = ref('');
const selectType = (t) => {
    selectedType.value = t.key;
    form.contract_type = t.key;
    form.total_installments = t.defaultPeriod;
    form.interest_rate = t.defaultInterest;
    form.contract_terms = t.defaultTerms;
    selectedPreset.value = periodPresets.find(p => p.value === t.defaultPeriod)?.value ?? 0;
    if (selectedPreset.value === 0) customPeriod.value = t.defaultPeriod;
    if (t.defaultDp > 0 && form.total_price) setDpPercent(t.defaultDp);
};

// === Installment Period Presets ===
const periodPresets = [
    { label: '3 أشهر', value: 3 },
    { label: '6 أشهر', value: 6 },
    { label: '12 شهر', value: 12 },
    { label: '18 شهر', value: 18 },
    { label: '24 شهر', value: 24 },
    { label: '36 شهر', value: 36 },
    { label: 'مخصص', value: 0 },
];
const selectedPreset = ref(12);
const customPeriod = ref(12);
const selectPreset = (p) => {
    selectedPreset.value = p.value;
    if (p.value > 0) form.total_installments = p.value;
    else form.total_installments = customPeriod.value;
};
watch(customPeriod, (v) => { if (selectedPreset.value === 0) form.total_installments = v; });

// === Smart Calculations ===
const remaining = computed(() => Math.max(0, Number(form.total_price || 0) - Number(form.down_payment || 0)));
const interestAmount = computed(() => remaining.value * (Number(form.interest_rate || 0) / 100));
const totalWithInterest = computed(() => remaining.value + interestAmount.value);
const installmentAmount = computed(() => form.total_installments > 0 ? (totalWithInterest.value / form.total_installments).toFixed(2) : '0.00');

// === SDB Customer Search ===
const searchQuery = ref('');
const searchResults = ref([]);
const showResults = ref(false);
let searchTimer = null;
watch(searchQuery, (val) => {
    if (val.length < 2) { searchResults.value = []; showResults.value = false; return; }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/business/search-customer?q=${encodeURIComponent(val)}`);
            searchResults.value = await res.json();
            showResults.value = searchResults.value.length > 0;
        } catch(e) {}
    }, 300);
});
const selectCustomer = (c) => {
    form.customer_name = c.full_name;
    form.customer_email = c.email || '';
    form.customer_phone = c.phone || '';
    form.customer_user_id = c.id;
    searchQuery.value = '';
    showResults.value = false;
};

// === Down Payment Presets ===
const dpPresets = [0, 10, 20, 30, 50];
const setDpPercent = (pct) => { form.down_payment = (Number(form.total_price || 0) * pct / 100).toFixed(2); };

const submit = () => form.post(route('business.installments.create'), { onSuccess: () => { showCreate.value = false; form.reset(); } });

// === Expanded card detail ===
const expandedId = ref(null);
const toggle = (id) => expandedId.value = expandedId.value === id ? null : id;

// === Contract Type Labels ===
const contractTypeLabel = (type) => ({ product: 'منتج', vehicle: 'سيارة', property: 'عقار', service: 'خدمة' }[type] || type);

// === Print / Download Contract ===
const buildContractHtml = (c) => {
    const signedDate = c.signed_at ? new Date(c.signed_at).toLocaleDateString('ar-SA') : '';
    const startDate = c.start_date ? new Date(c.start_date).toLocaleDateString('ar-SA') : '';
    return `<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Tahoma,sans-serif;padding:40px;color:#1a1a2e;font-size:14px;line-height:1.8}
.header{text-align:center;border-bottom:3px solid #106F24;padding-bottom:20px;margin-bottom:30px}
.header h1{font-size:22px;color:#106F24;margin-bottom:4px}
.header .sub{color:#666;font-size:13px}
.contract-num{font-size:16px;color:#106F24;font-weight:bold;margin-top:8px}
.section{margin-bottom:24px}
.section-title{font-size:15px;font-weight:bold;color:#106F24;border-bottom:1px solid #e0e0e0;padding-bottom:6px;margin-bottom:12px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 30px}
.info-row{display:flex;justify-content:space-between;padding:4px 0}
.info-label{color:#666;font-weight:500}
.info-value{font-weight:bold;color:#1a1a2e}
.terms-box{background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;padding:16px;white-space:pre-wrap;font-size:13px;line-height:2}
.sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:12px}
.sig-box{border:1px solid #e0e0e0;border-radius:8px;padding:12px;text-align:center}
.sig-box img{max-width:200px;max-height:150px;border-radius:6px}
.sig-label{font-size:12px;color:#666;margin-bottom:8px}
.footer{margin-top:40px;border-top:2px solid #e0e0e0;padding-top:16px;text-align:center;color:#999;font-size:11px}
.highlight{background:#ecfdf5;padding:10px 16px;border-radius:8px;border:1px solid #a7f3d0;text-align:center;font-size:16px;font-weight:bold;color:#106F24;margin:12px 0}
@media print{body{padding:20px}.no-print{display:none}}
</style></head><body>
<div class="header">
    <h1>📋 عقد ائتمان</h1>
    <div class="sub">SDB Wallet — نظام عقود الائتمان</div>
    <div class="contract-num">${c.contract_number}</div>
</div>

<div class="section">
    <div class="section-title">📌 بيانات العقد</div>
    <div class="info-grid">
        <div class="info-row"><span class="info-label">اسم المنتج/الخدمة:</span><span class="info-value">${c.product_name}</span></div>
        <div class="info-row"><span class="info-label">نوع العقد:</span><span class="info-value">${contractTypeLabel(c.contract_type)}</span></div>
        <div class="info-row"><span class="info-label">رقم العقد:</span><span class="info-value">${c.contract_number}</span></div>
        <div class="info-row"><span class="info-label">الحالة:</span><span class="info-value">${c.status === 'active' ? '✅ نشط' : c.status === 'pending' ? '⏳ بانتظار الموافقة' : c.status}</span></div>
    </div>
</div>

<div class="section">
    <div class="section-title">👤 بيانات العميل</div>
    <div class="info-grid">
        <div class="info-row"><span class="info-label">الاسم:</span><span class="info-value">${c.customer_name}</span></div>
        <div class="info-row"><span class="info-label">الهاتف:</span><span class="info-value">${c.customer_phone || '-'}</span></div>
        <div class="info-row"><span class="info-label">البريد:</span><span class="info-value">${c.customer_email || '-'}</span></div>
    </div>
</div>

<div class="section">
    <div class="section-title">💰 التفاصيل المالية</div>
    <div class="info-grid">
        <div class="info-row"><span class="info-label">المبلغ الإجمالي:</span><span class="info-value">${fmt(c.total_price)} ${c.currency}</span></div>
        <div class="info-row"><span class="info-label">الدفعة الأولى:</span><span class="info-value">${fmt(c.down_payment)} ${c.currency}</span></div>
        <div class="info-row"><span class="info-label">نسبة الفائدة:</span><span class="info-value">${c.interest_rate || 0}%</span></div>
        <div class="info-row"><span class="info-label">عدد الأقساط:</span><span class="info-value">${c.total_installments} شهر</span></div>
        <div class="info-row"><span class="info-label">تاريخ البداية:</span><span class="info-value">${startDate}</span></div>
        <div class="info-row"><span class="info-label">غرامة التأخير:</span><span class="info-value">${c.late_fee_percentage || 0}%</span></div>
    </div>
    <div class="highlight">💳 القسط الشهري: ${fmt(c.installment_amount)} ${c.currency}</div>
</div>

${c.contract_terms ? `<div class="section"><div class="section-title">📜 شروط وأحكام العقد</div><div class="terms-box">${c.contract_terms}</div></div>` : ''}

${c.customer_signature || c.selfie_with_id ? `<div class="section">
    <div class="section-title">✅ التوقيع والتحقق</div>
    ${signedDate ? `<div style="margin-bottom:10px;color:#666">تاريخ التوقيع: ${signedDate}</div>` : ''}
    <div class="sig-grid">
        ${c.customer_signature ? `<div class="sig-box"><div class="sig-label">توقيع العميل</div><img src="${c.customer_signature}"/></div>` : ''}
        ${c.selfie_with_id ? `<div class="sig-box"><div class="sig-label">سيلفي مع الهوية</div><img src="${c.selfie_with_id}"/></div>` : ''}
    </div>
</div>` : ''}

<div class="footer">
    <strong>SDB Wallet</strong> — نظام العقود الائتمانية الرقمي<br>
    تم إنشاء هذا المستند إلكترونياً بتاريخ ${new Date().toLocaleDateString('ar-SA')} عبر منصة SDB Wallet.<br>
    جميع البيانات والتوقيعات تمت بشكل رقمي مؤمّن وموثّق عبر نظامنا.<br>
    هذا العقد ملزم قانونياً لجميع الأطراف بعد التوقيع الإلكتروني.<br><br>
    <em style="color:#aaa">في حال وجود أي استفسار أو خطأ في البيانات، يرجى التواصل مع فريق الدعم عبر التطبيق أو البريد الإلكتروني support@sdbwallet.com — نحن هنا لمساعدتك.</em>
</div>
</body></html>`;
};

const printContract = (c) => {
    const w = window.open('', '_blank');
    w.document.write(buildContractHtml(c));
    w.document.close();
    setTimeout(() => w.print(), 500);
};

const downloadContract = (c) => {
    const w = window.open('', '_blank');
    w.document.write(buildContractHtml(c));
    w.document.close();
    setTimeout(() => w.print(), 500);
};
</script>

<template>
<BusinessLayout title="عقود الائتمان">
    <div class="ins-grid">
        <!-- Header -->
        <div class="ins-header">
            <div>
                <h2>📋 عقود الائتمان</h2>
                <p class="ins-desc">أنشئ عقود تقسيط لعملائك — يصل إشعار للعميل ويتابع أقساطه من التطبيق</p>
            </div>
            <button class="ins-create" @click="showCreate = !showCreate">{{ showCreate ? '✕ إلغاء' : '+ عقد جديد' }}</button>
        </div>

        <!-- Stats -->
        <div class="ins-stats">
            <div class="ins-stat"><div class="ins-stat-icon">📋</div><div><div class="ins-stat-num">{{ stats?.total || 0 }}</div><div class="ins-stat-label">إجمالي العقود</div></div></div>
            <div class="ins-stat ins-active"><div class="ins-stat-icon">✅</div><div><div class="ins-stat-num">{{ stats?.active || 0 }}</div><div class="ins-stat-label">نشطة</div></div></div>
            <div class="ins-stat ins-pending"><div class="ins-stat-icon">⏳</div><div><div class="ins-stat-num">{{ stats?.pending || 0 }}</div><div class="ins-stat-label">بانتظار الموافقة</div></div></div>
            <div class="ins-stat"><div class="ins-stat-icon">💰</div><div><div class="ins-stat-num">{{ fmt(stats?.totalValue) }}</div><div class="ins-stat-label">القيمة الإجمالية</div></div></div>
            <div class="ins-stat"><div class="ins-stat-icon">📈</div><div><div class="ins-stat-num">{{ fmt(stats?.totalCollected) }}</div><div class="ins-stat-label">المحصّل</div></div></div>
        </div>

        <!-- ════ Create Form ════ -->
        <div v-if="showCreate" class="ins-form-wrap">
            <h3>📋 عقد ائتمان جديد</h3>

            <!-- 0) Contract Type Selection -->
            <div class="ins-form-section">
                <div class="ins-section-title">📑 نوع العقد</div>
                <div class="ins-type-grid">
                    <div v-for="t in contractTypes" :key="t.key" class="ins-type-card" :class="{ active: selectedType === t.key }" @click="selectType(t)">
                        <div class="ins-type-icon">{{ t.icon }}</div>
                        <div class="ins-type-label">{{ t.label }}</div>
                        <div class="ins-type-desc">{{ t.desc }}</div>
                    </div>
                </div>
            </div>

            <!-- 1) SDB Customer Search -->
            <div class="ins-form-section">
                <div class="ins-section-title">🔍 بحث عن عميل SDB</div>
                <div class="ins-search-bar">
                    <span>🔍</span>
                    <input v-model="searchQuery" placeholder="ابحث بالاسم، رقم الهاتف، أو الإيميل..." class="ins-search-input" />
                </div>
                <div v-if="showResults" class="ins-search-results">
                    <div v-for="r in searchResults" :key="r.id" class="ins-search-item" @click="selectCustomer(r)">
                        <div class="ins-avatar">{{ (r.full_name || '?')[0] }}</div>
                        <div>
                            <div class="ins-sname">{{ r.full_name }}</div>
                            <div class="ins-smeta"><span v-if="r.phone">📱 {{ r.phone }}</span> <span v-if="r.email">📧 {{ r.email }}</span></div>
                        </div>
                    </div>
                </div>
                <div v-if="form.customer_user_id" class="ins-selected-customer">
                    ✅ العميل: <strong>{{ form.customer_name }}</strong>
                    <span v-if="form.customer_phone"> — {{ form.customer_phone }}</span>
                    <button class="ins-clear-btn" @click="form.customer_user_id=''; form.customer_name=''; form.customer_phone=''; form.customer_email=''">✕</button>
                </div>
                <div v-if="!form.customer_user_id" class="ins-manual-note">💡 العقود متاحة فقط لعملاء SDB المسجّلين</div>
            </div>

            <!-- 2) Product Info -->
            <div class="ins-form-section">
                <div class="ins-section-title">📦 بيانات المنتج / الخدمة</div>
                <div class="ins-form-row">
                    <div class="ins-f span2"><label>اسم المنتج / الخدمة *</label><input v-model="form.product_name" placeholder="مثل: iPhone 15 Pro, أثاث منزلي..." /></div>
                </div>
                <div class="ins-form-row">
                    <div class="ins-f"><label>السعر الكامل *</label><input v-model="form.total_price" type="number" step="0.01" placeholder="0.00" /></div>
                    <div class="ins-f">
                        <label>العملة</label>
                        <select v-model="form.currency">
                            <option value="USD">$ دولار أمريكي</option>
                            <option value="SYP">ل.س ليرة سورية</option>
                        </select>
                    </div>
                </div>
                <div class="ins-f"><label>وصف المنتج</label><textarea v-model="form.product_description" rows="2" placeholder="مواصفات المنتج أو تفاصيل الخدمة..."></textarea></div>
            </div>

            <!-- 3) Down Payment -->
            <div class="ins-form-section">
                <div class="ins-section-title">💳 الدفعة الأولى</div>
                <div class="ins-dp-row">
                    <div class="ins-f"><label>مبلغ الدفعة الأولى</label><input v-model="form.down_payment" type="number" step="0.01" /></div>
                    <div class="ins-dp-presets">
                        <button v-for="p in dpPresets" :key="p" class="ins-dp-btn" :class="{ active: Math.round(Number(form.down_payment) / Number(form.total_price || 1) * 100) === p }" @click="setDpPercent(p)">{{ p }}%</button>
                    </div>
                </div>
            </div>

            <!-- 4) Installment Period -->
            <div class="ins-form-section">
                <div class="ins-section-title">📅 فترة التقسيط</div>
                <div class="ins-period-grid">
                    <button v-for="p in periodPresets" :key="p.value" class="ins-period-btn" :class="{ active: selectedPreset === p.value }" @click="selectPreset(p)">
                        {{ p.label }}
                    </button>
                </div>
                <div v-if="selectedPreset === 0" class="ins-custom-period">
                    <label>عدد الأشهر:</label>
                    <input v-model="customPeriod" type="number" min="1" max="120" />
                </div>
                <div class="ins-form-row" style="margin-top:12px">
                    <div class="ins-f"><label>تاريخ أول قسط *</label><input v-model="form.start_date" type="date" /></div>
                    <div class="ins-f"><label>نسبة الفائدة السنوية %</label><input v-model="form.interest_rate" type="number" step="0.1" min="0" max="100" placeholder="0" /></div>
                </div>
            </div>

            <!-- 5) Late Fees -->
            <div class="ins-form-section">
                <div class="ins-section-title">⚠️ شروط التأخير</div>
                <div class="ins-form-row">
                    <div class="ins-f"><label>غرامة تأخير (%)</label><input v-model="form.late_fee_percentage" type="number" step="0.1" /></div>
                    <div class="ins-f"><label>غرامة ثابتة</label><input v-model="form.late_fee_fixed" type="number" step="0.01" /></div>
                    <div class="ins-f"><label>ملاحظات</label><input v-model="form.notes" placeholder="ملاحظات إضافية" /></div>
                </div>
            </div>

            <!-- 6) Contract Terms -->
            <div v-if="form.contract_terms" class="ins-form-section">
                <div class="ins-section-title">📜 شروط العقد <span class="ins-editable-badge">قابلة للتعديل</span></div>
                <textarea v-model="form.contract_terms" rows="6" class="ins-terms-textarea"></textarea>
            </div>
            <!-- ═══ Preview ═══ -->
            <div class="ins-preview">
                <div class="ins-preview-title">📊 ملخص العقد</div>
                <div class="ins-preview-grid">
                    <div><span class="ins-pl">السعر الكامل</span><span class="ins-pv">{{ fmt(form.total_price) }} {{ form.currency }}</span></div>
                    <div><span class="ins-pl">الدفعة الأولى</span><span class="ins-pv">{{ fmt(form.down_payment) }} {{ form.currency }}</span></div>
                    <div><span class="ins-pl">المتبقي</span><span class="ins-pv">{{ fmt(remaining) }} {{ form.currency }}</span></div>
                    <div v-if="form.interest_rate > 0"><span class="ins-pl">الفائدة ({{ form.interest_rate }}%)</span><span class="ins-pv">{{ fmt(interestAmount) }} {{ form.currency }}</span></div>
                    <div v-if="form.interest_rate > 0"><span class="ins-pl">الإجمالي مع الفائدة</span><span class="ins-pv">{{ fmt(totalWithInterest) }} {{ form.currency }}</span></div>
                    <div class="ins-highlight">
                        <span class="ins-pl">💰 القسط الشهري</span>
                        <span class="ins-pv-big">{{ installmentAmount }} {{ form.currency }} × {{ form.total_installments }} شهر</span>
                    </div>
                </div>
            </div>

            <button class="ins-submit" @click="submit" :disabled="!form.customer_user_id || !form.product_name || !form.total_price || !form.start_date || form.processing">
                {{ form.processing ? '⏳ جاري الإنشاء...' : '📤 إنشاء العقد وإرسال إشعار للعميل' }}
            </button>
        </div>

        <!-- ════ Contracts List ════ -->
        <div class="ins-list">
            <div v-for="c in contracts?.data" :key="c.id" class="ins-card" @click="toggle(c.id)">
                <div class="ins-card-header">
                    <div>
                        <div class="ins-card-product">{{ c.product_name }}</div>
                        <div class="ins-card-num">{{ c.contract_number }}</div>
                    </div>
                    <span :class="['ins-badge', statusClass(c.status)]">{{ statusLabel(c.status) }}</span>
                </div>
                <div class="ins-card-body">
                    <div class="ins-card-customer">👤 {{ c.customer_name }}</div>
                    <div class="ins-card-stats">
                        <div><span class="ins-pl">الإجمالي</span> {{ fmt(c.total_price) }} {{ c.currency }}</div>
                        <div><span class="ins-pl">القسط</span> {{ fmt(c.installment_amount) }}/شهر</div>
                        <div><span class="ins-pl">المدفوع</span> {{ c.paid_installments }}/{{ c.total_installments }}</div>
                        <div><span class="ins-pl">المحصّل</span> {{ fmt(c.total_paid) }}</div>
                    </div>
                    <div class="ins-card-progress">
                        <div class="ins-prog-bar"><div class="ins-prog-fill" :style="{ width: (c.paid_installments / c.total_installments * 100) + '%' }"></div></div>
                        <span class="ins-prog-text">{{ Math.round(c.paid_installments / c.total_installments * 100) }}%</span>
                    </div>
                    <div class="ins-card-next" v-if="c.status === 'active'">📅 القسط التالي: {{ fmtDate(c.next_payment_date) }}</div>

                    <!-- Expanded Detail -->
                    <div v-if="expandedId === c.id" class="ins-card-detail">
                        <div v-if="c.down_payment > 0"><span class="ins-pl">الدفعة الأولى:</span> {{ fmt(c.down_payment) }}</div>
                        <div v-if="c.interest_rate > 0"><span class="ins-pl">نسبة الفائدة:</span> {{ c.interest_rate }}%</div>
                        <div v-if="c.late_fee_percentage > 0"><span class="ins-pl">غرامة التأخير:</span> {{ c.late_fee_percentage }}%</div>
                        <div><span class="ins-pl">تاريخ البداية:</span> {{ fmtDate(c.start_date) }}</div>
                        <div v-if="c.product_description"><span class="ins-pl">الوصف:</span> {{ c.product_description }}</div>
                        <div v-if="c.notes"><span class="ins-pl">ملاحظات:</span> {{ c.notes }}</div>

                        <!-- Signature & Selfie if signed -->
                        <div v-if="c.customer_signature || c.selfie_with_id" class="ins-sig-section">
                            <div class="ins-sig-title">✅ وثائق التوقيع</div>
                            <div class="ins-sig-grid">
                                <div v-if="c.customer_signature" class="ins-sig-box">
                                    <div class="ins-sig-label">التوقيع</div>
                                    <img :src="c.customer_signature" class="ins-sig-img" />
                                </div>
                                <div v-if="c.selfie_with_id" class="ins-sig-box">
                                    <div class="ins-sig-label">سيلفي + هوية</div>
                                    <img :src="c.selfie_with_id" class="ins-selfie-img" />
                                </div>
                            </div>
                        </div>

                        <!-- Print / Download Actions -->
                        <div class="ins-actions-row">
                            <button class="ins-btn-print" @click.stop="printContract(c)">🖨️ طباعة العقد</button>
                            <button class="ins-btn-download" @click.stop="downloadContract(c)">📥 تنزيل PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="!contracts?.data?.length" class="ins-empty">لا توجد عقود — أنشئ أول عقد ائتمان 📋</div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.ins-grid { display: flex; flex-direction: column; gap: 16px; }
.ins-header { display: flex; justify-content: space-between; align-items: center; }
.ins-header h2 { font-size: 18px; font-weight: 900; color: #1a1a2e; }
.ins-desc { font-size: 12px; color: #6b7280; margin-top: 2px; }

.ins-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
.ins-stat { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 14px; display: flex; align-items: center; gap: 10px; }
.ins-active { background: #ecfdf5; border-color: #a7f3d0; }
.ins-pending { background: #fffbeb; border-color: #fde68a; }
.ins-stat-icon { font-size: 22px; }
.ins-stat-num { font-size: 18px; font-weight: 900; }
.ins-stat-label { font-size: 10px; color: #6b7280; font-weight: 600; }

.ins-create { background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; }
.ins-create:hover { background: #0d5c1e; }

.ins-form-wrap { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 24px; }
.ins-form-wrap h3 { font-size: 16px; font-weight: 800; margin-bottom: 16px; color: #1a1a2e; }
.ins-form-section { margin-bottom: 20px; }
.ins-section-title { font-size: 12px; font-weight: 700; color: #106F24; margin-bottom: 10px; letter-spacing: .5px; }
.ins-form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 10px; }
.ins-f { display: flex; flex-direction: column; gap: 4px; }
.ins-f.span2 { grid-column: span 2; }
.ins-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.ins-f input, .ins-f select, .ins-f textarea { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; color: #1a1a2e; font-size: 13px; font-family: 'Cairo'; outline: none; }
.ins-f input:focus, .ins-f select:focus, .ins-f textarea:focus { border-color: #106F24; }

/* Customer Search */
.ins-search-bar { display: flex; align-items: center; background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 14px; padding: 4px 12px; gap: 8px; }
.ins-search-bar:focus-within { border-color: #106F24; }
.ins-search-input { flex: 1; border: none; background: transparent; padding: 10px 0; font-size: 13px; font-family: 'Cairo'; outline: none; }
.ins-search-results { position: relative; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 4px; box-shadow: 0 8px 30px rgba(0,0,0,.1); z-index: 50; max-height: 200px; overflow-y: auto; }
.ins-search-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
.ins-search-item:hover { background: #f0fdf4; }
.ins-avatar { width: 36px; height: 36px; border-radius: 10px; background: #106F24; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
.ins-sname { font-size: 13px; font-weight: 700; }
.ins-smeta { font-size: 10px; color: #6b7280; display: flex; gap: 8px; }
.ins-selected-customer { background: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #059669; margin-top: 8px; display: flex; align-items: center; gap: 8px; }
.ins-clear-btn { background: none; border: none; color: #dc2626; cursor: pointer; font-size: 14px; margin-right: auto; }
.ins-manual-note { font-size: 11px; color: #9ca3af; margin-top: 6px; }

/* Down Payment */
.ins-dp-row { display: flex; gap: 12px; align-items: flex-end; }
.ins-dp-row .ins-f { flex: 1; }
.ins-dp-presets { display: flex; gap: 6px; padding-bottom: 4px; }
.ins-dp-btn { padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8f9fb; font-size: 12px; font-weight: 600; font-family: 'Cairo'; cursor: pointer; color: #6b7280; }
.ins-dp-btn.active { background: #106F24; color: #fff; border-color: #106F24; }

/* Period */
.ins-period-grid { display: flex; gap: 8px; flex-wrap: wrap; }
.ins-period-btn { padding: 8px 18px; border: 2px solid #e2e8f0; border-radius: 12px; background: #fff; font-size: 13px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; color: #334155; transition: all .2s; }
.ins-period-btn.active { background: #106F24; color: #fff; border-color: #106F24; }
.ins-period-btn:hover:not(.active) { border-color: #106F24; color: #106F24; }
.ins-custom-period { display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 12px; color: #6b7280; }
.ins-custom-period input { width: 80px; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-family: 'Cairo'; }

/* Preview */
.ins-preview { background: linear-gradient(135deg, rgba(16,111,36,.03), rgba(16,111,36,.08)); border: 1px solid rgba(16,111,36,.12); border-radius: 14px; padding: 18px; margin-top: 12px; }
.ins-preview-title { font-size: 14px; font-weight: 800; margin-bottom: 12px; color: #106F24; }
.ins-preview-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.ins-preview-grid > div { display: flex; justify-content: space-between; font-size: 12px; padding: 4px 0; }
.ins-highlight { background: rgba(16,111,36,.08); padding: 10px !important; border-radius: 10px; font-weight: 800; grid-column: 1 / -1; }
.ins-pl { color: #6b7280; }
.ins-pv { font-weight: 700; color: #1a1a2e; }
.ins-pv-big { font-size: 16px; font-weight: 900; color: #106F24; }

.ins-submit { width: 100%; margin-top: 16px; background: #106F24; color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 800; font-family: 'Cairo'; cursor: pointer; font-size: 14px; }
.ins-submit:disabled { opacity: .4; cursor: not-allowed; }
.ins-submit:hover:not(:disabled) { background: #0d5c1e; }

/* Cards */
.ins-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.ins-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 18px; transition: all .2s; cursor: pointer; }
.ins-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.05); }
.ins-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.ins-card-product { font-size: 15px; font-weight: 800; }
.ins-card-num { font-size: 10px; color: #9ca3af; font-family: monospace; }
.ins-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 8px; }
.sc-green { background: #ecfdf5; color: #059669; }
.sc-yellow { background: #fffbeb; color: #f59e0b; }
.sc-blue { background: #eff6ff; color: #3b82f6; }
.sc-red { background: #fef2f2; color: #ef4444; }
.sc-gray { background: #f1f5f9; color: #6b7280; }
.ins-card-customer { font-size: 12px; color: #6b7280; margin-bottom: 10px; }
.ins-card-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px; font-size: 11px; margin-bottom: 10px; }
.ins-card-progress { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.ins-prog-bar { flex: 1; height: 6px; background: #e8ecf1; border-radius: 3px; overflow: hidden; }
.ins-prog-fill { height: 100%; background: linear-gradient(90deg, #106F24, #10b981); border-radius: 3px; transition: width .3s; }
.ins-prog-text { font-size: 11px; font-weight: 700; color: #106F24; }
.ins-card-next { font-size: 11px; color: #6b7280; }

/* Expanded Detail */
.ins-card-detail { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #334155; }
.ins-card-detail > div { margin-bottom: 4px; }
.ins-sig-section { margin-top: 12px; padding: 12px; background: #f0fdf4; border-radius: 10px; }
.ins-sig-title { font-size: 12px; font-weight: 700; color: #059669; margin-bottom: 8px; }
.ins-sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ins-sig-box { text-align: center; }
.ins-sig-label { font-size: 10px; color: #6b7280; margin-bottom: 4px; }
.ins-sig-img { max-width: 100%; max-height: 80px; border-radius: 8px; }
.ins-selfie-img { max-width: 100%; max-height: 120px; border-radius: 8px; object-fit: cover; }

.ins-empty { text-align: center; padding: 40px; color: #9ca3af; background: #fff; border-radius: 16px; border: 1px solid #e8ecf1; }

/* Contract Type Cards */
.ins-type-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.ins-type-card { border: 2px solid #e2e8f0; border-radius: 14px; padding: 16px; text-align: center; cursor: pointer; transition: all .2s; background: #fff; }
.ins-type-card:hover { border-color: #106F24; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,111,36,.1); }
.ins-type-card.active { border-color: #106F24; background: #f0fdf4; }
.ins-type-icon { font-size: 28px; margin-bottom: 6px; }
.ins-type-label { font-size: 14px; font-weight: 800; color: #1a1a2e; }
.ins-type-desc { font-size: 10px; color: #6b7280; margin-top: 2px; }

/* Contract Terms */
.ins-terms-textarea { width: 100%; background: #fefce8; border: 1px solid #fde68a; border-radius: 12px; padding: 14px; font-size: 13px; font-family: 'Cairo'; color: #1a1a2e; line-height: 1.8; outline: none; resize: vertical; }
.ins-terms-textarea:focus { border-color: #f59e0b; }
.ins-editable-badge { background: #fef3c7; color: #92400e; font-size: 9px; padding: 2px 8px; border-radius: 6px; margin-right: 6px; font-weight: 600; }

@media (max-width: 900px) { .ins-stats { grid-template-columns: repeat(2, 1fr); } .ins-list { grid-template-columns: 1fr; } .ins-form-row { grid-template-columns: 1fr; } .ins-dp-row { flex-direction: column; } .ins-type-grid { grid-template-columns: repeat(2, 1fr); } }

/* Print / Download Actions */
.ins-actions-row { display: flex; gap: 10px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #e0e0e0; }
.ins-btn-print, .ins-btn-download { flex: 1; padding: 10px 16px; border: none; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: 'Cairo'; }
.ins-btn-print { background: #f0fdf4; color: #106F24; border: 1px solid #a7f3d0; }
.ins-btn-print:hover { background: #dcfce7; transform: translateY(-1px); }
.ins-btn-download { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.ins-btn-download:hover { background: #dbeafe; transform: translateY(-1px); }
</style>
