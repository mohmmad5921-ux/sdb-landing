<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ invoices: Object, stats: Object, filters: Object });
const showCreate = ref(false);
const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
const statusLabel = (s) => ({ draft: 'مسودة', sent: 'مرسلة', paid: 'مدفوعة', overdue: 'متأخرة', cancelled: 'ملغية' }[s] || s);
const statusClass = (s) => ({ draft: 'si-blue', sent: 'si-yellow', paid: 'si-green', overdue: 'si-red', cancelled: 'si-gray' }[s] || '');

const form = useForm({
    customer_name: '', customer_email: '', customer_phone: '',
    amount: '', tax_amount: 0, currency: 'USD',
    description: '', due_date: '', payment_type: 'full',
});

// Customer search
const searchQuery = ref('');
const searchResults = ref([]);
const showResults = ref(false);
const searching = ref(false);
let searchTimer = null;

watch(searchQuery, (val) => {
    if (val.length < 2) { searchResults.value = []; showResults.value = false; return; }
    clearTimeout(searchTimer);
    searching.value = true;
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/business/search-customer?q=${encodeURIComponent(val)}`);
            searchResults.value = await res.json();
            showResults.value = searchResults.value.length > 0;
        } catch (e) { searchResults.value = []; }
        searching.value = false;
    }, 300);
});

const selectCustomer = (c) => {
    form.customer_name = c.full_name;
    form.customer_email = c.email || '';
    form.customer_phone = c.phone || '';
    searchQuery.value = '';
    showResults.value = false;
};

const totalAmount = computed(() => Number(form.amount || 0) + Number(form.tax_amount || 0));
const submit = () => { form.post(route('business.invoices.create'), { onSuccess: () => { showCreate.value = false; form.reset(); } }); };
const sendInvoice = (id) => router.post(`/business/invoices/${id}/send`);
const filterStatus = (s) => router.get('/business/invoices', { status: s }, { preserveState: true });
</script>

<template>
<BusinessLayout title="الفواتير">
    <div class="bi-grid">
        <!-- Stats -->
        <div class="bi-stats">
            <div class="bi-stat" @click="filterStatus('')">
                <div class="bi-stat-num">{{ stats?.total || 0 }}</div>
                <div class="bi-stat-label">الإجمالي</div>
            </div>
            <div class="bi-stat si-green-bg" @click="filterStatus('paid')">
                <div class="bi-stat-num">{{ stats?.paid || 0 }}</div>
                <div class="bi-stat-label">مدفوعة</div>
            </div>
            <div class="bi-stat si-yellow-bg" @click="filterStatus('sent')">
                <div class="bi-stat-num">{{ stats?.pending || 0 }}</div>
                <div class="bi-stat-label">معلقة</div>
            </div>
            <div class="bi-stat si-red-bg" @click="filterStatus('overdue')">
                <div class="bi-stat-num">{{ stats?.overdue || 0 }}</div>
                <div class="bi-stat-label">متأخرة</div>
            </div>
            <div class="bi-stat">
                <div class="bi-stat-num">{{ fmt(stats?.paidAmount) }}</div>
                <div class="bi-stat-label">إجمالي المحصّل</div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bi-actions">
            <button class="bi-create-btn" @click="showCreate = !showCreate">
                {{ showCreate ? '✕ إلغاء' : '+ فاتورة جديدة' }}
            </button>
        </div>

        <!-- Create Form -->
        <div v-if="showCreate" class="bi-form-wrap">
            <div class="bi-form">
                <h3 class="bi-form-title">🧾 فاتورة جديدة</h3>

                <!-- SDB Customer Search -->
                <div class="bi-search-section">
                    <div class="bi-search-bar">
                        <div class="bi-search-icon">🔍</div>
                        <input v-model="searchQuery"
                               placeholder="ابحث عن عميل SDB... (هاتف، إيميل، رقم حساب، اسم)"
                               class="bi-search-input" />
                        <div v-if="searching" class="bi-search-loading">⏳</div>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div v-if="showResults" class="bi-search-results">
                        <div v-for="r in searchResults" :key="r.id"
                             class="bi-search-item" @click="selectCustomer(r)">
                            <div class="bi-search-avatar">{{ (r.full_name || '?')[0] }}</div>
                            <div class="bi-search-info">
                                <div class="bi-search-name">{{ r.full_name }}</div>
                                <div class="bi-search-meta">
                                    <span v-if="r.phone">📱 {{ r.phone }}</span>
                                    <span v-if="r.email">📧 {{ r.email }}</span>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bi-form-grid">
                    <div class="bi-f"><label>اسم العميل *</label><input v-model="form.customer_name" placeholder="اسم العميل" /></div>
                    <div class="bi-f"><label>البريد</label><input v-model="form.customer_email" type="email" placeholder="email@..." /></div>
                    <div class="bi-f"><label>الهاتف</label><input v-model="form.customer_phone" placeholder="+963..." /></div>
                    <div class="bi-f"><label>المبلغ *</label><input v-model="form.amount" type="number" step="0.01" placeholder="0.00" /></div>
                    <div class="bi-f"><label>الضريبة</label><input v-model="form.tax_amount" type="number" step="0.01" /></div>
                    <div class="bi-f">
                        <label>العملة</label>
                        <select v-model="form.currency">
                            <option value="USD">$ دولار أمريكي</option>
                            <option value="SYP">ل.س ليرة سورية</option>
                            <option value="EUR">€ يورو</option>
                            <option value="DKK">kr كرون دانماركي</option>
                            <option value="TRY">₺ ليرة تركية</option>
                            <option value="GBP">£ جنيه إسترليني</option>
                        </select>
                    </div>
                    <div class="bi-f"><label>تاريخ الاستحقاق</label><input v-model="form.due_date" type="date" /></div>
                    <div class="bi-f">
                        <label>نوع الدفع</label>
                        <select v-model="form.payment_type"><option value="full">كامل</option><option value="partial">جزئي</option><option value="installment">أقساط</option></select>
                    </div>
                </div>
                <div class="bi-f full"><label>الوصف</label><textarea v-model="form.description" rows="2" placeholder="تفاصيل الفاتورة..."></textarea></div>

                <div class="bi-form-footer">
                    <div class="bi-total">الإجمالي: <strong>{{ fmt(totalAmount) }} {{ form.currency }}</strong></div>
                    <button class="bi-submit" @click="submit" :disabled="!form.customer_name || !form.amount || form.processing">
                        {{ form.processing ? '⏳' : '✓ إنشاء الفاتورة' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="bi-table-wrap">
            <table class="bi-table">
                <thead>
                    <tr><th>الرقم</th><th>العميل</th><th>المبلغ</th><th>الحالة</th><th>الاستحقاق</th><th>إجراءات</th></tr>
                </thead>
                <tbody>
                    <tr v-for="inv in invoices?.data" :key="inv.id" class="bi-row">
                        <td class="mono">{{ inv.invoice_number }}</td>
                        <td>
                            <div class="bi-cust-name">{{ inv.customer_name }}</div>
                            <div class="bi-cust-email">{{ inv.customer_email || '' }}</div>
                        </td>
                        <td class="bi-amount">{{ fmt(inv.total_amount) }} {{ inv.currency }}</td>
                        <td><span :class="['bi-badge', statusClass(inv.status)]">{{ statusLabel(inv.status) }}</span></td>
                        <td class="bi-date">{{ fmtDate(inv.due_date) }}</td>
                        <td>
                            <div class="bi-row-actions">
                                <button v-if="inv.status === 'draft'" class="bi-act-btn" @click="sendInvoice(inv.id)">📤 إرسال</button>
                                <button v-if="inv.payment_link" class="bi-act-btn bi-act-copy" @click="navigator.clipboard.writeText(inv.payment_link)">🔗 نسخ الرابط</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!invoices?.data?.length" class="bi-empty">لا توجد فواتير — أنشئ أول فاتورة 🧾</div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.bi-grid { display: flex; flex-direction: column; gap: 16px; }
.bi-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
.bi-stat { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 16px; text-align: center; cursor: pointer; transition: all .2s; }
.bi-stat:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.si-green-bg { background: rgba(16,185,129,.05); border-color: rgba(16,185,129,.15); }
.si-yellow-bg { background: rgba(245,158,11,.05); border-color: rgba(245,158,11,.15); }
.si-red-bg { background: rgba(239,68,68,.05); border-color: rgba(239,68,68,.15); }
.bi-stat-num { font-size: 20px; font-weight: 900; color: #1a1a2e; }
.bi-stat-label { font-size: 10px; color: #6b7280; font-weight: 600; margin-top: 2px; }

.bi-actions { display: flex; justify-content: flex-end; }
.bi-create-btn { background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; font-family: 'Cairo', sans-serif; cursor: pointer; transition: all .2s; }
.bi-create-btn:hover { background: #0d5c1e; box-shadow: 0 4px 12px rgba(16,111,36,.25); }

.bi-form-wrap { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 24px; }
.bi-form-title { font-size: 16px; font-weight: 800; margin-bottom: 16px; color: #1a1a2e; }

/* Search Section */
.bi-search-section { position: relative; margin-bottom: 16px; }
.bi-search-bar { display: flex; align-items: center; background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 14px; padding: 4px 12px; gap: 10px; transition: border-color .2s; }
.bi-search-bar:focus-within { border-color: #106F24; }
.bi-search-icon { font-size: 18px; flex-shrink: 0; }
.bi-search-input { flex: 1; border: none; background: transparent; padding: 12px 0; font-size: 14px; font-family: 'Cairo'; outline: none; color: #1a1a2e; }
.bi-search-input::placeholder { color: #6b7280; }
.bi-search-loading { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.bi-search-results { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-top: 4px; box-shadow: 0 12px 40px rgba(0,0,0,.12); z-index: 50; max-height: 300px; overflow-y: auto; }
.bi-search-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; cursor: pointer; transition: background .15s; border-bottom: 1px solid #f1f5f9; }
.bi-search-item:last-child { border-bottom: none; }
.bi-search-item:hover { background: #f0fdf4; }
.bi-search-avatar { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #106F24, #059669); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0; }
.bi-search-info { flex: 1; }
.bi-search-name { font-size: 14px; font-weight: 700; color: #1a1a2e; }
.bi-search-meta { display: flex; gap: 12px; font-size: 11px; color: #6b7280; margin-top: 2px; }

.bi-form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.bi-f { display: flex; flex-direction: column; gap: 4px; }
.bi-f.full { grid-column: 1 / -1; margin-top: 8px; }
.bi-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.bi-f input, .bi-f select, .bi-f textarea { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; color: #1a1a2e; font-size: 13px; font-family: 'Cairo', sans-serif; outline: none; }
.bi-f input:focus, .bi-f select:focus, .bi-f textarea:focus { border-color: #106F24; }
.bi-f textarea { resize: vertical; }
.bi-form-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; }
.bi-total { font-size: 16px; color: #64748b; }
.bi-total strong { color: #106F24; font-size: 20px; }
.bi-submit { background: #106F24; color: #fff; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 700; font-family: 'Cairo', sans-serif; cursor: pointer; font-size: 13px; }
.bi-submit:hover { background: #0d5c1e; }
.bi-submit:disabled { opacity: .4; }

.bi-table-wrap { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; overflow: hidden; }
.bi-table { width: 100%; border-collapse: collapse; }
.bi-table th { text-align: right; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #9ca3af; border-bottom: 1px solid #e8ecf1; }
.bi-row td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #f8f9fb; color: #1a1a2e; }
.bi-row:hover { background: #f8f9fb; }
.mono { font-family: 'SF Mono', monospace; font-size: 11px; color: #6b7280; }
.bi-cust-name { font-weight: 700; }
.bi-cust-email { font-size: 10px; color: #9ca3af; }
.bi-amount { font-weight: 800; }
.bi-date { font-size: 11px; color: #6b7280; }
.bi-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 8px; }
.si-blue { background: rgba(99,102,241,.1); color: #6366f1; }
.si-green { background: rgba(16,185,129,.1); color: #059669; }
.si-yellow { background: rgba(245,158,11,.1); color: #f59e0b; }
.si-red { background: rgba(239,68,68,.1); color: #ef4444; }
.si-gray { background: #f1f5f9; color: #6b7280; }
.bi-row-actions { display: flex; gap: 6px; }
.bi-act-btn { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px 10px; font-size: 11px; color: #1a1a2e; font-family: 'Cairo'; cursor: pointer; transition: all .2s; }
.bi-act-btn:hover { background: #ecfdf5; border-color: #bbf7d0; color: #106F24; }
.bi-empty { text-align: center; padding: 40px; color: #9ca3af; }
@media (max-width: 900px) { .bi-stats { grid-template-columns: repeat(2, 1fr); } .bi-form-grid { grid-template-columns: 1fr; } }
</style>
