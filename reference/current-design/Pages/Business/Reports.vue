<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ period: String, stats: Object });
const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const activePeriod = ref(props.period || 'month');
const setPeriod = (p) => { activePeriod.value = p; router.get('/business/reports', { period: p }, { preserveState: true }); };
</script>

<template>
<BusinessLayout title="التقارير">
    <div class="rp-grid">
        <div class="rp-period-tabs">
            <button v-for="p in [{v:'today',l:'اليوم'},{v:'week',l:'الأسبوع'},{v:'month',l:'الشهر'},{v:'year',l:'السنة'}]"
                    :key="p.v" :class="['rp-tab', activePeriod === p.v && 'rp-tab-active']" @click="setPeriod(p.v)">{{ p.l }}</button>
        </div>

        <div class="rp-cards">
            <div class="rp-card rp-green"><div class="rp-card-icon">📈</div><div><div class="rp-card-label">الإيرادات</div><div class="rp-card-value">{{ fmt(stats?.revenue) }}</div></div></div>
            <div class="rp-card rp-red"><div class="rp-card-icon">📉</div><div><div class="rp-card-label">المصروفات</div><div class="rp-card-value">{{ fmt(stats?.expenses) }}</div></div></div>
            <div class="rp-card" :class="Number(stats?.profit) >= 0 ? 'rp-green' : 'rp-red'"><div class="rp-card-icon">💎</div><div><div class="rp-card-label">صافي الربح</div><div class="rp-card-value">{{ fmt(stats?.profit) }}</div></div></div>
            <div class="rp-card"><div class="rp-card-icon">🧾</div><div><div class="rp-card-label">فواتير محصّلة</div><div class="rp-card-value">{{ fmt(stats?.invoicePaid) }}</div></div></div>
            <div class="rp-card"><div class="rp-card-icon">📱</div><div><div class="rp-card-label">أقساط محصّلة</div><div class="rp-card-value">{{ fmt(stats?.installmentCollected) }}</div></div></div>
        </div>

        <div class="rp-chart-placeholder">
            <div class="rp-chart-icon">📊</div>
            <div>الرسوم البيانية ستكون متاحة قريباً</div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.rp-grid { display: flex; flex-direction: column; gap: 20px; }
.rp-period-tabs { display: flex; gap: 6px; background: #fff; border-radius: 12px; padding: 4px; width: fit-content; }
.rp-tab { background: none; border: none; padding: 8px 20px; border-radius: 10px; color: #6b7280; font-family: 'Cairo'; font-size: 12px; font-weight: 700; cursor: pointer; transition: all .2s; }
.rp-tab-active { background: rgba(99,102,241,.15); color: #6366f1; }
.rp-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.rp-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 14px; }
.rp-green { background: rgba(16,185,129,.04); border-color: rgba(16,185,129,.1); }
.rp-red { background: rgba(239,68,68,.04); border-color: rgba(239,68,68,.1); }
.rp-card-icon { font-size: 28px; }
.rp-card-label { font-size: 11px; color: #6b7280; font-weight: 600; }
.rp-card-value { font-size: 22px; font-weight: 900; margin-top: 4px; }
.rp-chart-placeholder { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 60px; text-align: center; color: #9ca3af; }
.rp-chart-icon { font-size: 48px; margin-bottom: 8px; }
@media (max-width: 900px) { .rp-cards { grid-template-columns: repeat(2, 1fr); } }
</style>
