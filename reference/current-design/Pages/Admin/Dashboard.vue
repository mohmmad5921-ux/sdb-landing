<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: AdminLayout });
const p = defineProps({ stats: Object, usersByCountry: Array, topActiveUsers: Array, recentTransactions: Array, recentUsers: Array, monthlyTrend: Array, userGrowth: Array });
const f = n => Number(n||0).toLocaleString('en');
const fc = n => '€' + Number(n||0).toLocaleString('en', {minimumFractionDigits:2});
const statusColor = s => ({completed:'#059669',failed:'#dc2626',pending:'#d97706'}[s]||'#6b7280');
const statusLabel = s => ({completed:'ناجح',failed:'فشل',pending:'معلّق',cancelled:'ملغي'}[s]||s);
const initials = (name) => { if (!name) return '?'; const parts = name.split(' '); return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0,2).toUpperCase(); };
const maxVol = () => Math.max(...(p.monthlyTrend||[]).map(m=>m.volume), 1);
const maxGr = () => Math.max(...(p.userGrowth||[]).map(m=>m.count), 1);
const avatarColors = ['#2563eb','#059669','#d97706','#dc2626','#7c3aed','#db2777','#0891b2','#65a30d'];
const getColor = (i) => avatarColors[i % avatarColors.length];
const growth = (current, previous) => {
  if (!previous || previous === 0) return current > 0 ? { pct: 100, dir: 'up' } : { pct: 0, dir: 'flat' };
  const pct = Math.round(((current - previous) / previous) * 100);
  return { pct: Math.abs(pct), dir: pct > 0 ? 'up' : pct < 0 ? 'down' : 'flat' };
};
const userGrowthData = () => growth(p.stats.newToday, p.stats.newYesterday);
const volumeGrowthData = () => growth(p.stats.monthlyVolume, p.stats.lastMonthVolume);
const photoUrl = (photo) => photo ? (photo.startsWith('http') ? photo : '/storage/' + photo) : null;
const timeSince = (date) => {
  if (!date) return '';
  const d = new Date(date);
  const now = new Date();
  const diff = Math.floor((now - d) / 1000);
  if (diff < 60) return 'الآن';
  if (diff < 3600) return Math.floor(diff/60) + ' دقيقة';
  if (diff < 86400) return Math.floor(diff/3600) + ' ساعة';
  return Math.floor(diff/86400) + ' يوم';
};
</script>
<template>
<Head title="لوحة التحكم" />
<div class="db">

  <!-- ══════════ HEADER ══════════ -->
  <div class="db-hdr">
    <div>
      <h1 class="db-title">لوحة التحكم الرئيسية</h1>
      <p class="db-subtitle">نظرة شاملة على أداء النظام</p>
    </div>
    <div class="db-date">{{ new Date().toLocaleDateString('ar', {weekday:'long',year:'numeric',month:'long',day:'numeric'}) }}</div>
  </div>

  <!-- ══════════ QUICK ACTIONS ══════════ -->
  <div class="db-quick-actions">
    <Link :href="route('admin.kyc')" class="db-qa" :class="{'db-qa-urgent': p.stats.pendingKyc > 0}">
      <div class="db-qa-ico" style="background:#fef3c7;color:#d97706">🪪</div>
      <div class="db-qa-info">
        <div class="db-qa-count" :style="{color: p.stats.pendingKyc > 0 ? '#d97706' : '#6b7280'}">{{ f(p.stats.pendingKyc) }}</div>
        <div class="db-qa-label">KYC معلّق</div>
      </div>
    </Link>
    <Link :href="route('admin.tickets')" class="db-qa" :class="{'db-qa-urgent': p.stats.openTickets > 0}">
      <div class="db-qa-ico" style="background:#eff6ff;color:#2563eb">🎫</div>
      <div class="db-qa-info">
        <div class="db-qa-count" :style="{color: p.stats.openTickets > 0 ? '#2563eb' : '#6b7280'}">{{ f(p.stats.openTickets) }}</div>
        <div class="db-qa-label">تذاكر مفتوحة</div>
      </div>
    </Link>
    <Link :href="route('admin.alerts')" class="db-qa" :class="{'db-qa-urgent': p.stats.securityAlerts > 0}">
      <div class="db-qa-ico" style="background:#fef2f2;color:#dc2626">🚨</div>
      <div class="db-qa-info">
        <div class="db-qa-count" :style="{color: p.stats.securityAlerts > 0 ? '#dc2626' : '#6b7280'}">{{ f(p.stats.securityAlerts) }}</div>
        <div class="db-qa-label">إنذارات أمنية</div>
      </div>
    </Link>
    <Link :href="route('admin.approvals')" class="db-qa" :class="{'db-qa-urgent': p.stats.pendingApprovals > 0}">
      <div class="db-qa-ico" style="background:#f0fdf4;color:#059669">✅</div>
      <div class="db-qa-info">
        <div class="db-qa-count" :style="{color: p.stats.pendingApprovals > 0 ? '#059669' : '#6b7280'}">{{ f(p.stats.pendingApprovals) }}</div>
        <div class="db-qa-label">موافقات معلّقة</div>
      </div>
    </Link>
    <Link :href="route('admin.fraud')" class="db-qa" :class="{'db-qa-urgent': p.stats.suspiciousTx > 0}">
      <div class="db-qa-ico" style="background:#fef2f2;color:#dc2626">🔍</div>
      <div class="db-qa-info">
        <div class="db-qa-count" :style="{color: p.stats.suspiciousTx > 0 ? '#dc2626' : '#6b7280'}">{{ f(p.stats.suspiciousTx) }}</div>
        <div class="db-qa-label">عمليات مشبوهة</div>
      </div>
    </Link>
  </div>

  <!-- ══════════ SECTION: المستخدمين ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#2563eb"></div>
      <h2 class="db-sec-title">المستخدمين</h2>
    </div>
    <div class="db-grid-3">
      <div class="db-stat">
        <div class="db-stat-ico" style="background:#eff6ff;color:#2563eb">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="db-stat-body">
          <div class="db-stat-val">{{ f(p.stats.totalUsers) }}</div>
          <div class="db-stat-lbl">إجمالي المستخدمين</div>
        </div>
      </div>
      <div class="db-stat">
        <div class="db-stat-ico" style="background:#ecfdf5;color:#059669">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        </div>
        <div class="db-stat-body">
          <div class="db-stat-val" style="color:#059669">{{ f(p.stats.newToday) }}</div>
          <div class="db-stat-lbl">
            جدد اليوم
            <span v-if="userGrowthData().dir !== 'flat'" :class="['db-growth', 'db-growth-' + userGrowthData().dir]">
              {{ userGrowthData().dir === 'up' ? '↑' : '↓' }} {{ userGrowthData().pct }}%
            </span>
          </div>
        </div>
      </div>
      <div class="db-stat">
        <div class="db-stat-ico" style="background:#fef3c7;color:#d97706">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="db-stat-body">
          <div class="db-stat-val">{{ f(p.stats.activeUsers) }}</div>
          <div class="db-stat-lbl">نشطين (30 يوم)</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ SECTION: الحسابات والأرصدة ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#059669"></div>
      <h2 class="db-sec-title">الحسابات والأرصدة</h2>
    </div>
    <div class="db-grid-3">
      <div class="db-stat">
        <div class="db-stat-ico" style="background:#ecfdf5;color:#059669">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20"/></svg>
        </div>
        <div class="db-stat-body">
          <div class="db-stat-val">{{ f(p.stats.activeAccounts) }}</div>
          <div class="db-stat-lbl">حسابات نشطة</div>
        </div>
      </div>
      <div class="db-stat">
        <div class="db-stat-ico" style="background:#fef2f2;color:#dc2626">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M8 12h8"/></svg>
        </div>
        <div class="db-stat-body">
          <div class="db-stat-val" style="color:#dc2626">{{ f(p.stats.frozenAccounts) }}</div>
          <div class="db-stat-lbl">حسابات مجمّدة</div>
        </div>
      </div>
      <div class="db-stat db-stat-highlight">
        <div class="db-stat-ico" style="background:#d1fae5;color:#059669">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="db-stat-body">
          <div class="db-stat-val db-stat-big" style="color:#059669">{{ fc(p.stats.totalBalance) }}</div>
          <div class="db-stat-lbl">إجمالي الأرصدة</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ SECTION: المعاملات ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#7c3aed"></div>
      <h2 class="db-sec-title">المعاملات</h2>
    </div>
    <div class="db-grid-5">
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#fef3c7;color:#d97706">📅</div>
        <div class="db-stat-val-sm">{{ fc(p.stats.dailyVolume) }}</div>
        <div class="db-stat-lbl-sm">حجم اليوم</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#eff6ff;color:#2563eb">📆</div>
        <div class="db-stat-val-sm">{{ fc(p.stats.monthlyVolume) }}</div>
        <div class="db-stat-lbl-sm">
          حجم الشهر
          <span v-if="volumeGrowthData().dir !== 'flat'" :class="['db-growth', 'db-growth-' + volumeGrowthData().dir]">
            {{ volumeGrowthData().dir === 'up' ? '↑' : '↓' }} {{ volumeGrowthData().pct }}%
          </span>
        </div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#f5f3ff;color:#7c3aed">📊</div>
        <div class="db-stat-val-sm">{{ fc(p.stats.yearlyVolume) }}</div>
        <div class="db-stat-lbl-sm">حجم السنة</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#ecfdf5;color:#059669">✅</div>
        <div class="db-stat-val-sm" style="color:#059669">{{ f(p.stats.successTx) }}</div>
        <div class="db-stat-lbl-sm">ناجحة</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#fef2f2;color:#dc2626">❌</div>
        <div class="db-stat-val-sm" style="color:#dc2626">{{ f(p.stats.failedTx) }}</div>
        <div class="db-stat-lbl-sm">فاشلة</div>
      </div>
    </div>
    <div class="db-grid-3" style="margin-top:12px">
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#fefce8;color:#ca8a04">⏳</div>
        <div class="db-stat-val-sm" style="color:#ca8a04">{{ f(p.stats.pendingTx) }}</div>
        <div class="db-stat-lbl-sm">معلّقة</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#eff6ff;color:#2563eb">🌍</div>
        <div class="db-stat-val-sm">{{ f(p.stats.internationalTx) }}</div>
        <div class="db-stat-lbl-sm">دولية</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#ecfdf5;color:#059669">🏠</div>
        <div class="db-stat-val-sm">{{ f(p.stats.localTx) }}</div>
        <div class="db-stat-lbl-sm">محلية</div>
      </div>
    </div>
  </div>

  <!-- ══════════ SECTION: الإيرادات والبطاقات ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#d97706"></div>
      <h2 class="db-sec-title">الإيرادات والبطاقات</h2>
    </div>
    <div class="db-grid-5">
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#ecfdf5;color:#059669">💹</div>
        <div class="db-stat-val-sm" style="color:#059669">{{ fc(p.stats.systemProfit) }}</div>
        <div class="db-stat-lbl-sm">أرباح النظام</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#f0fdf4;color:#16a34a">🧾</div>
        <div class="db-stat-val-sm">{{ fc(p.stats.feesCollected) }}</div>
        <div class="db-stat-lbl-sm">رسوم الشهر</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#eff6ff;color:#2563eb">💳</div>
        <div class="db-stat-val-sm">{{ f(p.stats.totalCards) }}</div>
        <div class="db-stat-lbl-sm">بطاقات صادرة</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#ecfdf5;color:#059669">✅</div>
        <div class="db-stat-val-sm">{{ f(p.stats.activeCards) }}</div>
        <div class="db-stat-lbl-sm">بطاقات نشطة</div>
      </div>
      <div class="db-stat db-stat-sm">
        <div class="db-stat-ico-sm" style="background:#fef2f2;color:#dc2626">🧊</div>
        <div class="db-stat-val-sm">{{ f(p.stats.frozenCards) }}</div>
        <div class="db-stat-lbl-sm">بطاقات مجمّدة</div>
      </div>
    </div>
  </div>

  <!-- ══════════ CHARTS ROW ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#0891b2"></div>
      <h2 class="db-sec-title">الرسوم البيانية</h2>
    </div>
    <div class="db-grid-2">
      <!-- Monthly Volume Chart -->
      <div class="db-card">
        <div class="db-card-hdr">
          <h3 class="db-card-title">📈 حجم المعاملات الشهري</h3>
        </div>
        <div class="db-chart">
          <div v-for="m in p.monthlyTrend" :key="m.month" class="db-bar-w">
            <div class="db-bar db-bar-blue" :style="{height: (m.volume/maxVol()*100)+'%'}">
              <span class="db-bar-tip">{{ fc(m.volume) }}</span>
            </div>
            <div class="db-bar-lbl">{{ m.month?.slice(5) }}</div>
          </div>
          <div v-if="!p.monthlyTrend?.length" class="db-empty-chart">لا توجد بيانات</div>
        </div>
      </div>
      <!-- User Growth Chart -->
      <div class="db-card">
        <div class="db-card-hdr">
          <h3 class="db-card-title">👥 نمو المستخدمين</h3>
        </div>
        <div class="db-chart">
          <div v-for="m in p.userGrowth" :key="m.month" class="db-bar-w">
            <div class="db-bar db-bar-grn" :style="{height: (m.count/maxGr()*100)+'%'}">
              <span class="db-bar-tip">{{ m.count }}</span>
            </div>
            <div class="db-bar-lbl">{{ m.month?.slice(5) }}</div>
          </div>
          <div v-if="!p.userGrowth?.length" class="db-empty-chart">لا توجد بيانات</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ TABLES + USERS ROW ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#7c3aed"></div>
      <h2 class="db-sec-title">التفاصيل</h2>
    </div>
    <div class="db-grid-2">
      <!-- Recent Transactions Table -->
      <div class="db-card">
        <h3 class="db-card-title">آخر المعاملات</h3>
        <table class="db-tbl">
          <thead><tr><th>المستخدم</th><th>المبلغ</th><th>النوع</th><th>الحالة</th><th>التاريخ</th></tr></thead>
          <tbody>
            <tr v-for="t in (p.recentTransactions || []).slice(0,6)" :key="t.id" :class="{'db-row-pending': t.status==='pending', 'db-row-failed': t.status==='failed'}">
              <td class="db-tbl-bold">{{ t.user_name||'—' }}</td>
              <td class="db-tbl-amount">{{ fc(t.amount) }}</td>
              <td><span class="db-type-badge">{{ t.type }}</span></td>
              <td><span class="db-status-badge" :style="{color:statusColor(t.status), background: statusColor(t.status)+'18'}">{{ statusLabel(t.status) }}</span></td>
              <td class="db-tbl-date">{{ new Date(t.created_at).toLocaleString('ar') }}</td>
            </tr>
            <tr v-if="!p.recentTransactions?.length"><td colspan="5" class="db-empty-chart">لا توجد معاملات</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Top Users + Countries -->
      <div class="db-card">
        <h3 class="db-card-title">🏆 أكثر المستخدمين نشاطاً</h3>
        <div class="db-user-list">
          <div v-for="(u,i) in (p.topActiveUsers || []).slice(0,5)" :key="u.id" class="db-user-row">
            <div class="db-user-rank" :class="{'db-rank-gold': i===0, 'db-rank-silver': i===1, 'db-rank-bronze': i===2}">{{ i+1 }}</div>
            <img v-if="photoUrl(u.profile_photo)" :src="photoUrl(u.profile_photo)" class="db-user-avatar-img" :alt="u.full_name" />
            <div v-else class="db-user-avatar" :style="{background: getColor(i)}">{{ initials(u.full_name) }}</div>
            <div class="db-user-info">
              <div class="db-user-name">{{ u.full_name }}</div>
              <div class="db-user-sub">{{ f(u.tx_count) }} عملية · {{ fc(u.total_volume) }}</div>
            </div>
          </div>
          <div v-if="!p.topActiveUsers?.length" class="db-empty-chart">لا توجد بيانات</div>
        </div>

        <h3 class="db-card-title" style="margin-top:20px">🌍 المستخدمين حسب الدولة</h3>
        <div class="db-country-list">
          <div v-for="c in p.usersByCountry" :key="c.country" class="db-country-row">
            <span class="db-country-name">{{ c.country }}</span>
            <span class="db-country-count">{{ f(c.count) }}</span>
          </div>
          <div v-if="!p.usersByCountry?.length" class="db-empty-chart">لا توجد بيانات</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ SECTION: آخر المسجلين ══════════ -->
  <div class="db-sec">
    <div class="db-sec-hdr">
      <div class="db-sec-dot" style="background:#2563eb"></div>
      <h2 class="db-sec-title">آخر المستخدمين المسجلين</h2>
    </div>
    <div class="db-card">
      <table class="db-tbl">
        <thead><tr><th style="width:40px"></th><th>الاسم</th><th>البريد الإلكتروني</th><th>الدولة</th><th>تاريخ التسجيل</th><th>منذ</th></tr></thead>
        <tbody>
          <tr v-for="u in (p.recentUsers || [])" :key="u.id">
            <td>
              <img v-if="photoUrl(u.profile_photo)" :src="photoUrl(u.profile_photo)" class="db-tbl-avatar-img" :alt="u.full_name" />
              <div v-else class="db-tbl-avatar" :style="{background: getColor(u.id % 8)}">{{ initials(u.full_name) }}</div>
            </td>
            <td class="db-tbl-bold">
              <Link :href="route('admin.users.show', u.id)" class="db-link">{{ u.full_name || '—' }}</Link>
            </td>
            <td>{{ u.email }}</td>
            <td>{{ u.country || '—' }}</td>
            <td class="db-tbl-date">{{ new Date(u.created_at).toLocaleDateString('ar') }}</td>
            <td><span class="db-time-badge">{{ timeSince(u.created_at) }}</span></td>
          </tr>
          <tr v-if="!p.recentUsers?.length"><td colspan="6" class="db-empty-chart">لا توجد مسجلين جدد</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>

<style scoped>
.db { direction: rtl; max-width: 1440px; margin: 0 auto; }

/* Header */
.db-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.db-title { font-size: 22px; font-weight: 900; color: #0f172a; margin: 0; }
.db-subtitle { font-size: 13px; color: #64748b; margin-top: 2px; }
.db-date { font-size: 13px; color: #94a3b8; font-weight: 500; }

/* ══════ QUICK ACTIONS ══════ */
.db-quick-actions {
  display: flex; gap: 12px; margin-bottom: 24px;
  padding: 16px; background: #ffffff; border: 1px solid #e8ebf0;
  border-radius: 16px; overflow-x: auto;
}
.db-qa {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px; border-radius: 12px;
  background: #f8fafc; border: 1px solid #eef0f4;
  text-decoration: none; transition: all .2s;
  flex: 1; min-width: 140px;
}
.db-qa:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-color: #d1d5db; }
.db-qa-urgent { border-color: #fbbf24; background: #fffbeb; }
.db-qa-urgent:hover { border-color: #f59e0b; }
.db-qa-ico { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.db-qa-info { flex: 1; min-width: 0; }
.db-qa-count { font-size: 22px; font-weight: 900; line-height: 1; }
.db-qa-label { font-size: 11px; color: #64748b; font-weight: 600; margin-top: 2px; }

/* ══════ GROWTH BADGES ══════ */
.db-growth {
  display: inline-block; font-size: 10px; font-weight: 800;
  padding: 1px 6px; border-radius: 6px; margin-right: 4px;
}
.db-growth-up { color: #059669; background: #d1fae5; }
.db-growth-down { color: #dc2626; background: #fee2e2; }

/* ══════ SECTIONS ══════ */
.db-sec { margin-bottom: 24px; }
.db-sec-hdr { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
.db-sec-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.db-sec-title { font-size: 15px; font-weight: 800; color: #1e293b; margin: 0; }

/* ══════ GRIDS ══════ */
.db-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.db-grid-5 { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.db-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

/* ══════ BIG STAT CARD ══════ */
.db-stat {
  background: #ffffff; border: 1px solid #e8ebf0; border-radius: 14px;
  padding: 18px; display: flex; align-items: center; gap: 14px;
  transition: all .2s;
}
.db-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.06); border-color: #d1d5db; }
.db-stat-highlight { border-color: #a7f3d0; background: linear-gradient(135deg, #f0fdf4, #ffffff); }

.db-stat-ico {
  width: 48px; height: 48px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.db-stat-body { flex: 1; min-width: 0; }
.db-stat-val { font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1.1; }
.db-stat-big { font-size: 20px; }
.db-stat-lbl { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 3px; }

/* ══════ SMALL STAT CARD ══════ */
.db-stat-sm {
  flex-direction: column; align-items: center; text-align: center;
  padding: 16px 12px; gap: 8px;
}
.db-stat-ico-sm { font-size: 22px; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.db-stat-val-sm { font-size: 20px; font-weight: 900; color: #0f172a; line-height: 1; }
.db-stat-lbl-sm { font-size: 11px; color: #64748b; font-weight: 700; }

/* ══════ CARD ══════ */
.db-card {
  background: #fff; border: 1px solid #e8ebf0; border-radius: 16px; padding: 22px;
}
.db-card-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.db-card-title { font-size: 15px; font-weight: 800; color: #1e293b; margin: 0 0 14px 0; }

/* ══════ CHART ══════ */
.db-chart { display: flex; align-items: flex-end; gap: 6px; height: 180px; padding-top: 20px; }
.db-bar-w { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; }
.db-bar {
  border-radius: 6px 6px 0 0; min-height: 4px; width: 100%;
  position: relative; transition: height .4s ease; cursor: pointer;
}
.db-bar-blue { background: linear-gradient(180deg, #3b82f6, #1d4ed8); }
.db-bar-grn { background: linear-gradient(180deg, #10b981, #059669); }
.db-bar-tip {
  display: none; position: absolute; top: -28px; left: 50%; transform: translateX(-50%);
  background: #0f172a; color: #fff; padding: 3px 8px; border-radius: 6px;
  font-size: 10px; white-space: nowrap; font-weight: 700;
}
.db-bar:hover .db-bar-tip { display: block; }
.db-bar-lbl { font-size: 10px; color: #94a3b8; margin-top: 6px; font-weight: 600; }

/* ══════ TABLE ══════ */
.db-tbl { width: 100%; border-collapse: collapse; }
.db-tbl th {
  font-size: 12px; font-weight: 700; color: #64748b;
  text-align: right; padding: 10px 12px;
  border-bottom: 2px solid #e8ebf0; background: #fafbfc;
}
.db-tbl td {
  font-size: 13px; color: #334155; padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
}
.db-tbl tr:hover td { background: #fafbfc; }
.db-tbl-bold { font-weight: 700; color: #0f172a; }
.db-tbl-amount { font-weight: 700; font-family: 'Inter', monospace; }
.db-tbl-date { font-size: 11px; color: #94a3b8; }
.db-type-badge { font-size: 11px; background: #f1f5f9; padding: 3px 8px; border-radius: 6px; color: #475569; font-weight: 600; }
.db-status-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.db-row-pending td { background: #fffbeb !important; }
.db-row-failed td { background: #fef2f2 !important; }

/* ══════ TABLE AVATARS ══════ */
.db-tbl-avatar {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 11px; flex-shrink: 0;
}
.db-tbl-avatar-img {
  width: 32px; height: 32px; border-radius: 8px; object-fit: cover;
}
.db-link { color: #0f172a; text-decoration: none; transition: color .15s; }
.db-link:hover { color: #2563eb; }
.db-time-badge {
  font-size: 10px; font-weight: 700; color: #059669;
  background: #ecfdf5; padding: 2px 8px; border-radius: 6px;
  white-space: nowrap;
}

/* ══════ USER LIST ══════ */
.db-user-list { display: flex; flex-direction: column; gap: 4px; }
.db-user-row { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 10px; transition: background .15s; }
.db-user-row:hover { background: #f8fafc; }
.db-user-rank {
  width: 28px; height: 28px; border-radius: 8px; background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 800; color: #64748b; flex-shrink: 0;
}
.db-rank-gold { background: linear-gradient(135deg, #fbbf24, #d97706); color: #fff; }
.db-rank-silver { background: linear-gradient(135deg, #cbd5e1, #94a3b8); color: #fff; }
.db-rank-bronze { background: linear-gradient(135deg, #f59e0b, #b45309); color: #fff; }
.db-user-avatar {
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 12px; flex-shrink: 0;
}
.db-user-avatar-img {
  width: 36px; height: 36px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}
.db-user-info { flex: 1; min-width: 0; }
.db-user-name { font-size: 13px; font-weight: 700; color: #0f172a; }
.db-user-sub { font-size: 11px; color: #94a3b8; margin-top: 1px; }

/* ══════ COUNTRIES ══════ */
.db-country-list { display: flex; flex-direction: column; gap: 2px; }
.db-country-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 10px; border-radius: 8px; transition: background .15s;
}
.db-country-row:hover { background: #f8fafc; }
.db-country-name { font-size: 13px; color: #334155; font-weight: 600; }
.db-country-count { font-size: 14px; font-weight: 800; color: #2563eb; }

.db-empty-chart { text-align: center; color: #cbd5e1; font-size: 13px; padding: 24px; width: 100%; }

/* Responsive */
@media (max-width: 1100px) {
  .db-grid-5 { grid-template-columns: repeat(3, 1fr); }
  .db-grid-2 { grid-template-columns: 1fr; }
  .db-quick-actions { flex-wrap: wrap; }
  .db-qa { min-width: 120px; }
}
@media (max-width: 768px) {
  .db-grid-3 { grid-template-columns: 1fr; }
  .db-grid-5 { grid-template-columns: repeat(2, 1fr); }
  .db-quick-actions { flex-direction: column; }
}
</style>
