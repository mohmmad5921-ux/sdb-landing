<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ requests: Object });
const showForm = ref(false);

const form = useForm({
    customer_name: '', customer_phone: '', customer_email: '',
    customer_account_number: '', business_notes: '',
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
    form.customer_account_number = c.account_number || '';
    searchQuery.value = '';
    showResults.value = false;
};

const submit = () => form.post(route('business.credit.request'), { onSuccess: () => { showForm.value = false; form.reset(); } });

const ratingColor = (r) => ({ 'A+': '#059669', 'A': '#10b981', 'B+': '#3b82f6', 'B': '#60a5fa', 'C': '#f59e0b', 'D': '#ef4444', 'F': '#dc2626' }[r] || '#64748b');
const ratingBg = (r) => ({ 'A+': '#ecfdf5', 'A': '#ecfdf5', 'B+': '#eff6ff', 'B': '#eff6ff', 'C': '#fffbeb', 'D': '#fef2f2', 'F': '#fef2f2' }[r] || '#f1f5f9');
const statusLabel = (s) => ({ pending: '⏳ قيد المراجعة', reviewed: '✅ تمت المراجعة', rejected: '❌ مرفوض' }[s] || s);
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
</script>

<template>
<BusinessLayout title="التصنيف الائتماني">
    <div class="cr-grid">
        <!-- Header -->
        <div class="cr-header">
            <div>
                <h2>📊 التصنيف الائتماني للعملاء</h2>
                <p class="cr-desc">اطلب تقييم الجدارة الائتمانية لعملائك من الإدارة</p>
            </div>
            <button class="cr-btn-new" @click="showForm = !showForm">
                {{ showForm ? '✕ إلغاء' : '+ طلب تصنيف جديد' }}
            </button>
        </div>

        <!-- Rating Legend -->
        <div class="cr-legend">
            <div v-for="r in ['A+','A','B+','B','C','D','F']" :key="r" class="cr-legend-item" :style="{background: ratingBg(r)}">
                <span class="cr-legend-grade" :style="{color: ratingColor(r)}">{{ r }}</span>
                <span class="cr-legend-label">{{ {'A+':'ممتاز+','A':'ممتاز','B+':'جيد جداً','B':'جيد','C':'مقبول','D':'ضعيف','F':'سيء'}[r] }}</span>
            </div>
        </div>

        <!-- Request Form -->
        <div v-if="showForm" class="cr-form-wrap">
            <h3>📋 طلب تصنيف ائتماني</h3>

            <!-- SDB Search -->
            <div class="cr-search-section">
                <div class="cr-search-bar">
                    <span>🔍</span>
                    <input v-model="searchQuery" placeholder="ابحث عن عميل SDB..." class="cr-search-input" />
                </div>
                <div v-if="showResults" class="cr-search-results">
                    <div v-for="r in searchResults" :key="r.id" class="cr-search-item" @click="selectCustomer(r)">
                        <div class="cr-avatar">{{ (r.full_name || '?')[0] }}</div>
                        <div>
                            <div class="cr-sname">{{ r.full_name }}</div>
                            <div class="cr-smeta"><span v-if="r.phone">📱{{ r.phone }}</span> <span v-if="r.account_number">🏦{{ r.account_number }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cr-form-grid">
                <div class="cr-f"><label>اسم العميل *</label><input v-model="form.customer_name" /></div>
                <div class="cr-f"><label>الهاتف</label><input v-model="form.customer_phone" /></div>
                <div class="cr-f"><label>البريد</label><input v-model="form.customer_email" /></div>
                <div class="cr-f"><label>رقم الحساب</label><input v-model="form.customer_account_number" /></div>
            </div>
            <div class="cr-f full"><label>ملاحظات</label><textarea v-model="form.business_notes" rows="2" placeholder="سبب الطلب..."></textarea></div>
            <button class="cr-submit" @click="submit" :disabled="!form.customer_name || form.processing">
                {{ form.processing ? '⏳' : '📤 إرسال الطلب' }}
            </button>
        </div>

        <!-- Requests List -->
        <div class="cr-list">
            <div v-for="req in requests?.data" :key="req.id" class="cr-card" :class="{'cr-reviewed': req.status === 'reviewed'}">
                <div class="cr-card-header">
                    <div class="cr-card-name">{{ req.customer_name }}</div>
                    <div class="cr-card-status">{{ statusLabel(req.status) }}</div>
                </div>
                <div class="cr-card-meta">
                    <span v-if="req.customer_phone">📱 {{ req.customer_phone }}</span>
                    <span v-if="req.customer_email">📧 {{ req.customer_email }}</span>
                    <span v-if="req.customer_account_number">🏦 {{ req.customer_account_number }}</span>
                </div>
                <div v-if="req.business_notes" class="cr-card-notes">{{ req.business_notes }}</div>

                <!-- Rating Result -->
                <div v-if="req.status === 'reviewed' && req.credit_rating" class="cr-result">
                    <div class="cr-result-grade" :style="{color: ratingColor(req.credit_rating), background: ratingBg(req.credit_rating)}">
                        {{ req.credit_rating }}
                    </div>
                    <div class="cr-result-info">
                        <div class="cr-result-score">النقاط: <strong>{{ req.credit_score }}/100</strong></div>
                        <div v-if="req.admin_notes" class="cr-result-notes">{{ req.admin_notes }}</div>
                        <div class="cr-result-date">تمت المراجعة: {{ fmtDate(req.reviewed_at) }}</div>
                    </div>
                </div>

                <div v-if="req.status === 'rejected'" class="cr-rejected">
                    <div v-if="req.admin_notes">{{ req.admin_notes }}</div>
                    <div v-else>تم رفض الطلب</div>
                </div>

                <div class="cr-card-date">{{ fmtDate(req.created_at) }}</div>
            </div>
            <div v-if="!requests?.data?.length" class="cr-empty">لا توجد طلبات تصنيف — أرسل أول طلب</div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.cr-grid { display: flex; flex-direction: column; gap: 20px; }
.cr-header { display: flex; justify-content: space-between; align-items: center; }
.cr-header h2 { font-size: 18px; font-weight: 900; color: #1a1a2e; }
.cr-desc { font-size: 12px; color: #6b7280; margin-top: 2px; }
.cr-btn-new { background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; }
.cr-btn-new:hover { background: #0d5c1e; }

.cr-legend { display: flex; gap: 8px; flex-wrap: wrap; }
.cr-legend-item { display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 10px; }
.cr-legend-grade { font-size: 16px; font-weight: 900; }
.cr-legend-label { font-size: 11px; color: #64748b; }

.cr-form-wrap { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 24px; }
.cr-form-wrap h3 { font-size: 15px; font-weight: 800; margin-bottom: 12px; color: #1a1a2e; }
.cr-search-section { position: relative; margin-bottom: 14px; }
.cr-search-bar { display: flex; align-items: center; background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 14px; padding: 4px 12px; gap: 8px; }
.cr-search-bar:focus-within { border-color: #106F24; }
.cr-search-input { flex: 1; border: none; background: transparent; padding: 10px 0; font-size: 13px; font-family: 'Cairo'; outline: none; }
.cr-search-results { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 4px; box-shadow: 0 8px 30px rgba(0,0,0,.1); z-index: 50; }
.cr-search-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
.cr-search-item:hover { background: #f0fdf4; }
.cr-avatar { width: 36px; height: 36px; border-radius: 10px; background: #106F24; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.cr-sname { font-size: 13px; font-weight: 700; }
.cr-smeta { font-size: 10px; color: #6b7280; display: flex; gap: 8px; }
.cr-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.cr-f { display: flex; flex-direction: column; gap: 4px; }
.cr-f.full { margin-top: 8px; }
.cr-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.cr-f input, .cr-f textarea { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; font-size: 13px; font-family: 'Cairo'; outline: none; }
.cr-f input:focus, .cr-f textarea:focus { border-color: #106F24; }
.cr-submit { margin-top: 12px; background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; font-size: 13px; }
.cr-submit:disabled { opacity: .4; }

.cr-list { display: flex; flex-direction: column; gap: 12px; }
.cr-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; }
.cr-reviewed { border-color: #bbf7d0; }
.cr-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.cr-card-name { font-size: 15px; font-weight: 800; color: #1a1a2e; }
.cr-card-status { font-size: 12px; font-weight: 600; }
.cr-card-meta { display: flex; gap: 16px; font-size: 11px; color: #6b7280; margin-bottom: 6px; }
.cr-card-notes { font-size: 12px; color: #64748b; padding: 8px 12px; background: #f8f9fb; border-radius: 8px; margin-top: 6px; }

.cr-result { display: flex; align-items: center; gap: 20px; margin-top: 12px; padding: 16px; border-radius: 14px; background: #f8f9fb; }
.cr-result-grade { font-size: 36px; font-weight: 900; padding: 12px 20px; border-radius: 14px; }
.cr-result-info { flex: 1; }
.cr-result-score { font-size: 14px; font-weight: 700; color: #1a1a2e; }
.cr-result-notes { font-size: 12px; color: #64748b; margin-top: 4px; }
.cr-result-date { font-size: 10px; color: #9ca3af; margin-top: 4px; }

.cr-rejected { margin-top: 8px; padding: 10px; background: #fef2f2; border-radius: 8px; font-size: 12px; color: #dc2626; }
.cr-card-date { font-size: 10px; color: #9ca3af; margin-top: 8px; }
.cr-empty { text-align: center; padding: 40px; color: #9ca3af; background: #fff; border-radius: 16px; border: 1px solid #e8ecf1; }
</style>
