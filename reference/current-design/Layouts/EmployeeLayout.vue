<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
const page = usePage();
const collapsed = ref(false);
const u = computed(() => page.props.auth?.user);
const stats = computed(() => page.props.auth?.admin_stats);
const flash = computed(() => page.props.flash);
const nav = [
  { label: 'لوحة التحكم', icon: '🏠', href: 'employee.dashboard' },
  { label: 'بحث عن عميل', icon: '🔍', href: 'employee.search' },
  { label: 'تذاكر الدعم', icon: '🎫', href: 'employee.tickets', badge: () => stats.value?.pending_kyc },
  { label: 'الشات', icon: '💬', href: 'employee.chat', badge: () => stats.value?.unread_chats },
  { label: 'الحوالات', icon: '💸', href: 'employee.remittances' },
  { label: 'شروط الاستخدام', icon: '📋', href: 'employee.terms' },
];
</script>
<template>
<div class="el">
  <aside class="el-side" :class="{ 'el-collapsed': collapsed }">
    <div class="el-logo">
      <span v-if="!collapsed" class="el-logo-text">🏦 SDB Wallet</span>
      <span v-else class="el-logo-icon">🏦</span>
      <button class="el-toggle" @click="collapsed = !collapsed">{{ collapsed ? '→' : '←' }}</button>
    </div>
    <div class="el-role">
      <span class="el-role-badge">👤 موظف دعم</span>
    </div>
    <nav class="el-nav">
      <Link v-for="n in nav" :key="n.href" :href="route(n.href)" class="el-link"
        :class="{ 'el-active': route().current(n.href) }">
        <span class="el-link-ico">{{ n.icon }}</span>
        <span v-if="!collapsed" class="el-link-lbl">{{ n.label }}</span>
        <span v-if="!collapsed && n.badge && n.badge()" class="el-badge">{{ n.badge() }}</span>
      </Link>
    </nav>
    <div class="el-user" v-if="!collapsed">
      <div class="el-user-info">
        <div class="el-user-name">{{ u?.full_name }}</div>
        <div class="el-user-email">{{ u?.email }}</div>
      </div>
      <Link :href="route('logout')" method="post" as="button" class="el-logout">🚪</Link>
    </div>
  </aside>
  <main class="el-main">
    <div v-if="flash?.success" class="el-flash el-flash-ok">{{ flash.success }}</div>
    <div v-if="flash?.error" class="el-flash el-flash-err">{{ flash.error }}</div>
    <slot />
  </main>
</div>
</template>
<style scoped>
.el{display:flex;min-height:100vh;direction:rtl;font-family:'Inter','Segoe UI',system-ui,sans-serif}
.el-side{width:240px;background:linear-gradient(180deg,#0f172a,#1e293b);color:#e2e8f0;display:flex;flex-direction:column;padding:16px 12px;transition:width .2s;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto}
.el-collapsed{width:60px}
.el-logo{display:flex;justify-content:space-between;align-items:center;padding-bottom:12px;border-bottom:1px solid #334155;margin-bottom:12px}
.el-logo-text{font-size:16px;font-weight:900;color:#fff}
.el-logo-icon{font-size:20px}
.el-toggle{background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;padding:4px}
.el-role{text-align:center;margin-bottom:16px}
.el-role-badge{font-size:11px;font-weight:700;color:#38bdf8;background:#0c4a6e;padding:4px 12px;border-radius:8px}
.el-nav{flex:1;display:flex;flex-direction:column;gap:4px}
.el-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:#94a3b8;font-size:13px;font-weight:600;transition:all .15s}
.el-link:hover{background:#334155;color:#f1f5f9}
.el-active{background:#1d4ed8 !important;color:#fff !important}
.el-link-ico{font-size:18px;flex-shrink:0}
.el-link-lbl{flex:1}
.el-badge{background:#ef4444;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:10px;min-width:18px;text-align:center}
.el-user{display:flex;align-items:center;gap:8px;padding-top:12px;border-top:1px solid #334155;margin-top:8px}
.el-user-info{flex:1;min-width:0}
.el-user-name{font-size:12px;font-weight:700;color:#f1f5f9;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.el-user-email{font-size:10px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.el-logout{background:none;border:none;color:#ef4444;font-size:16px;cursor:pointer;padding:6px}
.el-main{flex:1;background:#f8fafc;padding:24px;overflow-x:auto}
.el-flash{padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px}
.el-flash-ok{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}
.el-flash-err{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
</style>
