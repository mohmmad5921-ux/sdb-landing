<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
const page = usePage();
const collapsed = ref(false);
const u = computed(() => page.props.auth?.user);
const stats = computed(() => page.props.auth?.admin_stats);
const flash = computed(() => page.props.flash);

// ═══ RBAC — Permission-based navigation ═══
const permissions = computed(() => page.props.auth?.employee_permissions || []);
const employeeRole = computed(() => u.value?.employee_role || 'support_agent');
const isLeadOrAdmin = computed(() => ['admin', 'team_lead'].includes(employeeRole.value));

const hasPerm = (perm) => {
  if (isLeadOrAdmin.value) return true;
  return permissions.value.includes(perm);
};

const roleLabels = {
  support_agent: 'موظف دعم',
  supervisor: 'مشرف',
  team_lead: 'قائد فريق',
  admin: 'مدير النظام',
};

const permLabels = {
  support: 'الدعم الفني',
  compliance: 'التحقق والامتثال',
  finance: 'المالية',
  marketing: 'التسويق',
  app_management: 'إدارة التطبيق',
  settings: 'الإعدادات',
};

const roleLabel = computed(() => roleLabels[employeeRole.value] || 'موظف');

// ═══ Navigation sections organized by permission ═══
const sections = computed(() => {
  const s = [];

  s.push({
    title: 'القائمة الرئيسية',
    items: [{ label: 'لوحة التحكم', icon: '🏠', href: 'employee.dashboard', perm: null }],
  });

  if (hasPerm('support')) {
    s.push({
      title: '═══ الدعم الفني ═══',
      items: [
        { label: 'بحث عن عميل', icon: '🔍', href: 'employee.search' },
        { label: 'تذاكر الدعم', icon: '🎫', href: 'employee.tickets', badge: () => stats.value?.open_tickets },
        { label: 'المحادثات المباشرة', icon: '💬', href: 'employee.chat', badge: () => stats.value?.unread_chats },
        { label: 'طلبات خاصة', icon: '📋', href: 'admin.special-requests' },
        { label: 'الدعم الإداري', icon: '📞', href: 'admin.support' },
      ],
    });
  }

  if (hasPerm('compliance')) {
    s.push({
      title: '═══ التحقق والامتثال ═══',
      items: [
        { label: 'مراجعة KYC', icon: '📑', href: 'admin.kyc', badge: () => stats.value?.pending_kyc },
        { label: 'كشف الاحتيال', icon: '🚨', href: 'admin.fraud' },
        { label: 'مكافحة غسيل الأموال', icon: '🔒', href: 'admin.aml' },
        { label: 'مراقبة المعاملات', icon: '📊', href: 'admin.transaction-monitor' },
        { label: 'لوحة المخاطر', icon: '⚠️', href: 'admin.risk-dashboard' },
        { label: 'سجلات التحقق', icon: '📋', href: 'admin.verification-logs' },
        { label: 'الامتثال', icon: '🛡️', href: 'admin.compliance' },
      ],
    });
  }

  if (hasPerm('finance')) {
    s.push({
      title: '═══ العمليات المالية ═══',
      items: [
        { label: 'المعاملات', icon: '💳', href: 'admin.transactions' },
        { label: 'الحوالات', icon: '💸', href: 'employee.remittances' },
        { label: 'مركز التقارير', icon: '📈', href: 'admin.report-center' },
        { label: 'الإيرادات', icon: '💰', href: 'admin.revenue' },
        { label: 'إدارة الرسوم', icon: '🧾', href: 'admin.fees' },
        { label: 'الحسابات المجمدة', icon: '❄️', href: 'admin.frozen' },
      ],
    });
  }

  if (hasPerm('marketing')) {
    s.push({
      title: '═══ التسويق ═══',
      items: [
        { label: 'العروض الترويجية', icon: '🎁', href: 'admin.promotions' },
        { label: 'الإحالات', icon: '👥', href: 'admin.referrals' },
        { label: 'الحملات', icon: '📢', href: 'admin.campaigns' },
        { label: 'الإشعارات الجماعية', icon: '📡', href: 'admin.broadcast' },
        { label: 'قوالب البريد', icon: '✉️', href: 'admin.email-templates' },
      ],
    });
  }

  if (hasPerm('app_management')) {
    s.push({
      title: '═══ إدارة التطبيق ═══',
      items: [
        { label: 'إعدادات التطبيق', icon: '⚙️', href: 'admin.app-management' },
        { label: 'خلفيات التطبيق', icon: '🖼️', href: 'admin.backgrounds' },
        { label: 'المناسبات والعطل', icon: '📅', href: 'admin.holidays' },
        { label: 'رسائل داخلية', icon: '💬', href: 'admin.in-app-message' },
      ],
    });
  }

  if (hasPerm('settings')) {
    s.push({
      title: '═══ الإعدادات ═══',
      items: [
        { label: 'الإعدادات العامة', icon: '🔧', href: 'admin.settings' },
        { label: 'إدارة المحتوى', icon: '📝', href: 'admin.cms' },
        { label: 'الحدود والقيود', icon: '📏', href: 'admin.limits' },
      ],
    });
  }

  return s;
});

// ═══ Live Permission Sync (poll every 30s) ═══
let permissionPoll = null;
const lastPermUpdated = ref(page.props.auth?.permissions_updated_at || '');

const checkPermissions = async () => {
  try {
    const res = await fetch(route('employee.check-permissions'), {
      headers: { 'Accept': 'application/json' },
    });
    if (!res.ok) return;
    const data = await res.json();
    if (data.permissions_updated_at && data.permissions_updated_at !== lastPermUpdated.value) {
      lastPermUpdated.value = data.permissions_updated_at;
      router.reload({ preserveScroll: true });
    }
  } catch (e) { /* silent */ }
};

onMounted(() => {
  permissionPoll = setInterval(checkPermissions, 30000);
});

onUnmounted(() => {
  if (permissionPoll) clearInterval(permissionPoll);
});

const now = new Date();
const timeStr = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
const dateStr = now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>
<template>
<div class="gov">
  <!-- ═══ TOP TOOLBAR (Government Style) ═══ -->
  <div class="gov-toolbar">
    <div class="gov-toolbar-right">
      <div class="gov-logo">
        <div class="gov-logo-icon">
          <svg viewBox="0 0 28 28" fill="none" width="22" height="22"><rect width="28" height="28" rx="4" fill="#1a5c2a"/><text x="14" y="20" text-anchor="middle" font-size="14" font-weight="900" fill="#fff">S</text></svg>
        </div>
        <div class="gov-logo-text">
          <span class="gov-logo-title">SDB Wallet</span>
          <span class="gov-logo-sub">نظام إدارة الموظفين — Staff Portal v2.0</span>
        </div>
      </div>
    </div>
    <div class="gov-toolbar-left">
      <div class="gov-toolbar-info">
        <span class="gov-toolbar-item">📅 {{ dateStr }}</span>
        <span class="gov-toolbar-sep">|</span>
        <span class="gov-toolbar-item">🕐 {{ timeStr }}</span>
        <span class="gov-toolbar-sep">|</span>
        <span class="gov-toolbar-item gov-online">● متصل</span>
      </div>
      <Link :href="route('logout')" method="post" as="button" class="gov-logout-btn">
        🔒 تسجيل الخروج
      </Link>
    </div>
  </div>

  <!-- ═══ USER INFO BAR ═══ -->
  <div class="gov-userbar">
    <div class="gov-userbar-right">
      <span class="gov-field"><span class="gov-label">الموظف:</span> {{ u?.full_name }}</span>
      <span class="gov-field"><span class="gov-label">الدور:</span> {{ roleLabel }}</span>
      <span class="gov-field" v-if="u?.department?.name"><span class="gov-label">القسم:</span> {{ u.department.name }}</span>
      <span class="gov-field"><span class="gov-label">البريد:</span> {{ u?.email }}</span>
    </div>
    <div class="gov-userbar-left">
      <span class="gov-perm-label">الصلاحيات:</span>
      <span v-for="p in permissions" :key="p" class="gov-perm-tag">{{ permLabels[p] || p }}</span>
      <span v-if="!permissions.length" class="gov-perm-tag gov-perm-none">لا توجد صلاحيات</span>
    </div>
  </div>

  <!-- ═══ BODY ═══ -->
  <div class="gov-body">
    <!-- Sidebar (Navigation Panel) -->
    <aside class="gov-sidebar" :class="{ 'gov-sb-collapsed': collapsed }">
      <div class="gov-sb-header">
        <span v-if="!collapsed">📂 قائمة المهام</span>
        <button class="gov-sb-toggle" @click="collapsed = !collapsed" :title="collapsed ? 'توسيع' : 'طي'">
          {{ collapsed ? '▶' : '◀' }}
        </button>
      </div>

      <nav class="gov-nav">
        <div v-for="(section, si) in sections" :key="si" class="gov-nav-group">
          <div v-if="!collapsed" class="gov-nav-title">{{ section.title }}</div>
          <Link v-for="item in section.items" :key="item.href" :href="route(item.href)"
            class="gov-nav-item" :class="{ 'gov-nav-active': route().current(item.href) }"
            :title="collapsed ? item.label : ''">
            <span class="gov-nav-icon">{{ item.icon }}</span>
            <span v-if="!collapsed" class="gov-nav-text">{{ item.label }}</span>
            <span v-if="!collapsed && item.badge && item.badge()" class="gov-nav-badge">{{ item.badge() }}</span>
          </Link>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="gov-main">
      <!-- Flash Messages -->
      <div v-if="flash?.success" class="gov-flash gov-flash-ok">
        ✅ {{ flash.success }}
      </div>
      <div v-if="flash?.error" class="gov-flash gov-flash-err">
        ❌ {{ flash.error }}
      </div>

      <slot />
    </main>
  </div>

  <!-- ═══ STATUS BAR (Bottom) ═══ -->
  <div class="gov-statusbar">
    <span>النظام: SDB Wallet Staff Portal</span>
    <span>الإصدار: 2.0.1</span>
    <span>الحالة: جاهز</span>
    <span>{{ u?.full_name }} — {{ roleLabel }}</span>
  </div>
</div>
</template>
<style scoped>
/* ═══════════════════════════════════════════════
   GOVERNMENT / INSTITUTIONAL SYSTEM DESIGN
   ═══════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap');

.gov {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  direction: rtl;
  font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
  background: #c0c0c0;
  color: #1a1a1a;
  font-size: 13px;
}

/* ═══ TOP TOOLBAR ═══ */
.gov-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 12px;
  height: 42px;
  background: linear-gradient(180deg, #f0f0f0 0%, #d4d4d4 50%, #c8c8c8 100%);
  border-bottom: 2px solid #808080;
  box-shadow: inset 0 1px 0 #fff;
  flex-shrink: 0;
}

.gov-toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.gov-logo {
  display: flex;
  align-items: center;
  gap: 8px;
}

.gov-logo-icon {
  flex-shrink: 0;
}

.gov-logo-title {
  font-size: 15px;
  font-weight: 900;
  color: #1a5c2a;
  letter-spacing: -0.3px;
}

.gov-logo-sub {
  display: block;
  font-size: 9px;
  color: #666;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.gov-toolbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.gov-toolbar-info {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #555;
}

.gov-toolbar-sep {
  color: #999;
}

.gov-online {
  color: #1a8c3a;
  font-weight: 700;
}

.gov-logout-btn {
  padding: 3px 12px;
  background: linear-gradient(180deg, #f8f8f8, #e0e0e0);
  border: 1px solid #999;
  border-radius: 3px;
  font-size: 11px;
  font-weight: 700;
  color: #333;
  cursor: pointer;
  font-family: inherit;
  box-shadow: inset 0 1px 0 #fff, 0 1px 2px rgba(0,0,0,.1);
}

.gov-logout-btn:hover {
  background: linear-gradient(180deg, #fff, #e8e8e8);
}

.gov-logout-btn:active {
  background: linear-gradient(180deg, #d0d0d0, #e0e0e0);
  box-shadow: inset 0 1px 2px rgba(0,0,0,.15);
}

/* ═══ USER INFO BAR ═══ */
.gov-userbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 12px;
  background: linear-gradient(180deg, #e8eef4, #d0d8e2);
  border-bottom: 1px solid #a0a8b0;
  flex-wrap: wrap;
  gap: 4px;
  min-height: 28px;
  flex-shrink: 0;
}

.gov-userbar-right, .gov-userbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.gov-field {
  font-size: 11px;
  color: #333;
}

.gov-label {
  font-weight: 800;
  color: #1a3a5c;
}

.gov-perm-label {
  font-size: 10px;
  font-weight: 800;
  color: #1a3a5c;
}

.gov-perm-tag {
  font-size: 9px;
  font-weight: 700;
  background: #1a5c2a;
  color: #fff;
  padding: 1px 6px;
  border-radius: 2px;
  letter-spacing: 0.3px;
}

.gov-perm-none {
  background: #999;
}

/* ═══ BODY ═══ */
.gov-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* ═══ SIDEBAR ═══ */
.gov-sidebar {
  width: 220px;
  background: linear-gradient(180deg, #e4e8ec, #d0d4d8);
  border-left: 2px solid #a0a4a8;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow-y: auto;
  overflow-x: hidden;
  transition: width 0.2s;
}

.gov-sb-collapsed {
  width: 42px;
}

.gov-sb-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 8px;
  background: linear-gradient(180deg, #6888a8, #4a6a8a);
  color: #fff;
  font-size: 12px;
  font-weight: 800;
  border-bottom: 1px solid #3a5a7a;
  min-height: 28px;
}

.gov-sb-toggle {
  background: none;
  border: 1px solid rgba(255,255,255,.3);
  color: #fff;
  width: 20px;
  height: 20px;
  border-radius: 2px;
  cursor: pointer;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.gov-sb-toggle:hover {
  background: rgba(255,255,255,.15);
}

/* Navigation */
.gov-nav {
  flex: 1;
  padding: 4px 0;
}

.gov-nav-group {
  margin-bottom: 2px;
}

.gov-nav-title {
  font-size: 10px;
  font-weight: 800;
  color: #556;
  padding: 6px 10px 3px;
  letter-spacing: 0.3px;
  border-bottom: 1px solid #b8bcc0;
  margin: 0 4px;
}

.gov-nav-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  text-decoration: none;
  color: #2a2a2a;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.1s;
  border: 1px solid transparent;
  margin: 1px 4px;
  border-radius: 2px;
}

.gov-nav-item:hover {
  background: linear-gradient(180deg, #fff, #e8ecf0);
  border-color: #8898a8;
  box-shadow: inset 0 1px 0 #fff;
}

.gov-nav-active {
  background: linear-gradient(180deg, #1a5c2a, #145020) !important;
  color: #fff !important;
  border-color: #0a3a1a !important;
  font-weight: 700;
  box-shadow: inset 0 1px 2px rgba(0,0,0,.2);
}

.gov-nav-active .gov-nav-icon {
  filter: brightness(1.5);
}

.gov-nav-icon {
  font-size: 12px;
  width: 18px;
  text-align: center;
  flex-shrink: 0;
}

.gov-nav-text {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.gov-nav-badge {
  background: #cc0000;
  color: #fff;
  font-size: 9px;
  font-weight: 900;
  padding: 0 5px;
  border-radius: 2px;
  min-width: 14px;
  text-align: center;
  line-height: 16px;
}

/* ═══ MAIN CONTENT ═══ */
.gov-main {
  flex: 1;
  overflow-y: auto;
  background: #f0f0f0;
  border: 1px inset #c0c0c0;
}

/* Flash Messages */
.gov-flash {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 700;
  margin: 6px 8px;
  border-radius: 2px;
  border: 1px solid;
}

.gov-flash-ok {
  background: #e6f4ea;
  color: #1a5c2a;
  border-color: #4a8c5a;
}

.gov-flash-err {
  background: #fce8e6;
  color: #c53929;
  border-color: #c53929;
}

/* Content Padding */
.gov-main > :deep(:last-child:not(.gov-flash)) {
  padding: 8px;
}

/* ═══ STATUS BAR ═══ */
.gov-statusbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2px 12px;
  height: 22px;
  background: linear-gradient(180deg, #e0e0e0, #c8c8c8);
  border-top: 1px solid #fff;
  font-size: 10px;
  color: #666;
  flex-shrink: 0;
}

/* ═══ SCROLLBAR (Classic) ═══ */
.gov-sidebar::-webkit-scrollbar {
  width: 14px;
}

.gov-sidebar::-webkit-scrollbar-track {
  background: #e0e0e0;
  border: 1px solid #c0c0c0;
}

.gov-sidebar::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #d0d0d0, #b0b0b0);
  border: 1px solid #888;
  border-radius: 0;
}

.gov-sidebar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #c0c0c0, #a0a0a0);
}

.gov-main::-webkit-scrollbar {
  width: 14px;
}

.gov-main::-webkit-scrollbar-track {
  background: #e0e0e0;
  border: 1px solid #c0c0c0;
}

.gov-main::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #d0d0d0, #b0b0b0);
  border: 1px solid #888;
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 768px) {
  .gov-toolbar {
    flex-direction: column;
    height: auto;
    padding: 6px 10px;
    gap: 4px;
  }
  .gov-userbar {
    flex-direction: column;
    gap: 4px;
    padding: 6px 10px;
  }
  .gov-sidebar {
    width: 42px;
  }
  .gov-sb-collapsed {
    width: 0;
    display: none;
  }
  .gov-statusbar {
    font-size: 9px;
  }
}
</style>
