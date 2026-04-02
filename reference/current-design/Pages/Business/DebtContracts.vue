<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ contracts: Object, stats: Object });
const showForm = ref(false);
const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

const form = useForm({
    customer_name: '', customer_phone: '', customer_email: '', customer_user_id: '',
    amount: '', currency: 'USD', description: '', due_date: '', installments_count: 1, terms: '',
});

// Customer search
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

const submit = () => form.post(route('business.debts.create'), { onSuccess: () => { showForm.value = false; form.reset(); } });

const statusLabel = (s) => ({ pending_signature: '⏳ بانتظار التوقيع', signed: '✅ موقّع', rejected: '❌ مرفوض', completed: '✔️ منجز', cancelled: '🚫 ملغي' }[s] || s);
const statusColor = (s) => ({ pending_signature: '#f59e0b', signed: '#059669', rejected: '#dc2626', completed: '#2563eb', cancelled: '#6b7280' }[s] || '#6b7280');

// Expand contract details
const expanded = ref(null);
const toggle = (id) => expanded.value = expanded.value === id ? null : id;
</script>

<template>
<BusinessLayout title="عقود الائتمان">
    <div class="dc-grid">
        <div class="dc-header">
            <div>
                <h2>📝 عقود الائتمان الإلكترونية</h2>
                <p class="dc-desc">أنشئ عقد ائتمان → يوصل إشعار للعميل ع التطبيق → يوقّع ويصوّر سيلفي مع هويته</p>
            </div>
            <button class="dc-btn-new" @click="showForm = !showForm">
                {{ showForm ? '✕ إلغاء' : '+ عقد جديد' }}
            </button>
        </div>

        <!-- Stats -->
        <div class="dc-stats">
            <div class="dc-stat"><div class="dc-stat-n">{{ stats?.total || 0 }}</div><div class="dc-stat-l">إجمالي العقود</div></div>
            <div class="dc-stat dc-warn"><div class="dc-stat-n">{{ stats?.pending || 0 }}</div><div class="dc-stat-l">⏳ بانتظار التوقيع</div></div>
            <div class="dc-stat dc-ok"><div class="dc-stat-n">{{ stats?.signed || 0 }}</div><div class="dc-stat-l">✅ موقّعة</div></div>
            <div class="dc-stat"><div class="dc-stat-n">{{ fmt(stats?.totalAmount) }}</div><div class="dc-stat-l">إجمالي المبالغ</div></div>
        </div>

        <!-- Create Form -->
        <div v-if="showForm" class="dc-form-wrap">
            <h3>📋 عقد ائتمان جديد</h3>
            <div class="dc-search-section">
                <div class="dc-search-bar">
                    <span>🔍</span>
                    <input v-model="searchQuery" placeholder="ابحث عن عميل SDB... (هاتف، إيميل، اسم)" class="dc-search-input" />
                </div>
                <div v-if="showResults" class="dc-search-results">
                    <div v-for="r in searchResults" :key="r.id" class="dc-search-item" @click="selectCustomer(r)">
                        <div class="dc-avatar">{{ (r.full_name || '?')[0] }}</div>
                        <div>
                            <div class="dc-sname">{{ r.full_name }}</div>
                            <div class="dc-smeta"><span v-if="r.phone">📱{{ r.phone }}</span> <span v-if="r.email">📧{{ r.email }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="form.customer_name" class="dc-selected-customer">
                ✅ العميل المحدد: <strong>{{ form.customer_name }}</strong>
                <span v-if="form.customer_phone"> — {{ form.customer_phone }}</span>
            </div>
            <div class="dc-form-grid">
                <div class="dc-f"><label>المبلغ *</label><input v-model="form.amount" type="number" step="0.01" placeholder="0.00" /></div>
                <div class="dc-f">
                    <label>العملة</label>
                    <select v-model="form.currency">
                        <option value="USD">$ دولار</option>
                        <option value="SYP">ل.س ليرة سورية</option>
                        <option value="EUR">€ يورو</option>
                        <option value="TRY">₺ ليرة تركية</option>
                    </select>
                </div>
                <div class="dc-f"><label>تاريخ الاستحقاق</label><input v-model="form.due_date" type="date" /></div>
                <div class="dc-f"><label>عدد الأقساط</label><input v-model="form.installments_count" type="number" min="1" max="60" /></div>
            </div>
            <div class="dc-f full"><label>وصف العقد *</label><textarea v-model="form.description" rows="2" placeholder="بضاعة، خدمات، قرض..."></textarea></div>
            <div class="dc-f full"><label>شروط العقد</label><textarea v-model="form.terms" rows="3" placeholder="شروط إضافية (اختياري)..."></textarea></div>
            <div class="dc-form-footer">
                <div class="dc-total">المبلغ: <strong>{{ fmt(form.amount) }} {{ form.currency }}</strong></div>
                <button class="dc-submit" @click="submit" :disabled="!form.customer_user_id || !form.amount || !form.description || form.processing">
                    {{ form.processing ? '⏳' : '📤 إنشاء وإرسال للعميل' }}
                </button>
            </div>
        </div>

        <!-- Contracts List -->
        <div class="dc-list">
            <div v-for="c in contracts?.data" :key="c.id" class="dc-card" @click="toggle(c.id)">
                <div class="dc-card-top">
                    <div>
                        <div class="dc-card-number">{{ c.contract_number }}</div>
                        <div class="dc-card-name">{{ c.customer_name }}</div>
                        <div class="dc-card-meta">
                            <span v-if="c.customer_phone">📱 {{ c.customer_phone }}</span>
                            <span v-if="c.customer_email">📧 {{ c.customer_email }}</span>
                        </div>
                    </div>
                    <div class="dc-card-right">
                        <div class="dc-card-amount">{{ fmt(c.amount) }} {{ c.currency }}</div>
                        <div class="dc-card-status" :style="{color: statusColor(c.status)}">{{ statusLabel(c.status) }}</div>
                    </div>
                </div>

                <!-- Expanded Details -->
                <div v-if="expanded === c.id" class="dc-details">
                    <div class="dc-detail-row"><strong>الوصف:</strong> {{ c.description }}</div>
                    <div class="dc-detail-row" v-if="c.due_date"><strong>تاريخ الاستحقاق:</strong> {{ fmtDate(c.due_date) }}</div>
                    <div class="dc-detail-row" v-if="c.installments_count > 1"><strong>عدد الأقساط:</strong> {{ c.installments_count }}</div>
                    <div class="dc-detail-row" v-if="c.terms"><strong>الشروط:</strong> {{ c.terms }}</div>
                    <div class="dc-detail-row"><strong>تاريخ الإنشاء:</strong> {{ fmtDate(c.created_at) }}</div>

                    <!-- Signature & Selfie -->
                    <div v-if="c.status === 'signed'" class="dc-signed-info">
                        <div class="dc-signed-badge">✅ تم التوقيع بتاريخ {{ fmtDate(c.signed_at) }}</div>
                        <div class="dc-sig-grid">
                            <div v-if="c.customer_signature" class="dc-sig-box">
                                <div class="dc-sig-label">التوقيع الإلكتروني</div>
                                <img :src="c.customer_signature" class="dc-sig-img" />
                            </div>
                            <div v-if="c.selfie_with_id" class="dc-sig-box">
                                <div class="dc-sig-label">سيلفي مع الهوية</div>
                                <img :src="c.selfie_with_id" class="dc-selfie-img" />
                            </div>
                        </div>
                    </div>

                    <div v-if="c.status === 'rejected'" class="dc-rejected-info">
                        ❌ رفض العميل: {{ c.rejection_reason || 'بدون سبب' }}
                    </div>
                </div>

                <div class="dc-card-date">{{ fmtDate(c.created_at) }}</div>
            </div>
            <div v-if="!contracts?.data?.length" class="dc-empty">لا توجد عقود — أنشئ أول عقد ائتمان 📝</div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.dc-grid { display: flex; flex-direction: column; gap: 18px; }
.dc-header { display: flex; justify-content: space-between; align-items: center; }
.dc-header h2 { font-size: 18px; font-weight: 900; color: #1a1a2e; }
.dc-desc { font-size: 12px; color: #6b7280; margin-top: 2px; }
.dc-btn-new { background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; }
.dc-btn-new:hover { background: #0d5c1e; }

.dc-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.dc-stat { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 14px; text-align: center; }
.dc-warn { background: #fffbeb; border-color: #fde68a; }
.dc-ok { background: #ecfdf5; border-color: #a7f3d0; }
.dc-stat-n { font-size: 22px; font-weight: 900; color: #1a1a2e; }
.dc-stat-l { font-size: 10px; color: #6b7280; font-weight: 600; margin-top: 2px; }

.dc-form-wrap { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 24px; }
.dc-form-wrap h3 { font-size: 15px; font-weight: 800; margin-bottom: 12px; color: #1a1a2e; }

.dc-search-section { position: relative; margin-bottom: 8px; }
.dc-search-bar { display: flex; align-items: center; background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 14px; padding: 4px 12px; gap: 8px; }
.dc-search-bar:focus-within { border-color: #106F24; }
.dc-search-input { flex: 1; border: none; background: transparent; padding: 10px 0; font-size: 13px; font-family: 'Cairo'; outline: none; }
.dc-search-results { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 4px; box-shadow: 0 8px 30px rgba(0,0,0,.1); z-index: 50; }
.dc-search-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
.dc-search-item:hover { background: #f0fdf4; }
.dc-avatar { width: 36px; height: 36px; border-radius: 10px; background: #106F24; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.dc-sname { font-size: 13px; font-weight: 700; }
.dc-smeta { font-size: 10px; color: #6b7280; display: flex; gap: 8px; }
.dc-selected-customer { background: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #059669; margin-bottom: 10px; }

.dc-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.dc-f { display: flex; flex-direction: column; gap: 4px; }
.dc-f.full { margin-top: 8px; }
.dc-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.dc-f input, .dc-f select, .dc-f textarea { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; font-size: 13px; font-family: 'Cairo'; outline: none; }
.dc-f input:focus, .dc-f select:focus, .dc-f textarea:focus { border-color: #106F24; }
.dc-form-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
.dc-total { font-size: 15px; color: #64748b; }
.dc-total strong { color: #106F24; font-size: 18px; }
.dc-submit { background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; font-size: 13px; }
.dc-submit:disabled { opacity: .4; }

.dc-list { display: flex; flex-direction: column; gap: 10px; }
.dc-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 18px; cursor: pointer; transition: all .2s; }
.dc-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.05); }
.dc-card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.dc-card-number { font-family: 'SF Mono', monospace; font-size: 11px; color: #6b7280; }
.dc-card-name { font-size: 15px; font-weight: 800; color: #1a1a2e; margin-top: 2px; }
.dc-card-meta { display: flex; gap: 12px; font-size: 11px; color: #6b7280; margin-top: 2px; }
.dc-card-right { text-align: left; }
.dc-card-amount { font-size: 18px; font-weight: 900; color: #1a1a2e; }
.dc-card-status { font-size: 12px; font-weight: 700; margin-top: 2px; }
.dc-card-date { font-size: 10px; color: #9ca3af; margin-top: 8px; }

.dc-details { margin-top: 14px; padding-top: 14px; border-top: 1px solid #f1f5f9; }
.dc-detail-row { font-size: 12px; color: #334155; margin-bottom: 6px; }
.dc-detail-row strong { color: #0f172a; }

.dc-signed-info { margin-top: 12px; }
.dc-signed-badge { background: #ecfdf5; color: #059669; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700; margin-bottom: 10px; }
.dc-sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.dc-sig-box { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; }
.dc-sig-label { font-size: 10px; color: #6b7280; font-weight: 600; margin-bottom: 6px; }
.dc-sig-img { max-width: 100%; max-height: 100px; border-radius: 8px; }
.dc-selfie-img { max-width: 100%; max-height: 200px; border-radius: 10px; object-fit: cover; }

.dc-rejected-info { margin-top: 10px; padding: 10px; background: #fef2f2; border-radius: 8px; color: #dc2626; font-size: 12px; }
.dc-empty { text-align: center; padding: 40px; color: #9ca3af; background: #fff; border-radius: 16px; border: 1px solid #e8ecf1; }
@media (max-width: 768px) { .dc-stats { grid-template-columns: repeat(2, 1fr); } .dc-form-grid { grid-template-columns: 1fr; } .dc-sig-grid { grid-template-columns: 1fr; } }
</style>
