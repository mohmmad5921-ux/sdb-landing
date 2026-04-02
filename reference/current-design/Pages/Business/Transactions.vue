<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ transactions: Object, filters: Object });
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');
const from = ref(props.filters?.from || '');
const to = ref(props.filters?.to || '');
const selectedTx = ref(null);

const applyFilters = () => {
    router.get('/business/transactions', { type: type.value, status: status.value, from: from.value, to: to.value }, { preserveState: true });
};

const fmt = (a, s = '$') => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + s;
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';

// Smart type detection — if description mentions سداد/قسط, show as installment payment
const getSmartType = (tx) => {
    const desc = tx.description || '';
    if (desc.includes('سداد') && desc.includes('قسط')) return 'installment_payment';
    return tx.type;
};

const typeLabel = (t) => ({
    transfer: 'تحويل',
    deposit: 'إيداع',
    withdrawal: 'سحب',
    card_payment: 'دفع بطاقة',
    exchange: 'تحويل عملة',
    refund: 'استرداد',
    installment: 'قسط',
    installment_payment: '💳 سداد أقساط',
    pos: 'نقطة بيع',
    fee: 'رسوم',
}[t] || t);

const typeIcon = (t) => ({
    transfer: '↗️',
    deposit: '💰',
    withdrawal: '🏧',
    card_payment: '💳',
    exchange: '🔄',
    refund: '↩️',
    installment: '📱',
    installment_payment: '📋',
    pos: '🏪',
    fee: '💸',
}[t] || '💸');

const statusBadge = (s) => ({ completed: 'st-green', pending: 'st-yellow', failed: 'st-red', cancelled: 'st-red' }[s] || '');
const statusLabel = (s) => ({ completed: 'مكتملة', pending: 'معلقة', failed: 'فاشلة', cancelled: 'ملغية' }[s] || s);

// Check if this is an incoming payment
const isIncoming = (tx) => {
    if (tx.is_incoming !== undefined) return tx.is_incoming;
    const desc = tx.description || '';
    return desc.includes('سداد') && desc.includes('قسط');
};

// Extract contract number from description
const extractContract = (desc) => {
    if (!desc) return null;
    const match = desc.match(/CR-[A-Z0-9]+/);
    return match ? match[0] : null;
};

const openDetail = (tx) => {
    selectedTx.value = tx;
};
const closeDetail = () => {
    selectedTx.value = null;
};
</script>

<template>
<BusinessLayout title="العمليات">
    <div class="bt-grid">
        <!-- Filters -->
        <div class="bt-filters">
            <select v-model="type" @change="applyFilters" class="bt-select">
                <option value="">كل الأنواع</option>
                <option value="transfer">تحويل / سداد أقساط</option>
                <option value="deposit">إيداع</option>
                <option value="card_payment">دفع بطاقة</option>
                <option value="exchange">تحويل عملة</option>
            </select>
            <select v-model="status" @change="applyFilters" class="bt-select">
                <option value="">كل الحالات</option>
                <option value="completed">مكتملة</option>
                <option value="pending">معلقة</option>
                <option value="failed">فاشلة</option>
            </select>
            <input v-model="from" type="date" @change="applyFilters" class="bt-input" />
            <input v-model="to" type="date" @change="applyFilters" class="bt-input" />
        </div>

        <!-- Table -->
        <div class="bt-table-wrap">
            <table class="bt-table">
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>الوصف</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tx in transactions?.data" :key="tx.id" class="bt-row" @click="openDetail(tx)">
                        <td>
                            <div class="bt-type">
                                <span class="bt-icon">{{ typeIcon(getSmartType(tx)) }}</span>
                                <span :class="{ 'bt-installment-label': isIncoming(tx) }">{{ typeLabel(getSmartType(tx)) }}</span>
                            </div>
                        </td>
                        <td>
                            <span :class="['bt-amount', isIncoming(tx) ? 'green' : '']">
                                {{ isIncoming(tx) ? '+' : '-' }}{{ fmt(tx.amount, tx.currency_code || '$') }}
                            </span>
                        </td>
                        <td class="bt-desc">
                            <div v-if="tx.sender_name && isIncoming(tx)" class="bt-sender">👤 {{ tx.sender_name }}</div>
                            <div>{{ tx.description || '—' }}</div>
                        </td>
                        <td><span :class="['bt-status', statusBadge(tx.status)]">{{ statusLabel(tx.status) }}</span></td>
                        <td class="bt-date">{{ fmtDate(tx.created_at) }}</td>
                        <td>
                            <button class="bt-detail-btn" @click.stop="openDetail(tx)" title="تفاصيل">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="!transactions?.data?.length" class="bt-empty">لا توجد عمليات</div>
        </div>

        <!-- Pagination -->
        <div v-if="transactions?.last_page > 1" class="bt-pagination">
            <a v-for="link in transactions.links" :key="link.label"
               :href="link.url"
               :class="['bt-page', link.active && 'bt-page-active', !link.url && 'bt-page-disabled']"
               @click.prevent="link.url && router.get(link.url)"
               v-html="link.label">
            </a>
        </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="selectedTx" class="modal-overlay" @click.self="closeDetail">
            <div class="modal-card">
                <button class="modal-close" @click="closeDetail">✕</button>

                <!-- Header -->
                <div class="modal-header" :class="isIncoming(selectedTx) ? 'incoming' : 'outgoing'">
                    <div class="modal-header-icon">{{ isIncoming(selectedTx) ? '💰' : '↗️' }}</div>
                    <div class="modal-header-label">{{ isIncoming(selectedTx) ? 'وارد — سداد أقساط' : typeLabel(getSmartType(selectedTx)) }}</div>
                    <div class="modal-header-amount" :class="isIncoming(selectedTx) ? 'amt-green' : ''">
                        {{ isIncoming(selectedTx) ? '+' : '-' }}{{ fmt(selectedTx.amount, selectedTx.currency_code || '$') }}
                    </div>
                </div>

                <!-- Details -->
                <div class="modal-body">
                    <!-- Customer Info -->
                    <div v-if="selectedTx.sender_name && isIncoming(selectedTx)" class="modal-section customer-card">
                        <div class="customer-avatar">👤</div>
                        <div class="customer-info">
                            <div class="customer-name">{{ selectedTx.sender_name }}</div>
                            <div class="customer-sub">عميل — دفع أقساط</div>
                        </div>
                    </div>

                    <div class="modal-section">
                        <h4>📋 تفاصيل العملية</h4>
                        <div class="modal-row">
                            <span class="modal-label">رقم المرجع</span>
                            <span class="modal-value mono">{{ selectedTx.reference_number || '—' }}</span>
                        </div>
                        <div v-if="selectedTx.sender_name && isIncoming(selectedTx)" class="modal-row">
                            <span class="modal-label">العميل</span>
                            <span class="modal-value" style="font-weight:800;">{{ selectedTx.sender_name }}</span>
                        </div>
                        <div v-if="selectedTx.receiver_name && !isIncoming(selectedTx)" class="modal-row">
                            <span class="modal-label">المستلم</span>
                            <span class="modal-value">{{ selectedTx.receiver_name }}</span>
                        </div>
                        <div class="modal-row">
                            <span class="modal-label">الوصف</span>
                            <span class="modal-value">{{ selectedTx.description || '—' }}</span>
                        </div>
                        <div class="modal-row">
                            <span class="modal-label">العملة</span>
                            <span class="modal-value">{{ selectedTx.currency_code || '—' }}</span>
                        </div>
                        <div class="modal-row">
                            <span class="modal-label">التاريخ</span>
                            <span class="modal-value">{{ fmtDate(selectedTx.created_at) }}</span>
                        </div>
                        <div class="modal-row">
                            <span class="modal-label">الحالة</span>
                            <span :class="['bt-status', statusBadge(selectedTx.status)]">{{ statusLabel(selectedTx.status) }}</span>
                        </div>
                    </div>

                    <!-- Contract Info (if installment payment) -->
                    <div v-if="extractContract(selectedTx.description)" class="modal-section contract-section">
                        <h4>📝 معلومات العقد</h4>
                        <div class="modal-row">
                            <span class="modal-label">رقم العقد</span>
                            <span class="modal-value mono highlight">{{ extractContract(selectedTx.description) }}</span>
                        </div>
                        <div class="modal-row">
                            <span class="modal-label">النوع</span>
                            <span class="modal-value">
                                <span class="badge-installment">💳 سداد أقساط ائتمان</span>
                            </span>
                        </div>
                    </div>

                    <div class="modal-footer-note">
                        لعرض تفاصيل العقد والعميل، انتقل إلى صفحة <strong>عقود الائتمان</strong> من القائمة الجانبية
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</BusinessLayout>
</template>

<style scoped>
.bt-grid { display: flex; flex-direction: column; gap: 16px; }
.bt-filters { display: flex; gap: 10px; flex-wrap: wrap; }
.bt-select, .bt-input { background: #f1f5f9; border: 1px solid #d1d5db; border-radius: 10px; padding: 8px 12px; color: #1a1a2e; font-size: 12px; font-family: 'Cairo', sans-serif; outline: none; min-width: 120px; }
.bt-select:focus, .bt-input:focus { border-color: #6366f1; }
.bt-select option { background: #f8f9fb; color: #1a1a2e; }

.bt-table-wrap { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; overflow: hidden; }
.bt-table { width: 100%; border-collapse: collapse; }
.bt-table th { text-align: right; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #6b7280; border-bottom: 1px solid #e8ecf1; text-transform: uppercase; letter-spacing: .5px; }
.bt-row { transition: background .2s; cursor: pointer; }
.bt-row:hover { background: #f8fafc; }
.bt-row td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #f8f9fb; }
.bt-type { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.bt-icon { font-size: 16px; }
.bt-installment-label { color: #106F24; font-weight: 800; }
.bt-amount { font-weight: 800; font-size: 14px; }
.bt-amount.green { color: #10b981; }
.bt-desc { color: #6b7280; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bt-date { font-size: 11px; color: #6b7280; }
.bt-status { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 8px; }
.st-green { background: rgba(16,185,129,.1); color: #10b981; }
.st-yellow { background: rgba(245,158,11,.1); color: #f59e0b; }
.st-red { background: rgba(239,68,68,.1); color: #ef4444; }
.bt-empty { text-align: center; padding: 40px; color: #9ca3af; font-size: 14px; }
.bt-pagination { display: flex; justify-content: center; gap: 4px; }
.bt-page { padding: 6px 12px; border-radius: 8px; font-size: 12px; color: #6b7280; background: #f8f9fb; border: 1px solid #e8ecf1; text-decoration: none; cursor: pointer; }
.bt-page-active { background: rgba(99,102,241,.15); color: #6366f1; border-color: rgba(99,102,241,.3); }
.bt-page-disabled { opacity: .3; cursor: default; }

.bt-detail-btn { background: none; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px 8px; cursor: pointer; color: #6b7280; transition: all .2s; }
.bt-detail-btn:hover { background: #f0fdf4; color: #106F24; border-color: #a7f3d0; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-card { background: #fff; border-radius: 20px; width: 90%; max-width: 480px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,.25); animation: modalIn .3s ease; direction: rtl; position: relative; }
@keyframes modalIn { from { opacity: 0; transform: scale(.9) translateY(20px); } to { opacity: 1; transform: none; } }
.modal-close { position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,.2); border: none; color: #fff; font-size: 16px; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; z-index: 10; transition: background .2s; }
.modal-close:hover { background: rgba(255,255,255,.4); }

.modal-header { padding: 28px 24px; text-align: center; }
.modal-header.incoming { background: linear-gradient(135deg, #106F24, #22C55E); color: #fff; }
.modal-header.outgoing { background: linear-gradient(135deg, #1e293b, #475569); color: #fff; }
.modal-header-icon { font-size: 36px; margin-bottom: 8px; }
.modal-header-label { font-size: 14px; opacity: .9; margin-bottom: 8px; }
.modal-header-amount { font-size: 28px; font-weight: 900; }
.modal-header-amount.amt-green { color: #bbf7d0; }

.modal-body { padding: 20px 24px; }
.modal-section { margin-bottom: 20px; }
.modal-section h4 { font-size: 14px; font-weight: 800; color: #1e293b; margin: 0 0 12px; }
.modal-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.modal-label { font-size: 13px; color: #6b7280; }
.modal-value { font-size: 13px; font-weight: 600; color: #1e293b; }
.modal-value.mono { font-family: monospace; letter-spacing: 1px; }
.modal-value.highlight { color: #106F24; }

.contract-section { background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; }
.contract-section h4 { color: #106F24; }
.contract-section .modal-row { border-color: #d1fae5; }

.badge-installment { background: linear-gradient(135deg, #106F24, #22C55E); color: #fff; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }

.modal-footer-note { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; font-size: 12px; color: #6b7280; text-align: center; line-height: 1.6; }
.modal-footer-note strong { color: #106F24; }

.bt-sender { font-size: 12px; font-weight: 700; color: #106F24; margin-bottom: 2px; }
.bt-desc { color: #6b7280; max-width: 240px; }
.bt-desc > div:last-child { font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.customer-card { display: flex; align-items: center; gap: 14px; background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid #a7f3d0; border-radius: 14px; padding: 16px; }
.customer-avatar { font-size: 36px; width: 54px; height: 54px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #22C55E; }
.customer-info { flex: 1; }
.customer-name { font-size: 17px; font-weight: 900; color: #1e293b; }
.customer-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
</style>
