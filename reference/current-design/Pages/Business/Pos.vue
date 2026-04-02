<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ recentPos: Array, stats: Object });
const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
const statusLabel = (s) => ({ pending: 'بانتظار الدفع', paid: 'مدفوع', expired: 'منتهي', cancelled: 'ملغي' }[s] || s);

const form = useForm({ amount: '', currency: 'USD', description: '' });
const qrResult = ref(null);
const showQR = ref(false);

const createPos = () => {
    form.post(route('business.pos.create'), {
        preserveScroll: true,
        onSuccess: (page) => {
            const posData = page.props?.flash?.posData;
            if (posData) { qrResult.value = posData; showQR.value = true; }
            form.reset();
        },
    });
};
</script>

<template>
<BusinessLayout title="نقطة البيع">
    <div class="pos-grid">
        <!-- Stats -->
        <div class="pos-stats">
            <div class="pos-stat pos-primary">
                <div class="pos-stat-icon">💰</div>
                <div><div class="pos-stat-num">{{ fmt(stats?.today) }}</div><div class="pos-stat-label">مبيعات اليوم</div></div>
            </div>
            <div class="pos-stat">
                <div class="pos-stat-icon">🧾</div>
                <div><div class="pos-stat-num">{{ stats?.todayCount || 0 }}</div><div class="pos-stat-label">عمليات اليوم</div></div>
            </div>
        </div>

        <div class="pos-main">
            <!-- POS Terminal -->
            <div class="pos-terminal">
                <h2 class="pos-title">🏪 عملية دفع جديدة</h2>
                <div class="pos-amount-wrap">
                    <input v-model="form.amount" type="number" step="0.01" placeholder="0.00" class="pos-amount-input" autofocus />
                    <select v-model="form.currency" class="pos-currency">
                        <option value="USD">USD $</option><option value="SYP">SYP ل.س</option><option value="EUR">EUR €</option><option value="DKK">DKK kr</option>
                    </select>
                </div>
                <input v-model="form.description" class="pos-desc" placeholder="وصف العملية (اختياري)" />
                <button class="pos-btn" @click="createPos" :disabled="!form.amount || form.processing">
                    {{ form.processing ? '⏳ جاري...' : '📱 إنشاء QR للدفع' }}
                </button>
            </div>

            <!-- QR Display -->
            <div v-if="showQR && qrResult" class="pos-qr-wrap">
                <h3>📱 امسح للدفع</h3>
                <div class="pos-qr-box">
                    <div class="pos-qr-placeholder">
                        <div class="pos-qr-icon">📱</div>
                        <div class="pos-qr-ref">{{ qrResult.ref }}</div>
                        <div class="pos-qr-amount">{{ fmt(qrResult.amount) }} {{ form.currency }}</div>
                    </div>
                </div>
                <div class="pos-qr-info">
                    <div>المرجع: <strong>{{ qrResult.ref }}</strong></div>
                    <div>صالح لمدة 30 دقيقة</div>
                </div>
                <button class="pos-new-btn" @click="showQR = false; qrResult = null">+ عملية جديدة</button>
            </div>
        </div>

        <!-- Recent -->
        <div class="pos-recent">
            <h3>📋 آخر العمليات</h3>
            <div class="pos-list">
                <div v-for="p in recentPos" :key="p.id" class="pos-item">
                    <div class="pos-item-icon">{{ p.status === 'paid' ? '✅' : p.status === 'pending' ? '⏳' : '❌' }}</div>
                    <div class="pos-item-info">
                        <div class="pos-item-ref">{{ p.pos_reference }}</div>
                        <div class="pos-item-date">{{ fmtDate(p.created_at) }} {{ p.description ? '· ' + p.description : '' }}</div>
                    </div>
                    <div class="pos-item-right">
                        <div class="pos-item-amount">{{ fmt(p.amount) }} {{ p.currency }}</div>
                        <div :class="['pos-item-status', p.status === 'paid' ? 'green' : '']">{{ statusLabel(p.status) }}</div>
                    </div>
                </div>
                <div v-if="!recentPos?.length" class="pos-empty">لا توجد عمليات</div>
            </div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.pos-grid { display: flex; flex-direction: column; gap: 20px; }
.pos-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; max-width: 500px; }
.pos-stat { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 16px; display: flex; align-items: center; gap: 12px; }
.pos-primary { background: linear-gradient(135deg, rgba(16,185,129,.06), rgba(16,185,129,.02)); border-color: rgba(16,185,129,.15); }
.pos-stat-icon { font-size: 24px; }
.pos-stat-num { font-size: 22px; font-weight: 900; }
.pos-stat-label { font-size: 10px; color: #6b7280; font-weight: 600; }

.pos-main { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.pos-terminal { background: #fff; border: 1px solid #e8ecf1; border-radius: 20px; padding: 28px; }
.pos-title { font-size: 16px; font-weight: 800; margin-bottom: 20px; }
.pos-amount-wrap { display: flex; gap: 8px; margin-bottom: 12px; }
.pos-amount-input { flex: 1; background: #f1f5f9; border: 2px solid #d1d5db; border-radius: 14px; padding: 16px; color: #1a1a2e; font-size: 28px; font-weight: 900; font-family: 'Cairo'; outline: none; text-align: center; }
.pos-amount-input:focus { border-color: #10b981; }
.pos-currency { background: #f1f5f9; border: 1px solid #d1d5db; border-radius: 12px; padding: 12px; color: #1a1a2e; font-family: 'Cairo'; font-size: 14px; }
.pos-currency 
.pos-desc { width: 100%; background: #f1f5f9; border: 1px solid #e8ecf1; border-radius: 12px; padding: 12px; color: #1a1a2e; font-family: 'Cairo'; font-size: 13px; margin-bottom: 16px; outline: none; box-sizing: border-box; }
.pos-btn { width: 100%; padding: 16px; background: linear-gradient(135deg, #10b981, #059669); color: #1a1a2e; border: none; border-radius: 14px; font-size: 16px; font-weight: 800; font-family: 'Cairo'; cursor: pointer; transition: all .2s; }
.pos-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(16,185,129,.3); }
.pos-btn:disabled { opacity: .4; transform: none; }

.pos-qr-wrap { background: #fff; border: 1px solid rgba(16,185,129,.15); border-radius: 20px; padding: 28px; text-align: center; }
.pos-qr-wrap h3 { font-size: 16px; font-weight: 800; margin-bottom: 16px; }
.pos-qr-box { background: #fff; border-radius: 16px; padding: 24px; margin: 0 auto 16px; max-width: 250px; }
.pos-qr-placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.pos-qr-icon { font-size: 64px; }
.pos-qr-ref { font-size: 12px; color: #333; font-family: monospace; }
.pos-qr-amount { font-size: 24px; font-weight: 900; color: #10b981; }
.pos-qr-info { font-size: 12px; color: #6b7280; margin-bottom: 12px; }
.pos-new-btn { background: #e8ecf1; border: 1px solid #d1d5db; border-radius: 10px; padding: 8px 20px; color: #1a1a2e; font-family: 'Cairo'; cursor: pointer; }

.pos-recent { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 18px; }
.pos-recent h3 { font-size: 14px; font-weight: 800; margin-bottom: 12px; }
.pos-item { display: flex; align-items: center; gap: 12px; padding: 10px 4px; border-bottom: 1px solid #f8f9fb; }
.pos-item:last-child { border-bottom: none; }
.pos-item-icon { font-size: 18px; }
.pos-item-info { flex: 1; }
.pos-item-ref { font-size: 12px; font-weight: 700; font-family: monospace; }
.pos-item-date { font-size: 10px; color: #9ca3af; }
.pos-item-right { text-align: left; }
.pos-item-amount { font-size: 14px; font-weight: 800; }
.pos-item-status { font-size: 10px; color: #6b7280; }
.pos-item-status.green { color: #10b981; }
.pos-empty { text-align: center; padding: 20px; color: #9ca3af; }
@media (max-width: 900px) { .pos-main { grid-template-columns: 1fr; } }
</style>
