<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
defineOptions({ layout: AdminLayout });
const p = defineProps({ currencies: Array, holdings: Array, stats: Object, recentTransactions: Array, settings: Object, prices: Object });
const flash = computed(() => usePage().props.flash || {});
import { usePage } from '@inertiajs/vue3';

const settingsForm = ref({ ...p.settings });
const saving = ref(false);

const saveSettings = () => {
  saving.value = true;
  const form = useForm(settingsForm.value);
  form.post(route('admin.crypto.settings'), {
    preserveScroll: true,
    onFinish: () => saving.value = false,
  });
};

const toggleCoin = (id) => {
  router.post(route('admin.crypto.toggle', id), {}, { preserveScroll: true });
};

const fc = (n, decimals = 2) => Number(n || 0).toLocaleString('en', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
</script>
<template>
<Head title="إدارة الكريبتو" />
<div class="cr">
  <!-- Header -->
  <div class="cr-hdr">
    <h1 class="cr-title">₿ إدارة العملات الرقمية</h1>
    <p class="cr-sub">Crypto Admin Panel — التحكم الكامل بالعملات الرقمية</p>
  </div>

  <!-- Stats -->
  <div class="cr-stats">
    <div class="cr-st"><div class="cr-st-icon">👥</div><div class="cr-st-val">{{ p.stats.totalTraders }}</div><div class="cr-st-lbl">متداولين نشطين</div></div>
    <div class="cr-st"><div class="cr-st-icon">📊</div><div class="cr-st-val">{{ p.stats.txCount30d }}</div><div class="cr-st-lbl">عملية (30 يوم)</div></div>
    <div class="cr-st"><div class="cr-st-icon">💰</div><div class="cr-st-val">€{{ fc(p.stats.volume30d) }}</div><div class="cr-st-lbl">حجم التداول (30 يوم)</div></div>
    <div class="cr-st"><div class="cr-st-icon">🪙</div><div class="cr-st-val">{{ p.stats.activeCurrencies }}</div><div class="cr-st-lbl">عملات نشطة</div></div>
  </div>

  <!-- Live Prices & Control -->
  <div class="cr-section">
    <h2 class="cr-sh">📈 الأسعار الحية والتحكم</h2>
    <div class="cr-tbl-wrap">
      <table class="cr-tbl">
        <thead>
          <tr><th>العملة</th><th>السعر (USD)</th><th>تغير 24س</th><th>الحالة</th><th>تحكم</th></tr>
        </thead>
        <tbody>
          <tr v-for="c in p.currencies" :key="c.id">
            <td class="cr-coin">
              <span class="cr-code">{{ c.code }}</span>
              <span class="cr-name">{{ c.name_ar }}</span>
            </td>
            <td class="cr-price">${{ fc(c.price_usd) }}</td>
            <td :class="['cr-change', c.change_24h >= 0 ? 'cr-up' : 'cr-down']">
              {{ c.change_24h >= 0 ? '▲' : '▼' }} {{ Math.abs(c.change_24h || 0).toFixed(2) }}%
            </td>
            <td>
              <span :class="['cr-badge', c.is_active ? 'cr-badge-on' : 'cr-badge-off']">
                {{ c.is_active ? 'نشط' : 'معطّل' }}
              </span>
            </td>
            <td>
              <button @click="toggleCoin(c.id)" :class="['cr-toggle-btn', c.is_active ? 'cr-btn-danger' : 'cr-btn-success']">
                {{ c.is_active ? 'تعطيل' : 'تفعيل' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Holdings -->
  <div class="cr-section" v-if="p.holdings?.length">
    <h2 class="cr-sh">💼 أرصدة المستخدمين</h2>
    <div class="cr-tbl-wrap">
      <table class="cr-tbl">
        <thead><tr><th>العملة</th><th>عدد الحاملين</th><th>إجمالي الرصيد</th></tr></thead>
        <tbody>
          <tr v-for="h in p.holdings" :key="h.code">
            <td class="cr-code">{{ h.code }}</td>
            <td>{{ h.holders }}</td>
            <td class="cr-bold">{{ fc(h.total_balance, 6) }} {{ h.code }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Settings -->
  <div class="cr-section">
    <h2 class="cr-sh">⚙️ إعدادات التداول</h2>
    <div class="cr-settings">
      <div class="cr-set-row">
        <label>نسبة العمولة (Spread) %</label>
        <input v-model.number="settingsForm.spread_percent" type="number" step="0.1" min="0" max="10" class="cr-input" />
      </div>
      <div class="cr-set-row">
        <label>الحد الأدنى للتداول (EUR)</label>
        <input v-model.number="settingsForm.min_trade_eur" type="number" step="1" min="1" class="cr-input" />
      </div>
      <div class="cr-set-row">
        <label>الحد الأقصى للتداول (EUR)</label>
        <input v-model.number="settingsForm.max_trade_eur" type="number" step="100" min="100" class="cr-input" />
      </div>
      <button @click="saveSettings" class="cr-save-btn" :disabled="saving">
        {{ saving ? 'جاري الحفظ...' : '💾 حفظ الإعدادات' }}
      </button>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="cr-section" v-if="p.recentTransactions?.length">
    <h2 class="cr-sh">📋 آخر عمليات الكريبتو</h2>
    <div class="cr-tbl-wrap">
      <table class="cr-tbl">
        <thead><tr><th>المستخدم</th><th>النوع</th><th>المبلغ</th><th>العملة</th><th>الحالة</th><th>التاريخ</th></tr></thead>
        <tbody>
          <tr v-for="t in p.recentTransactions" :key="t.id">
            <td>{{ t.from_account?.user?.full_name || '—' }}</td>
            <td><span class="cr-tag">{{ t.type }}</span></td>
            <td class="cr-bold">{{ fc(t.amount) }}</td>
            <td>{{ t.from_account?.currency?.code || t.to_account?.currency?.code || '—' }}</td>
            <td><span :class="['cr-st-badge', 'cr-st-'+t.status]">{{ t.status }}</span></td>
            <td class="cr-date">{{ new Date(t.created_at).toLocaleString('ar') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>
<style scoped>
.cr { padding: 24px; direction: rtl; max-width: 1400px; margin: 0 auto; }
.cr-hdr { margin-bottom: 24px; }
.cr-title { font-size: 24px; font-weight: 900; color: #0f172a; margin: 0; }
.cr-sub { font-size: 13px; color: #94a3b8; margin-top: 4px; }

.cr-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px; }
.cr-st { background: #fff; border: 1px solid #e8ebf0; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; align-items: center; transition: transform .2s; }
.cr-st:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
.cr-st-icon { font-size: 28px; margin-bottom: 8px; }
.cr-st-val { font-size: 26px; font-weight: 900; color: #0f172a; }
.cr-st-lbl { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 4px; }

.cr-section { background: #fff; border: 1px solid #e8ebf0; border-radius: 16px; padding: 20px; margin-bottom: 20px; }
.cr-sh { font-size: 16px; font-weight: 800; color: #1e293b; margin: 0 0 16px 0; }

.cr-tbl-wrap { overflow-x: auto; }
.cr-tbl { width: 100%; border-collapse: collapse; min-width: 500px; }
.cr-tbl th { font-size: 12px; font-weight: 700; color: #64748b; text-align: right; padding: 10px 12px; background: #f8fafc; border-bottom: 2px solid #e8ebf0; }
.cr-tbl td { font-size: 13px; color: #334155; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
.cr-tbl tr:hover td { background: #fafbfe; }

.cr-coin { display: flex; align-items: center; gap: 8px; }
.cr-code { font-weight: 800; font-size: 14px; color: #0f172a; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; }
.cr-name { font-size: 12px; color: #64748b; }
.cr-price { font-weight: 700; font-size: 14px; color: #0f172a; font-family: monospace; }
.cr-change { font-weight: 700; font-size: 13px; }
.cr-up { color: #059669; }
.cr-down { color: #dc2626; }
.cr-bold { font-weight: 800; color: #0f172a; }
.cr-date { font-size: 11px; color: #94a3b8; }

.cr-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
.cr-badge-on { color: #059669; background: #ecfdf5; }
.cr-badge-off { color: #dc2626; background: #fef2f2; }

.cr-toggle-btn { padding: 5px 14px; border: none; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all .2s; }
.cr-btn-danger { background: #fef2f2; color: #dc2626; }
.cr-btn-danger:hover { background: #fee2e2; }
.cr-btn-success { background: #ecfdf5; color: #059669; }
.cr-btn-success:hover { background: #d1fae5; }

.cr-tag { font-size: 10px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #64748b; }
.cr-st-badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
.cr-st-completed { color: #059669; background: #ecfdf5; }
.cr-st-pending { color: #d97706; background: #fffbeb; }
.cr-st-failed { color: #dc2626; background: #fef2f2; }

.cr-settings { display: flex; flex-direction: column; gap: 14px; max-width: 500px; }
.cr-set-row { display: flex; align-items: center; gap: 12px; }
.cr-set-row label { flex: 1; font-size: 13px; font-weight: 700; color: #334155; }
.cr-input { width: 140px; padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-weight: 700; text-align: center; outline: none; transition: border .2s; }
.cr-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
.cr-save-btn { align-self: flex-start; padding: 10px 28px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 800; cursor: pointer; transition: all .2s; }
.cr-save-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(245,158,11,0.3); }
.cr-save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
