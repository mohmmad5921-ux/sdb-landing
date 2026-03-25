<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: AdminLayout });
const p = defineProps({ agent: Object, remittances: Array, stats: Object, currencyBreakdown: Array, receiveCurrencyBreakdown: Array, currencies: Object, monthlyBreakdown: Array });
const f = n => Number(n||0).toLocaleString('en');
const fv = (n, sym) => sym + Number(n||0).toLocaleString('en', {minimumFractionDigits:2});
const statusLabel = s => ({ready:'جاهزة',collected:'مسلّمة',cancelled:'ملغية',expired:'منتهية'}[s]||s);
const statusColor = s => ({ready:'#d97706',collected:'#059669',cancelled:'#dc2626',expired:'#6b7280'}[s]||'#6b7280');
const getCurrSymbol = (code) => p.currencies?.[code]?.symbol || code;
const currColors = {'EUR':'#2563eb','USD':'#059669','SYP':'#d97706','USDT':'#16a34a','BTC':'#f59e0b','ETH':'#6366f1','DKK':'#dc2626','GBP':'#7c3aed','SEK':'#0891b2','TRY':'#e11d48','SOL':'#9333ea','XRP':'#334155','ADA':'#0d9488'};
const getCurrColor = (code) => currColors[code] || '#6b7280';
</script>
<template>
<Head :title="'بروفايل الوكيل — ' + agent.name_ar" />
<div class="ap">
  <!-- Header -->
  <div class="ap-header">
    <div class="ap-header-right">
      <Link :href="route('admin.agents')" class="ap-back">← العودة للوكلاء</Link>
      <div class="ap-profile">
        <div class="ap-avatar">{{ agent.name_ar?.charAt(0) }}</div>
        <div>
          <h1 class="ap-name">{{ agent.name_ar }}</h1>
          <p class="ap-name-en">{{ agent.name_en }}</p>
          <div class="ap-meta">
            <span>📍 {{ agent.governorate_ar }} — {{ agent.district_ar }}</span>
            <span v-if="agent.phone">📞 {{ agent.phone }}</span>
            <span v-if="agent.address_ar">🏠 {{ agent.address_ar }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="ap-status-area">
      <span class="ap-status" :class="agent.is_active ? 'ap-active' : 'ap-inactive'">{{ agent.is_active ? '✅ نشط' : '⛔ معطّل' }}</span>
      <span class="ap-commission">عمولة: {{ stats.commissionRate }}%</span>
    </div>
  </div>

  <!-- Overall Summary -->
  <div class="ap-sec">
    <div class="ap-sec-hdr">
      <div class="ap-sec-dot" style="background:#059669"></div>
      <h2 class="ap-sec-title">ملخص الحساب المالي</h2>
    </div>
    <div class="ap-grid-4">
      <div class="ap-kpi ap-kpi-main">
        <div class="ap-kpi-ico" style="background:#d1fae5;color:#059669">💰</div>
        <div>
          <div class="ap-kpi-val" style="color:#059669">{{ f(stats.collectedCount) }}</div>
          <div class="ap-kpi-lbl">حوالات مسلّمة</div>
        </div>
      </div>
      <div class="ap-kpi">
        <div class="ap-kpi-ico" style="background:#fef3c7;color:#d97706">⏳</div>
        <div>
          <div class="ap-kpi-val" style="color:#d97706">{{ f(stats.readyCount) }}</div>
          <div class="ap-kpi-lbl">جاهزة للتسليم</div>
        </div>
      </div>
      <div class="ap-kpi">
        <div class="ap-kpi-ico" style="background:#ecfdf5;color:#059669">🧾</div>
        <div>
          <div class="ap-kpi-val" style="color:#059669">{{ fv(stats.commissionEarned, '$') }}</div>
          <div class="ap-kpi-lbl">عمولة مستحقة</div>
          <div class="ap-kpi-sub">معلّقة: {{ fv(stats.pendingCommission, '$') }}</div>
        </div>
      </div>
      <div class="ap-kpi ap-kpi-highlight">
        <div class="ap-kpi-ico" style="background:#fef2f2;color:#dc2626">🏦</div>
        <div>
          <div class="ap-kpi-val" style="color:#dc2626">{{ f(stats.totalRemittances) }}</div>
          <div class="ap-kpi-lbl">إجمالي الحوالات</div>
          <div class="ap-kpi-sub">ملغية: {{ f(stats.cancelledCount) }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ PER-CURRENCY BREAKDOWN (SEND) ══════════ -->
  <div class="ap-sec">
    <div class="ap-sec-hdr">
      <div class="ap-sec-dot" style="background:#2563eb"></div>
      <h2 class="ap-sec-title">💱 حسابات العملات (مرسلة)</h2>
    </div>
    <div v-if="currencyBreakdown?.length" class="ap-curr-grid">
      <div v-for="c in currencyBreakdown" :key="c.code" class="ap-curr-card" :style="{'border-top': '3px solid ' + getCurrColor(c.code)}">
        <div class="ap-curr-header">
          <span class="ap-curr-code" :style="{color: getCurrColor(c.code)}">{{ c.symbol }} {{ c.code }}</span>
          <span class="ap-curr-name">{{ c.name_ar }}</span>
          <span class="ap-curr-count">{{ f(c.count) }} حوالة</span>
        </div>
        <div class="ap-curr-body">
          <div class="ap-curr-row">
            <span class="ap-curr-label">إجمالي</span>
            <span class="ap-curr-val">{{ fv(c.total, c.symbol) }}</span>
          </div>
          <div class="ap-curr-row">
            <span class="ap-curr-label" style="color:#059669">✅ مسلّم</span>
            <span class="ap-curr-val" style="color:#059669">{{ fv(c.collected, c.symbol) }}</span>
          </div>
          <div class="ap-curr-row">
            <span class="ap-curr-label" style="color:#d97706">⏳ معلّق</span>
            <span class="ap-curr-val" style="color:#d97706">{{ fv(c.pending, c.symbol) }}</span>
          </div>
          <div class="ap-curr-row ap-curr-row-hl">
            <span class="ap-curr-label">🧾 عمولة</span>
            <span class="ap-curr-val" style="color:#7c3aed">{{ fv(c.commission, c.symbol) }}</span>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="ap-empty-inline">لا توجد حوالات مرسلة بعد</div>
  </div>

  <!-- ══════════ PER-CURRENCY BREAKDOWN (RECEIVE) ══════════ -->
  <div class="ap-sec" v-if="receiveCurrencyBreakdown?.length">
    <div class="ap-sec-hdr">
      <div class="ap-sec-dot" style="background:#d97706"></div>
      <h2 class="ap-sec-title">📥 حسابات العملات (مستلمة)</h2>
    </div>
    <div class="ap-curr-grid">
      <div v-for="c in receiveCurrencyBreakdown" :key="c.code" class="ap-curr-card" :style="{'border-top': '3px solid ' + getCurrColor(c.code)}">
        <div class="ap-curr-header">
          <span class="ap-curr-code" :style="{color: getCurrColor(c.code)}">{{ c.symbol }} {{ c.code }}</span>
          <span class="ap-curr-name">{{ c.name_ar }}</span>
        </div>
        <div class="ap-curr-body">
          <div class="ap-curr-row">
            <span class="ap-curr-label">إجمالي</span>
            <span class="ap-curr-val">{{ f(c.total) }} {{ c.symbol }}</span>
          </div>
          <div class="ap-curr-row">
            <span class="ap-curr-label" style="color:#059669">✅ مسلّم</span>
            <span class="ap-curr-val" style="color:#059669">{{ f(c.collected) }} {{ c.symbol }}</span>
          </div>
          <div class="ap-curr-row">
            <span class="ap-curr-label" style="color:#d97706">⏳ معلّق</span>
            <span class="ap-curr-val" style="color:#d97706">{{ f(c.pending) }} {{ c.symbol }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Monthly Breakdown -->
  <div class="ap-sec" v-if="monthlyBreakdown?.length">
    <div class="ap-sec-hdr">
      <div class="ap-sec-dot" style="background:#7c3aed"></div>
      <h2 class="ap-sec-title">📅 حركة شهرية</h2>
    </div>
    <div class="ap-card">
      <table class="ap-tbl">
        <thead><tr><th>الشهر</th><th>العملة</th><th>عدد الحوالات</th><th>المبلغ المرسل</th><th>المبلغ المستلم</th></tr></thead>
        <tbody>
          <tr v-for="m in monthlyBreakdown" :key="m.month + m.send_currency">
            <td class="ap-tbl-bold">{{ m.month }}</td>
            <td><span class="ap-curr-badge" :style="{color: getCurrColor(m.send_currency), background: getCurrColor(m.send_currency) + '15'}">{{ m.send_currency }}</span></td>
            <td>{{ f(m.count) }}</td>
            <td class="ap-tbl-amount">{{ fv(m.total_send, getCurrSymbol(m.send_currency)) }}</td>
            <td class="ap-tbl-amount">{{ f(m.total_receive) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Remittances Table -->
  <div class="ap-sec">
    <div class="ap-sec-hdr">
      <div class="ap-sec-dot" style="background:#0891b2"></div>
      <h2 class="ap-sec-title">📋 سجل الحوالات ({{ remittances?.length || 0 }})</h2>
    </div>
    <div class="ap-card">
      <table class="ap-tbl">
        <thead><tr><th>#</th><th>المرسل</th><th>المستلم</th><th>العملة</th><th>المبلغ</th><th>المستلم بـ</th><th>الرسوم</th><th>الحالة</th><th>التاريخ</th><th>تاريخ التسليم</th></tr></thead>
        <tbody>
          <tr v-for="r in remittances" :key="r.id" :class="{'ap-row-ready': r.status==='ready', 'ap-row-cancelled': r.status==='cancelled'}">
            <td>{{ r.id }}</td>
            <td class="ap-tbl-bold">{{ r.sender_name }}<br><span class="ap-sub">{{ r.sender_email }}</span></td>
            <td class="ap-tbl-bold">{{ r.recipient_name }}<br><span class="ap-sub">{{ r.recipient_phone }}</span></td>
            <td><span class="ap-curr-badge" :style="{color: getCurrColor(r.send_currency), background: getCurrColor(r.send_currency) + '15'}">{{ r.send_currency }}</span></td>
            <td class="ap-tbl-amount">{{ fv(r.amount, getCurrSymbol(r.send_currency)) }}</td>
            <td class="ap-tbl-amount">{{ f(r.receive_amount) }} {{ getCurrSymbol(r.receive_currency) }}</td>
            <td>{{ fv(r.fee, getCurrSymbol(r.send_currency)) }}</td>
            <td><span class="ap-badge" :style="{color: statusColor(r.status), background: statusColor(r.status)+'18'}">{{ statusLabel(r.status) }}</span></td>
            <td class="ap-tbl-date">{{ new Date(r.created_at).toLocaleDateString('ar') }}</td>
            <td class="ap-tbl-date">{{ r.collected_at ? new Date(r.collected_at).toLocaleDateString('ar') : '—' }}</td>
          </tr>
          <tr v-if="!remittances?.length"><td colspan="10" class="ap-empty">لا توجد حوالات</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>

<style scoped>
.ap { direction: rtl; max-width: 1440px; margin: 0 auto; }
.ap-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
.ap-header-right { display: flex; flex-direction: column; gap: 12px; }
.ap-back { font-size: 13px; color: #64748b; text-decoration: none; font-weight: 600; transition: color .15s; }
.ap-back:hover { color: #2563eb; }
.ap-profile { display: flex; gap: 16px; align-items: center; }
.ap-avatar {
  width: 64px; height: 64px; border-radius: 16px;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 900; font-size: 24px; flex-shrink: 0;
}
.ap-name { font-size: 22px; font-weight: 900; color: #0f172a; margin: 0; }
.ap-name-en { font-size: 13px; color: #94a3b8; margin: 2px 0; }
.ap-meta { display: flex; gap: 14px; font-size: 12px; color: #64748b; flex-wrap: wrap; }
.ap-status-area { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.ap-status { font-size: 13px; font-weight: 700; padding: 6px 14px; border-radius: 10px; }
.ap-active { background: #ecfdf5; color: #059669; }
.ap-inactive { background: #fef2f2; color: #dc2626; }
.ap-commission { font-size: 14px; font-weight: 800; color: #7c3aed; background: #f5f3ff; padding: 4px 12px; border-radius: 8px; }

/* Sections */
.ap-sec { margin-bottom: 24px; }
.ap-sec-hdr { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
.ap-sec-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.ap-sec-title { font-size: 15px; font-weight: 800; color: #1e293b; margin: 0; }

/* Grid */
.ap-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

/* KPI Cards */
.ap-kpi {
  display: flex; align-items: center; gap: 14px;
  background: #fff; border: 1px solid #e8ebf0; border-radius: 14px;
  padding: 18px; transition: all .2s;
}
.ap-kpi:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.ap-kpi-main { border-color: #a7f3d0; background: linear-gradient(135deg, #f0fdf4, #fff); }
.ap-kpi-highlight { border-color: #fecaca; background: linear-gradient(135deg, #fef2f2, #fff); }
.ap-kpi-ico {
  width: 48px; height: 48px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.ap-kpi-val { font-size: 22px; font-weight: 900; line-height: 1.1; }
.ap-kpi-lbl { font-size: 11px; color: #64748b; font-weight: 600; margin-top: 2px; }
.ap-kpi-sub { font-size: 10px; color: #94a3b8; font-weight: 600; margin-top: 1px; }

/* ══════ CURRENCY CARDS ══════ */
.ap-curr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
.ap-curr-card {
  background: #fff; border: 1px solid #e8ebf0; border-radius: 14px;
  padding: 0; overflow: hidden; transition: all .2s;
}
.ap-curr-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.ap-curr-header {
  display: flex; align-items: center; gap: 8px;
  padding: 14px 16px; background: #fafbfc; border-bottom: 1px solid #f1f5f9;
}
.ap-curr-code { font-size: 18px; font-weight: 900; }
.ap-curr-name { font-size: 12px; color: #64748b; font-weight: 600; flex: 1; }
.ap-curr-count {
  font-size: 10px; font-weight: 700; color: #64748b;
  background: #f1f5f9; padding: 2px 8px; border-radius: 6px;
}
.ap-curr-body { padding: 12px 16px; }
.ap-curr-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 6px 0; border-bottom: 1px solid #f8fafc;
}
.ap-curr-row:last-child { border-bottom: none; }
.ap-curr-row-hl { background: #f5f3ff; margin: 4px -16px 0; padding: 8px 16px; border-radius: 0 0 14px 14px; border-bottom: none; }
.ap-curr-label { font-size: 12px; color: #64748b; font-weight: 600; }
.ap-curr-val { font-size: 14px; font-weight: 800; color: #0f172a; font-family: 'Inter', monospace; }
.ap-curr-badge {
  font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px;
}
.ap-empty-inline { text-align: center; color: #cbd5e1; font-size: 13px; padding: 30px; background: #fff; border: 1px solid #e8ebf0; border-radius: 14px; }

/* Card & Table */
.ap-card { background: #fff; border: 1px solid #e8ebf0; border-radius: 16px; padding: 16px; overflow-x: auto; }
.ap-tbl { width: 100%; border-collapse: collapse; min-width: 900px; }
.ap-tbl th { font-size: 11px; font-weight: 700; color: #94a3b8; text-align: right; padding: 10px 12px; border-bottom: 2px solid #e8ebf0; background: #fafbfc; }
.ap-tbl td { font-size: 13px; color: #334155; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
.ap-tbl tr:hover td { background: #fafbfc; }
.ap-tbl-bold { font-weight: 700; color: #0f172a; }
.ap-tbl-amount { font-weight: 700; font-family: 'Inter', monospace; }
.ap-tbl-date { font-size: 11px; color: #94a3b8; }
.ap-sub { font-size: 10px; color: #94a3b8; }
.ap-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.ap-row-ready td { background: #fffbeb !important; }
.ap-row-cancelled td { background: #fef2f2 !important; }
.ap-empty { text-align: center; color: #cbd5e1; padding: 30px; }

@media (max-width: 1100px) { .ap-grid-4 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { .ap-grid-4 { grid-template-columns: 1fr; } .ap-curr-grid { grid-template-columns: 1fr; } }
</style>
