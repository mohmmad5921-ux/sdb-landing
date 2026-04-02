<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
const props = defineProps({ title: { type: String, default: 'لوحة التحكم' } });
const page = usePage();
const user = computed(() => page.props.auth?.user);
const business = computed(() => page.props.business || {});
const isDemo = computed(() => business.value?.status === 'pending' || business.value?.status === 'demo');
const sidebarOpen = ref(true);
const currentPath = computed(() => window.location.pathname);

const sections = [
    { label: 'لوحة التحكم', icon: '📊', href: '/business/dashboard', key: 'dashboard' },
    { label: 'الحسابات', icon: '💰', href: '/business/accounts', key: 'accounts' },
    { label: 'العمليات', icon: '📋', href: '/business/transactions', key: 'transactions' },
    { divider: true, label: 'الأعمال' },
    { label: 'الفواتير', icon: '🧾', href: '/business/invoices', key: 'invoices' },
    { label: 'عقود الائتمان', icon: '📋', href: '/business/installments', key: 'installments' },
    { label: 'نقطة البيع', icon: '🏪', href: '/business/pos', key: 'pos' },
    { label: 'العملاء', icon: '👥', href: '/business/customers', key: 'customers' },
    { divider: true, label: 'الإدارة' },
    { label: 'الموظفين', icon: '👨‍💼', href: '/business/staff', key: 'staff' },
    { label: 'الرواتب', icon: '💵', href: '/business/payroll', key: 'payroll' },
    { label: 'API & المطورين', icon: '🔌', href: '/business/api', key: 'api' },
    { divider: true, label: 'التحليلات' },
    { label: 'التقارير', icon: '📈', href: '/business/reports', key: 'reports' },
    { label: 'التصنيف الائتماني', icon: '📊', href: '/business/credit-rating', key: 'credit' },
    { label: 'الأمان', icon: '🛡️', href: '/business/security', key: 'security' },
    { label: 'الإعدادات', icon: '⚙️', href: '/business/settings', key: 'settings' },
];

const isActive = (href) => currentPath.value === href || currentPath.value.startsWith(href + '/');
const logout = () => router.post(route('logout'));

const now = ref(new Date());
onMounted(() => setInterval(() => now.value = new Date(), 60000));
const dateStr = computed(() => now.value.toLocaleDateString('ar-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
</script>

<template>
<Head :title="`${title} — ${business?.business_name || 'SDB Business'}`" />
<div class="bl-root">
    <!-- Demo Banner -->
    <div v-if="isDemo" class="bl-demo-banner">
        <span>⚠️</span>
        <span>حسابك في وضع تجريبي — يرجى إكمال التسجيل والانتظار لحين الموافقة</span>
    </div>

    <!-- Sidebar -->
    <aside :class="['bl-sidebar', !sidebarOpen && 'bl-collapsed']">
        <div class="bl-brand">
            <div class="bl-brand-logo">
                <div class="bl-logo-circle">
                    <span v-if="business?.logo_url">
                        <img :src="business.logo_url" class="bl-logo-img" />
                    </span>
                    <span v-else class="bl-logo-letter">{{ (business?.business_name || 'B')[0] }}</span>
                </div>
                <div v-if="sidebarOpen" class="bl-brand-text">
                    <div class="bl-brand-name">{{ business?.business_name || 'SDB Business' }}</div>
                    <div class="bl-brand-sub">NetBank</div>
                </div>
            </div>
            <button class="bl-toggle" @click="sidebarOpen = !sidebarOpen">
                {{ sidebarOpen ? '◁' : '▷' }}
            </button>
        </div>

        <nav class="bl-nav">
            <template v-for="item in sections" :key="item.key || item.label">
                <div v-if="item.divider" class="bl-nav-divider">
                    <span v-if="sidebarOpen">{{ item.label }}</span>
                </div>
                <Link v-else :href="item.href"
                      :class="['bl-nav-item', isActive(item.href) && 'bl-nav-active']">
                    <span class="bl-nav-icon">{{ item.icon }}</span>
                    <span v-if="sidebarOpen" class="bl-nav-label">{{ item.label }}</span>
                </Link>
            </template>
        </nav>

        <div class="bl-sidebar-footer">
            <Link href="/business/settings" class="bl-nav-item">
                <span class="bl-nav-icon">👤</span>
                <span v-if="sidebarOpen" class="bl-nav-label">{{ user?.full_name?.split(' ')[0] }}</span>
            </Link>
            <button @click="logout" class="bl-nav-item bl-logout">
                <span class="bl-nav-icon">🚪</span>
                <span v-if="sidebarOpen" class="bl-nav-label">خروج</span>
            </button>
        </div>
    </aside>

    <!-- Main -->
    <main class="bl-main">
        <header class="bl-topbar">
            <div class="bl-topbar-right">
                <h1 class="bl-page-title">{{ title }}</h1>
                <p class="bl-page-date">{{ dateStr }}</p>
            </div>
            <div class="bl-topbar-left">
                <div v-if="business?.status === 'active'" class="bl-status-badge bl-status-active">✅ نشط</div>
                <div v-else-if="isDemo" class="bl-status-badge bl-status-demo">🔒 تجريبي</div>
                <div v-else class="bl-status-badge bl-status-pending">⏳ قيد المراجعة</div>
            </div>
        </header>

        <div class="bl-content">
            <slot />
        </div>
    </main>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap');

.bl-root {
    display: flex; min-height: 100vh;
    background: #f8f9fb;
    font-family: 'Cairo', system-ui, sans-serif;
    direction: rtl; color: #1a1a2e;
}

/* Demo Banner */
.bl-demo-banner { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: linear-gradient(90deg, #f59e0b, #ef4444); color: #fff; font-size: 12px; font-weight: 700; padding: 8px 20px; display: flex; align-items: center; gap: 8px; justify-content: center; }
.bl-root:has(.bl-demo-banner) { padding-top: 36px; }

/* Sidebar */
.bl-sidebar {
    width: 250px; background: #fff;
    border-left: 1px solid #e8ecf1;
    display: flex; flex-direction: column; flex-shrink: 0;
    transition: width .3s ease; overflow: hidden;
    box-shadow: -2px 0 12px rgba(0,0,0,.03);
}
.bl-collapsed { width: 68px; }

.bl-brand { padding: 18px 14px; border-bottom: 1px solid #e8ecf1; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.bl-brand-logo { display: flex; align-items: center; gap: 10px; overflow: hidden; }
.bl-logo-circle { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #106F24, #1a9d3a); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.bl-logo-img { width: 100%; height: 100%; object-fit: cover; }
.bl-logo-letter { font-size: 18px; font-weight: 900; color: #fff; }
.bl-brand-name { font-size: 14px; font-weight: 800; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
.bl-brand-sub { font-size: 10px; color: #106F24; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
.bl-toggle { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 12px; padding: 4px 6px; border-radius: 6px; transition: all .2s; }
.bl-toggle:hover { color: #1a1a2e; background: #f3f4f6; }

/* Nav */
.bl-nav { flex: 1; padding: 12px 10px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
.bl-nav-divider { padding: 16px 14px 6px; font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.5px; }
.bl-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; font-size: 13px; color: #64748b; text-decoration: none; font-weight: 600; transition: all .2s; border: none; background: none; cursor: pointer; width: 100%; text-align: right; white-space: nowrap; }
.bl-nav-item:hover { background: #f1f5f9; color: #1a1a2e; }
.bl-nav-active { background: #ecfdf5 !important; color: #106F24 !important; font-weight: 700; }
.bl-nav-icon { font-size: 16px; width: 22px; text-align: center; flex-shrink: 0; }
.bl-nav-label { overflow: hidden; text-overflow: ellipsis; }
.bl-sidebar-footer { padding: 10px; border-top: 1px solid #e8ecf1; }
.bl-logout { color: #ef4444 !important; }
.bl-logout:hover { background: #fef2f2 !important; }

/* Main */
.bl-main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; min-width: 0; }
.bl-topbar {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 28px; border-bottom: 1px solid #e8ecf1;
    background: #fff; position: sticky; top: 0; z-index: 10;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.bl-page-title { font-size: 18px; font-weight: 800; color: #1a1a2e; }
.bl-page-date { font-size: 11px; color: #9ca3af; margin-top: 2px; }
.bl-topbar-left { display: flex; align-items: center; gap: 10px; }
.bl-status-badge { font-size: 11px; padding: 5px 14px; border-radius: 10px; font-weight: 700; }
.bl-status-active { background: #ecfdf5; color: #059669; }
.bl-status-demo { background: #fffbeb; color: #d97706; }
.bl-status-pending { background: #eef2ff; color: #4f46e5; }

/* Content */
.bl-content { padding: 24px 28px; flex: 1; }

/* Scrollbar */
.bl-nav::-webkit-scrollbar { width: 3px; }
.bl-nav::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }

/* Mobile */
@media (max-width: 900px) {
    .bl-sidebar { position: fixed; z-index: 50; height: 100vh; transform: translateX(100%); }
    .bl-sidebar:not(.bl-collapsed) { transform: translateX(0); box-shadow: -4px 0 20px rgba(0,0,0,.1); }
    .bl-collapsed { transform: translateX(100%); }
}
</style>
