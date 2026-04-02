<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import AdminCallOverlay from '@/Components/AdminCallOverlay.vue';
import EmployeeLayout from './EmployeeLayout.vue';

const sidebarOpen = ref(true);
const page = usePage();
const currentRoute = computed(() => page.url);
const isEmployee = computed(() => page.props.auth?.user?.role === 'employee');

// Global Search
const searchQuery = ref('');
const searchResults = ref([]);
const searchOpen = ref(false);
let searchTimeout = null;

watch(searchQuery, (val) => {
  clearTimeout(searchTimeout);
  if (val.length < 2) { searchResults.value = []; searchOpen.value = false; return; }
  searchTimeout = setTimeout(async () => {
    try {
      const res = await fetch(route('admin.search') + '?q=' + encodeURIComponent(val));
      const data = await res.json();
      searchResults.value = data.results || [];
      searchOpen.value = searchResults.value.length > 0;
    } catch (e) { searchResults.value = []; }
  }, 300);
});

const goToResult = (r) => {
  searchOpen.value = false;
  searchQuery.value = '';
  router.visit(r.url);
};

const closeSearch = () => { searchOpen.value = false; };

// Notifications badge
const pendingKyc = computed(() => page.props.auth?.admin_stats?.pending_kyc || 0);
const newUsersToday = computed(() => page.props.auth?.admin_stats?.new_users_today || 0);
const unreadChats = computed(() => page.props.auth?.admin_stats?.unread_chats || 0);
const frozenSupport = computed(() => page.props.auth?.admin_stats?.frozen_support || 0);
const notifCount = computed(() => pendingKyc.value + newUsersToday.value + unreadChats.value + frozenSupport.value);

// AI Chat
const aiOpen = ref(false);
const aiMsg = ref('');
const aiMessages = ref([]);
const aiLoading = ref(false);
const aiChatBody = ref(null);

const sendAiMsg = async () => {
  const msg = aiMsg.value.trim();
  if (!msg || aiLoading.value) return;
  aiMessages.value.push({ role: 'user', content: msg });
  aiMsg.value = '';
  aiLoading.value = true;
  nextTick(() => { if (aiChatBody.value) aiChatBody.value.scrollTop = aiChatBody.value.scrollHeight; });
  try {
    const history = aiMessages.value.slice(0, -1).map(m => ({ role: m.role === 'user' ? 'user' : 'assistant', content: m.content }));
    const res = await fetch(route('admin.ai-chat'), {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=_t]')?.content || document.querySelector('meta[name=sw-token]')?.content || document.querySelector('meta[name=csrf-token]')?.content },
      body: JSON.stringify({ message: msg, history }),
    });
    const data = await res.json();
    aiMessages.value.push({ role: 'assistant', content: data.reply || 'خطأ' });
  } catch (e) { aiMessages.value.push({ role: 'assistant', content: '❌ خطأ في الاتصال' }); }
  aiLoading.value = false;
  nextTick(() => { if (aiChatBody.value) aiChatBody.value.scrollTop = aiChatBody.value.scrollHeight; });
};

// RBAC — get user role
const userRole = computed(() => page.props.auth?.user?.role || 'admin');
const employeeRole = computed(() => page.props.auth?.user?.employee_role || '');
const isSuperAdmin = computed(() => ['super_admin', 'admin'].includes(userRole.value));
const employeePermissions = computed(() => page.props.auth?.employee_permissions || []);

const hasAccess = (roles) => {
  if (!roles || isSuperAdmin.value) return true;
  if (userRole.value === 'employee') {
    const role = employeeRole.value;
    // Employee admins and team leads get full access
    if (['admin', 'team_lead'].includes(role)) return true;
    // Check against employee_permissions (multi-permission system)
    const perms = employeePermissions.value;
    // Special: 'admin_only' means only super_admin/admin roles
    if (roles.includes('admin_only')) return false;
    return roles.some(r => perms.includes(r));
  }
  return true;
};

// ═══ Live Permission Sync (poll every 30s) ═══
let permissionPoll = null;
const lastPermUpdated = ref(page.props.auth?.permissions_updated_at || '');

const checkPermissionUpdates = async () => {
  if (userRole.value !== 'employee') return;
  try {
    const res = await fetch(route('admin.employees.check-permissions'), {
      headers: { 'Accept': 'application/json' },
    });
    if (!res.ok) return;
    const data = await res.json();
    if (data.permissions_updated_at && data.permissions_updated_at !== lastPermUpdated.value) {
      lastPermUpdated.value = data.permissions_updated_at;
      // Permissions changed — reload page to reflect new sidebar
      router.reload({ preserveScroll: true });
    }
  } catch (e) { /* silent */ }
};

onMounted(() => {
  if (userRole.value === 'employee') {
    permissionPoll = setInterval(checkPermissionUpdates, 30000);
  }
});

onUnmounted(() => {
  if (permissionPoll) clearInterval(permissionPoll);
});

// Sidebar navigation — organized into logical categories with RBAC
const sideItems = [
  { label: 'لوحة التحكم', icon: 'dashboard', route: 'admin.dashboard' },
  { id: 'finance', label: 'العمليات المالية', icon: 'payments', roles: ['finance'], children: [
    { label: 'الإيداعات', route: 'admin.transactions' },
    { label: 'التحويلات', route: 'admin.accounts' },
    { label: 'التسويات', route: 'admin.remittances' },
    { label: 'عمليات الصرف', route: 'admin.currencies' },
    { label: 'الرسوم', route: 'admin.fees' },
    { label: 'البطاقات', route: 'admin.cards' },
    { label: 'الكريبتو', route: 'admin.crypto' },
    { label: '🏦 NymCard', route: 'admin.nymcard' },
  ]},
  { id: 'customers', label: 'العملاء', icon: 'users', roles: ['support','compliance'], children: [
    { label: 'جميع العملاء', route: 'admin.users' },
    { label: 'التحقق KYC', route: 'admin.kyc' },
    { label: 'تصنيف العملاء', route: 'admin.tags' },
    { label: 'الحسابات المجمّدة', route: 'admin.frozen' },
    { label: 'الاشتراكات', route: 'admin.subscriptions' },
    { label: 'خريطة العملاء', route: 'admin.customer-map' },
  ]},
  { id: 'merchants', label: 'التجار والوكلاء', icon: 'shop', roles: ['finance'], children: [
    { label: 'لوحة الشركات', route: 'admin.businesses.dashboard' },
    { label: 'حسابات الشركات', route: 'admin.businesses.index' },
    { label: 'التجار', route: 'admin.merchants' },
    { label: 'الموافقات', route: 'admin.approvals' },
    { label: 'الوكلاء', route: 'admin.agents' },
    { label: 'الحوالات', route: 'admin.remittances' },
  ]},
  { label: 'الاستشارات', icon: 'consult', route: 'admin.consultations', roles: ['support', 'finance'] },
  { id: 'employees', label: 'الدعم الفني', icon: 'support', roles: ['support'], children: [
    { label: 'إدارة الموظفين', route: 'admin.employees' },
    { label: 'الدعم الفني', route: 'admin.support' },
    { label: 'تذاكر الدعم', route: 'admin.tickets' },
    { label: 'محادثات الدعم', route: 'admin.chat' },
    { label: 'طلبات خاصة', route: 'admin.special-requests' },
  ]},
  { id: 'analytics', label: 'التقارير والتحليلات', icon: 'dashboard', roles: ['finance'], children: [
    { label: 'التحليلات', route: 'admin.analytics' },
    { label: 'تحليل المستخدمين', route: 'admin.user-analytics' },
    { label: 'الإيرادات', route: 'admin.revenue' },
    { label: 'الاحتفاظ', route: 'admin.retention' },
    { label: 'KPIs حية', route: 'admin.live-kpi' },
    { label: 'التقارير', route: 'admin.pdf-reports' },
    { label: 'مركز التقارير', route: 'admin.report-center' },
    { label: 'التقارير العامة', route: 'admin.reports' },
  ]},
  { id: 'security', label: 'الأمان والمخاطر', icon: 'settings', roles: ['compliance'], children: [
    { label: '🛡️ مركز الأمان', route: 'admin.security' },
    { label: 'كشف الاحتيال', route: 'admin.fraud' },
    { label: 'مكافحة غسيل الأموال', route: 'admin.aml' },
    { label: 'مراقبة المعاملات', route: 'admin.transaction-monitor' },
    { label: 'لوحة المخاطر', route: 'admin.risk-dashboard' },
    { label: 'الامتثال', route: 'admin.compliance' },
    { label: 'المخاطر والامتثال', route: 'admin.risk' },
    { label: 'سجل التحقق', route: 'admin.verification-logs' },
    { label: 'سجل التدقيق', route: 'admin.audit-logs' },
    { label: 'IP Whitelist', route: 'admin.ip-whitelist' },
    { label: 'التنبيهات الأمنية', route: 'admin.alerts' },
    { label: 'سجل التغييرات', route: 'admin.changelog' },
  ]},
  { id: 'marketing', label: 'التسويق والتواصل', icon: 'consult', roles: ['marketing'], children: [
    { label: 'العروض والترويج', route: 'admin.promotions' },
    { label: 'الإحالات', route: 'admin.referrals' },
    { label: 'حملات البريد', route: 'admin.campaigns' },
    { label: 'قوالب البريد', route: 'admin.email-templates' },
    { label: 'قائمة الانتظار', route: 'admin.waitlist' },
    { label: 'إشعارات جماعية', route: 'admin.broadcast' },
    { label: 'التواصل', route: 'admin.communications' },
  ]},
  { id: 'app', label: 'التطبيق', icon: 'cards', roles: ['app_management'], children: [
    { label: 'إدارة التطبيق', route: 'admin.app-management' },
    { label: '🎉 المناسبات والأعياد', route: 'admin.holidays' },
    { label: 'خلفيات التطبيق', route: 'admin.backgrounds' },
    { label: 'الصور والشعارات', route: 'admin.assets' },
    { label: 'رسالة داخل التطبيق', route: 'admin.in-app-message' },
  ]},
  { id: 'settings', label: 'الإعدادات', icon: 'settings', roles: ['settings'], children: [
    { label: 'إعدادات عامة', route: 'admin.settings' },
    { label: 'إدارة المحتوى', route: 'admin.cms' },
    { label: 'حالة API', route: 'admin.api-status' },
    { label: 'التكاملات', route: 'admin.integrations' },
    { label: 'إدارة البيانات', route: 'admin.data-management' },
    { label: 'المهام', route: 'admin.tasks' },
    { label: 'الأمان', route: 'admin.security' },
    { label: 'الجلسات', route: 'admin.sessions' },
    { label: 'أسعار الصرف', route: 'admin.rates' },
    { label: 'الدول', route: 'admin.countries' },
    { label: 'الحدود', route: 'admin.limits' },
  ]},
];

// SVG icon map
const iconMap = {
  dashboard: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
  deposit: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>',
  transfer: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>',
  settle: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 14l6-6"/><circle cx="9.5" cy="8.5" r="1.5"/><circle cx="14.5" cy="13.5" r="1.5"/><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>',
  exchange: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/><path d="M20 16l-4 4-4-4M4 8l4-4 4 4"/></svg>',
  payments: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
  cards: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
  bitcoin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.767 19.089c4.924.868 6.14-6.025 1.216-6.894m-1.216 6.894L5.86 18.047m5.908 1.042-.347 1.97m1.563-8.864c4.924.869 6.14-6.025 1.215-6.893m-1.215 6.893-3.94-.694m5.155-6.2L8.29 4.26m5.908 1.042.348-1.97M7.48 20.364l3.126-17.727"/></svg>',
  consult: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
  support: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  shop: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  agent: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
  employee: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
  settings: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
};

// Track open groups
const openGroups = ref({});

const toggleGroup = (id) => {
  openGroups.value[id] = !openGroups.value[id];
};

const isActive = (r) => {
  try {
    const url = route(r);
    return currentRoute.value.startsWith(url.replace(window.location.origin, ''));
  } catch { return false; }
};

// Auto-open groups that contain an active route
watch(currentRoute, () => {
  sideItems.forEach(item => {
    if (item.children) {
      const hasActive = item.children.some(c => isActive(c.route));
      if (hasActive) openGroups.value[item.id] = true;
    }
  });
}, { immediate: true });

// Page title from current route
const pageTitle = computed(() => {
  for (const item of sideItems) {
    if (item.route && isActive(item.route)) return item.label;
    if (item.children) {
      const child = item.children.find(c => isActive(c.route));
      if (child) return child.label;
    }
  }
  return 'لوحة التحكم';
});

defineProps({ title: { type: String, default: '' }, subtitle: { type: String, default: '' } });
</script>

<template>
  <!-- Employee users → render EmployeeLayout (government style) -->
  <EmployeeLayout v-if="isEmployee">
    <slot />
  </EmployeeLayout>

  <!-- Admin/Super Admin → render normal AdminLayout -->
  <div v-else class="ps-root">
    <!-- SIDEBAR -->
    <aside class="ps-sidebar" :class="{'ps-collapsed': !sidebarOpen}">
      <!-- Logo -->
      <div class="ps-logo">
        <div class="ps-logo-mark">
          <img src="/images/sdb-logo.png" alt="SDB Wallet" style="width:34px;height:34px;border-radius:8px;object-fit:contain" />
        </div>
      </div>

      <!-- Nav Items -->
      <nav class="ps-nav">
        <template v-for="item in sideItems" :key="item.label">
          <!-- Simple link (no children) -->
          <Link v-if="item.route && !item.children && hasAccess(item.roles)"
            :href="route(item.route)"
            :class="['ps-nav-item', isActive(item.route) ? 'ps-active' : '']">
            <span class="ps-nav-svg" v-html="iconMap[item.icon]"></span>
            <span v-if="sidebarOpen" class="ps-nav-label">{{ item.label }}</span>
            <span v-if="item.route === 'admin.support' && unreadChats > 0" class="ps-nav-badge">{{ unreadChats }}</span>
          </Link>

          <!-- Group with children -->
          <template v-if="item.children && hasAccess(item.roles)">
            <button
              :class="['ps-nav-item ps-group-btn', item.children.some(c => isActive(c.route)) ? 'ps-active' : '']"
              @click="toggleGroup(item.id)"
              v-if="sidebarOpen">
              <span class="ps-nav-svg" v-html="iconMap[item.icon]"></span>
              <span class="ps-nav-label">{{ item.label }}</span>
              <span v-if="item.id === 'employees' && unreadChats > 0" class="ps-nav-badge ps-badge-blue">{{ unreadChats }}</span>
              <span v-if="item.id === 'customers' && pendingKyc > 0" class="ps-nav-badge">{{ pendingKyc }}</span>
              <svg class="ps-chevron" :class="{'ps-chevron-open': openGroups[item.id]}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <!-- Collapsed: just icon -->
            <div v-else class="ps-nav-item ps-nav-icon-only"
              :class="{'ps-active': item.children.some(c => isActive(c.route))}"
              :title="item.label"
              @click="toggleGroup(item.id)">
              <span class="ps-nav-svg" v-html="iconMap[item.icon]"></span>
            </div>
            <!-- Children -->
            <div v-if="sidebarOpen && openGroups[item.id]" class="ps-children">
              <Link v-for="child in item.children" :key="child.route"
                :href="route(child.route)"
                :class="['ps-child-item', isActive(child.route) ? 'ps-child-active' : '']">
                {{ child.label }}
                <span v-if="child.route === 'admin.kyc' && pendingKyc > 0" class="ps-nav-badge">{{ pendingKyc }}</span>
                <span v-if="child.route === 'admin.support' && unreadChats > 0" class="ps-nav-badge ps-badge-blue">{{ unreadChats }}</span>
                <span v-if="child.route === 'admin.chat' && unreadChats > 0" class="ps-nav-badge ps-badge-blue">{{ unreadChats }}</span>
                <span v-if="child.route === 'admin.frozen' && frozenSupport > 0" class="ps-nav-badge">{{ frozenSupport }}</span>
              </Link>
            </div>
          </template>
        </template>
      </nav>

      <!-- Logout at bottom -->
      <div class="ps-nav-bottom">
        <Link :href="route('logout')" method="post" as="button" class="ps-nav-item ps-logout">
          <svg class="ps-nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span v-if="sidebarOpen" class="ps-nav-label">تسجيل الخروج</span>
        </Link>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="ps-main">
      <!-- TOP BAR -->
      <header class="ps-topbar">
        <div class="ps-topbar-right">
          <button @click="sidebarOpen = !sidebarOpen" class="ps-toggle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
          <h1 class="ps-page-title">{{ pageTitle }}</h1>
        </div>

        <div class="ps-topbar-center">
          <!-- Global Search -->
          <div class="ps-search-wrap" @click.stop>
            <div class="ps-search-box">
              <svg class="ps-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input v-model="searchQuery" type="text" placeholder="بحث سريع..." class="ps-search-input" @focus="searchOpen = searchResults.length > 0" @blur="setTimeout(closeSearch, 200)" />
            </div>
            <!-- Search Results -->
            <div v-if="searchOpen" class="ps-search-dropdown">
              <div v-for="(r, i) in searchResults" :key="i" class="ps-search-result" @mousedown.prevent="goToResult(r)">
                <span class="ps-sr-icon">{{ r.icon }}</span>
                <div class="ps-sr-info">
                  <div class="ps-sr-title">{{ r.title }}</div>
                  <div class="ps-sr-sub">{{ r.subtitle }}</div>
                </div>
                <span :class="['ps-sr-status', 'ps-sr-' + r.status]">{{ r.status }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="ps-topbar-left">
          <slot name="actions" />
          <!-- AI Assistant Button (in topbar) -->
          <button class="ai-topbar-btn" @click="aiOpen = !aiOpen" :class="{active: aiOpen}" title="مساعد SDB الذكي">
            <span>🤖</span>
          </button>
          <!-- Notification Bell -->
          <Link v-if="notifCount > 0" :href="route('admin.kyc')" class="ps-notif-btn" title="إشعارات">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="ps-notif-badge">{{ notifCount }}</span>
          </Link>
          <!-- Admin badge -->
          <div class="ps-admin-badge">
            <div class="ps-admin-avatar">{{ (page.props.auth?.user?.name || 'A').charAt(0) }}</div>
            <span v-if="sidebarOpen" class="ps-admin-name">{{ page.props.auth?.user?.name || 'Admin' }}</span>
          </div>
        </div>
      </header>

      <!-- Content -->
      <div class="ps-content">
        <slot />
      </div>
    </main>

    <!-- AI Chat Panel -->
    <div class="ai-panel" v-if="aiOpen">
      <div class="ai-header">
        <div class="ai-header-info">
          <span class="ai-avatar">🤖</span>
          <div>
            <div class="ai-name">مساعد SDB</div>
            <div class="ai-status">Gemini AI · متصل</div>
          </div>
        </div>
        <button class="ai-close" @click="aiOpen = false">✕</button>
      </div>
      <div class="ai-body" ref="aiChatBody">
        <div v-if="!aiMessages.length" class="ai-welcome">
          <div class="ai-welcome-icon">🤖</div>
          <div class="ai-welcome-title">مرحباً! أنا مساعد SDB</div>
          <div class="ai-welcome-text">أقدر أساعدك بكل شي بالأدمن</div>
          <div class="ai-suggestions">
            <button @click="aiMsg = 'كيف أتحقق من هوية عميل جديد؟'; sendAiMsg()">🪪 كيف أتحقق من هوية عميل؟</button>
            <button @click="aiMsg = 'ما هي إحصائيات اليوم؟'; sendAiMsg()">📊 إحصائيات اليوم</button>
            <button @click="aiMsg = 'كيف أجمّد حساب مشبوه؟'; sendAiMsg()">🧊 تجميد حساب مشبوه</button>
          </div>
        </div>
        <div v-for="(m, i) in aiMessages" :key="i" :class="['ai-msg', m.role === 'user' ? 'ai-msg-user' : 'ai-msg-bot']">
          <div class="ai-msg-bubble" v-html="m.content.replace(/\n/g, '<br>')"></div>
        </div>
        <div v-if="aiLoading" class="ai-msg ai-msg-bot">
          <div class="ai-msg-bubble ai-typing"><span></span><span></span><span></span></div>
        </div>
      </div>
      <div class="ai-input-wrap">
        <input v-model="aiMsg" @keyup.enter="sendAiMsg" placeholder="اكتب سؤالك..." class="ai-input" :disabled="aiLoading" />
        <button @click="sendAiMsg" class="ai-send" :disabled="aiLoading || !aiMsg.trim()">➤</button>
      </div>
    </div>
    
    <!-- Global WebRTC Admin Call Overlay -->
    <AdminCallOverlay />

  </div>
</template>

<style>
@import '../../css/admin.css';

/* ═══════════════════════════════════════
   PaySnap-Style Admin Layout
   ═══════════════════════════════════════ */
.ps-root {
  display: flex; min-height: 100vh;
  background: #f8f9fb; color: #1a1a2e;
  direction: rtl; font-family: 'Inter', -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
}

/* ─── SIDEBAR ─── */
.ps-sidebar {
  width: 240px; background: #ffffff;
  display: flex; flex-direction: column; flex-shrink: 0;
  border-left: 1px solid #eef0f4;
  transition: width .25s cubic-bezier(.4,0,.2,1);
  z-index: 10;
}
.ps-collapsed { width: 64px; overflow: hidden; }

.ps-logo {
  display: flex; align-items: center; gap: 10px;
  padding: 18px 16px;
  border-bottom: 1px solid #eef0f4;
}
.ps-logo-mark { width: 34px; height: 34px; flex-shrink: 0; }
.ps-logo-mark svg { width: 100%; height: 100%; }
.ps-brand { font-size: 17px; font-weight: 800; color: #1a1a2e; letter-spacing: -0.3px; }

.ps-nav {
  padding: 10px 8px; display: flex; flex-direction: column; gap: 2px;
  flex: 1; overflow-y: auto;
}
.ps-nav::-webkit-scrollbar { width: 3px; }
.ps-nav::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }

.ps-nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; border-radius: 10px;
  font-size: 13px; color: #6b7280;
  text-decoration: none; font-weight: 600;
  transition: all .15s; border: none; background: none;
  cursor: pointer; width: 100%; text-align: right;
}
.ps-nav-item:hover { background: #f3f4f6; color: #1a1a2e; }
.ps-active {
  background: #ecfdf5 !important; color: #059669 !important; font-weight: 700;
}
.ps-active .ps-nav-svg { color: #10b981; }

.ps-nav-svg { width: 20px; height: 20px; flex-shrink: 0; color: #9ca3af; transition: color .15s; }
.ps-nav-item:hover .ps-nav-svg { color: #6b7280; }
.ps-nav-label { white-space: nowrap; flex: 1; }

.ps-nav-badge {
  background: #ef4444; color: #fff; font-size: 10px; font-weight: 700;
  padding: 1px 6px; border-radius: 8px; min-width: 16px; text-align: center;
}
.ps-badge-blue { background: #3b82f6; }

.ps-nav-icon-only { justify-content: center; padding: 10px; }

/* Chevron */
.ps-chevron { width: 16px; height: 16px; margin-right: auto; color: #d1d5db; transition: transform .2s; flex-shrink: 0; }
.ps-chevron-open { transform: rotate(180deg); }

.ps-group-btn { position: relative; }

/* Children */
.ps-children {
  display: flex; flex-direction: column; gap: 1px;
  padding: 2px 0 4px 0; margin-right: 20px;
  border-right: 2px solid #eef0f4;
  animation: psSlide .2s ease;
}
@keyframes psSlide { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

.ps-child-item {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 12px; border-radius: 8px;
  font-size: 12px; color: #9ca3af; text-decoration: none;
  font-weight: 600; transition: all .15s;
}
.ps-child-item:hover { background: #f9fafb; color: #374151; }
.ps-child-active { color: #059669 !important; background: #ecfdf5; font-weight: 700; }

.ps-nav-bottom { padding: 8px; border-top: 1px solid #eef0f4; }
.ps-logout { color: #ef4444 !important; }
.ps-logout:hover { background: #fef2f2 !important; }
.ps-logout .ps-nav-svg { color: #ef4444; }

/* ─── MAIN ─── */
.ps-main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }

/* ─── TOPBAR ─── */
.ps-topbar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 24px;
  background: #ffffff; border-bottom: 1px solid #eef0f4;
  flex-shrink: 0; gap: 16px;
}
.ps-topbar-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.ps-topbar-center { flex: 1; max-width: 400px; margin: 0 auto; }
.ps-topbar-left { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.ps-toggle {
  color: #9ca3af; background: none; border: none;
  cursor: pointer; padding: 6px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
}
.ps-toggle:hover { background: #f3f4f6; color: #374151; }

.ps-page-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0; }

/* Search */
.ps-search-wrap { position: relative; width: 100%; }
.ps-search-box {
  display: flex; align-items: center; gap: 8px;
  background: #f3f4f6; border: 1px solid transparent; border-radius: 10px;
  padding: 7px 12px; transition: all .2s;
}
.ps-search-box:focus-within { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
.ps-search-icon { color: #9ca3af; flex-shrink: 0; }
.ps-search-input { flex: 1; border: none; background: transparent; padding: 0; font-size: 13px; color: #1a1a2e; outline: none; direction: rtl; }
.ps-search-input::placeholder { color: #9ca3af; }

.ps-search-dropdown { position: absolute; top: 100%; right: 0; left: 0; background: #fff; border: 1px solid #eef0f4; border-radius: 12px; margin-top: 6px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); z-index: 999; max-height: 320px; overflow-y: auto; }
.ps-search-result { display: flex; align-items: center; gap: 10px; padding: 10px 14px; cursor: pointer; transition: background .15s; border-bottom: 1px solid #f9fafb; }
.ps-search-result:hover { background: #f9fafb; }
.ps-sr-icon { font-size: 18px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 8px; flex-shrink: 0; }
.ps-sr-info { flex: 1; min-width: 0; }
.ps-sr-title { font-size: 13px; font-weight: 600; color: #1a1a2e; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ps-sr-sub { font-size: 11px; color: #9ca3af; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ps-sr-status { font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 6px; }
.ps-sr-active { color: #059669; background: #ecfdf5; }
.ps-sr-pending { color: #d97706; background: #fffbeb; }
.ps-sr-frozen { color: #3b82f6; background: #eff6ff; }
.ps-sr-completed { color: #059669; background: #ecfdf5; }
.ps-sr-failed,.ps-sr-suspended,.ps-sr-blocked,.ps-sr-cancelled { color: #dc2626; background: #fef2f2; }

/* Notification */
.ps-notif-btn { position: relative; color: #9ca3af; padding: 6px; border-radius: 8px; transition: all .15s; text-decoration: none; display: flex; align-items: center; }
.ps-notif-btn:hover { background: #f3f4f6; color: #374151; }
.ps-notif-badge { position: absolute; top: 0; right: -2px; background: #ef4444; color: #fff; font-size: 9px; font-weight: 700; padding: 1px 4px; border-radius: 6px; min-width: 14px; text-align: center; }

/* Admin badge */
.ps-admin-badge { display: flex; align-items: center; gap: 8px; padding: 4px 8px; border-radius: 10px; border: 1px solid #eef0f4; }
.ps-admin-avatar {
  width: 30px; height: 30px; border-radius: 8px;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 12px;
}
.ps-admin-name { font-size: 12px; font-weight: 600; color: #374151; white-space: nowrap; }

/* Content */
.ps-content { padding: 20px 24px; display: flex; flex-direction: column; gap: 20px; }

/* Override old layout */
.ps-root .min-h-screen { min-height: auto !important; background: transparent !important; }

@media (max-width: 1100px) {
  .ps-sidebar { width: 64px; overflow: hidden; }
  .ps-nav-label, .ps-children, .ps-brand, .ps-chevron, .ps-admin-name { display: none; }
  .ps-nav-icon-only { display: flex !important; }
  .ps-topbar-center { display: none; }
}

/* ═══ AI Chat Widget ═══ */
.ai-topbar-btn { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); color: white; font-size: 18px; border: none; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: all .2s; display: flex; align-items: center; justify-content: center; }
.ai-topbar-btn:hover { transform: scale(1.08); box-shadow: 0 4px 16px rgba(16,185,129,0.4); }
.ai-topbar-btn.active { background: #64748b; box-shadow: 0 2px 8px rgba(100,116,139,0.3); }

.ai-panel { position: fixed; top: 60px; left: 24px; width: 380px; max-height: 500px; background: #fff; border-radius: 18px; box-shadow: 0 12px 48px rgba(0,0,0,0.15); z-index: 9999; display: flex; flex-direction: column; overflow: hidden; animation: ai-slideDown .3s ease; }
@keyframes ai-slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

.ai-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: linear-gradient(135deg, #10b981, #059669); color: white; }
.ai-header-info { display: flex; align-items: center; gap: 10px; }
.ai-avatar { font-size: 24px; width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.ai-name { font-weight: 700; font-size: 14px; }
.ai-status { font-size: 10px; opacity: 0.85; }
.ai-close { background: none; border: none; color: white; font-size: 16px; cursor: pointer; opacity: 0.7; transition: opacity .2s; }
.ai-close:hover { opacity: 1; }

.ai-body { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 8px; max-height: 320px; direction: rtl; }
.ai-body::-webkit-scrollbar { width: 3px; }
.ai-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }
.ai-welcome { text-align: center; padding: 16px 8px; }
.ai-welcome-icon { font-size: 40px; margin-bottom: 10px; }
.ai-welcome-title { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
.ai-welcome-text { font-size: 12px; color: #9ca3af; margin-bottom: 14px; }
.ai-suggestions { display: flex; flex-direction: column; gap: 5px; }
.ai-suggestions button { background: #f3f4f6; border: 1px solid #eef0f4; border-radius: 8px; padding: 7px 12px; font-size: 12px; cursor: pointer; transition: all .2s; text-align: right; color: #374151; }
.ai-suggestions button:hover { background: #e5e7eb; border-color: #10b981; color: #059669; }

.ai-msg { display: flex; }
.ai-msg-user { justify-content: flex-end; }
.ai-msg-bot { justify-content: flex-start; }
.ai-msg-bubble { max-width: 85%; padding: 8px 12px; border-radius: 12px; font-size: 12px; line-height: 1.6; word-wrap: break-word; }
.ai-msg-user .ai-msg-bubble { background: #10b981; color: white; border-bottom-left-radius: 4px; }
.ai-msg-bot .ai-msg-bubble { background: #f3f4f6; color: #1a1a2e; border-bottom-right-radius: 4px; }

.ai-typing { display: flex; align-items: center; gap: 4px; padding: 10px 16px; }
.ai-typing span { width: 6px; height: 6px; background: #9ca3af; border-radius: 50%; animation: ai-dot 1.4s ease infinite; }
.ai-typing span:nth-child(2) { animation-delay: .2s; }
.ai-typing span:nth-child(3) { animation-delay: .4s; }
@keyframes ai-dot { 0%,100% { transform: translateY(0); opacity: .4; } 50% { transform: translateY(-5px); opacity: 1; } }

.ai-input-wrap { display: flex; gap: 6px; padding: 10px 14px; border-top: 1px solid #eef0f4; background: #fafafa; }
.ai-input { flex: 1; border: 1px solid #eef0f4; border-radius: 10px; padding: 8px 12px; font-size: 12px; direction: rtl; outline: none; transition: border .2s; }
.ai-input:focus { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.1); }
.ai-send { width: 36px; height: 36px; border-radius: 10px; background: #10b981; color: white; border: none; font-size: 14px; cursor: pointer; transition: all .2s; display: flex; align-items: center; justify-content: center; }
.ai-send:hover:not(:disabled) { background: #059669; }
.ai-send:disabled { opacity: 0.4; cursor: not-allowed; }

@media (max-width: 768px) {
  .ai-panel { left: 8px; right: 8px; width: auto; top: 56px; max-height: 65vh; }
}
</style>
