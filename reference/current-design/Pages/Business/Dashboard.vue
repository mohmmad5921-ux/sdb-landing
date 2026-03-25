<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    business: Object,
    accounts: Array,
    recentTransactions: Array,
    totalBalance: Number,
    stats: Object,
});

const fmt = (a, s = '€') => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + s;
const fmtK = (a) => { a = Number(a || 0); if (a >= 1000000) return (a/1000000).toFixed(1) + 'M'; if (a >= 1000) return (a/1000).toFixed(1) + 'K'; return a.toFixed(0); };

const navItems = [
    { label: 'لوحة التحكم', icon: '🏠', href: '/business/dashboard', active: true },
    { label: 'الحسابات', icon: '💰', href: '/business/dashboard' },
    { label: 'الموظفين', icon: '👥', href: '/business/dashboard' },
    { label: 'الفواتير', icon: '🧾', href: '/business/dashboard' },
    { label: 'التقارير', icon: '📊', href: '/business/dashboard' },
    { label: 'البطاقات', icon: '💳', href: '/business/dashboard' },
    { label: 'الإعدادات', icon: '⚙️', href: '/business/dashboard' },
];
</script>

<template>
<Head title="لوحة التحكم — الحساب التجاري" />
<AuthenticatedLayout>
<div class="bp-root">
    <!-- Sidebar -->
    <aside class="bp-sidebar">
        <div class="bp-logo">
            <div class="bp-logo-text">SDB</div>
            <div class="bp-logo-sub">Business</div>
        </div>
        <nav class="bp-nav">
            <Link v-for="n in navItems" :key="n.label" :href="n.href"
                  :class="['bp-nav-item', n.active ? 'bp-nav-active' : '']">
                <span>{{ n.icon }}</span>
                <span>{{ n.label }}</span>
            </Link>
        </nav>
        <div class="bp-sidebar-footer">
            <Link href="/profile" class="bp-nav-item"><span>👤</span><span>الملف الشخصي</span></Link>
            <Link :href="route('logout')" method="post" as="button" class="bp-nav-item bp-logout"><span>🚪</span><span>تسجيل الخروج</span></Link>
        </div>
    </aside>

    <!-- Main -->
    <main class="bp-main">
        <header class="bp-topbar">
            <div>
                <h1 class="bp-title">مرحباً، {{ user?.full_name?.split(' ')[0] }} 👋</h1>
                <p class="bp-date">{{ new Date().toLocaleDateString('ar-EG', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            </div>
            <div class="bp-header-badges">
                <span class="bp-badge bp-badge-green">🏢 حساب تجاري</span>
                <span v-if="business?.business_name" class="bp-badge">{{ business.business_name }}</span>
            </div>
        </header>

        <div class="bp-content">
            <!-- Stats Row -->
            <div class="bp-stats-grid">
                <div class="bp-stat">
                    <div class="bp-stat-icon" style="background:rgba(16,185,129,.12)">💰</div>
                    <div>
                        <div class="bp-stat-label">الرصيد الإجمالي</div>
                        <div class="bp-stat-value" style="color:#10b981">{{ fmt(totalBalance) }}</div>
                    </div>
                </div>
                <div class="bp-stat">
                    <div class="bp-stat-icon" style="background:rgba(99,102,241,.12)">📊</div>
                    <div>
                        <div class="bp-stat-label">الحجم الشهري</div>
                        <div class="bp-stat-value" style="color:#6366f1">{{ fmtK(stats?.monthlyVolume) }}</div>
                    </div>
                </div>
                <div class="bp-stat">
                    <div class="bp-stat-icon" style="background:rgba(245,158,11,.12)">🏦</div>
                    <div>
                        <div class="bp-stat-label">الحسابات</div>
                        <div class="bp-stat-value" style="color:#f59e0b">{{ stats?.totalAccounts || 0 }}</div>
                    </div>
                </div>
                <div class="bp-stat">
                    <div class="bp-stat-icon" style="background:rgba(139,92,246,.12)">💳</div>
                    <div>
                        <div class="bp-stat-label">البطاقات</div>
                        <div class="bp-stat-value" style="color:#8b5cf6">{{ stats?.totalCards || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Accounts -->
            <div>
                <h2 class="bp-section-title">💰 الحسابات</h2>
                <div class="bp-accounts-grid">
                    <div v-for="acc in accounts" :key="acc.id" class="bp-account-card">
                        <div class="bp-acc-top">
                            <span class="bp-acc-currency">{{ acc.currency?.symbol || '€' }}</span>
                            <span class="bp-acc-status">{{ acc.status === 'active' ? '✅ نشط' : '⏸ مغلق' }}</span>
                        </div>
                        <div class="bp-acc-balance">{{ fmt(acc.balance, acc.currency?.symbol || '€') }}</div>
                        <div class="bp-acc-iban">{{ acc.iban }}</div>
                        <div class="bp-acc-code">{{ acc.currency?.code }} · {{ acc.account_number }}</div>
                    </div>
                    <div v-if="!accounts?.length" class="bp-empty">لا توجد حسابات</div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bp-tx-section">
                <h2 class="bp-section-title">📋 آخر المعاملات</h2>
                <div class="bp-tx-list">
                    <div v-for="tx in recentTransactions" :key="tx.id" class="bp-tx-row">
                        <div class="bp-tx-left">
                            <div class="bp-tx-icon">{{ tx.type === 'transfer' ? '↗' : tx.type === 'deposit' ? '💳' : '💸' }}</div>
                            <div>
                                <div class="bp-tx-type">{{ tx.type }}</div>
                                <div class="bp-tx-date">{{ new Date(tx.created_at).toLocaleDateString('ar-EG') }}</div>
                            </div>
                        </div>
                        <div class="bp-tx-amount" :class="tx.type === 'deposit' ? 'green' : ''">{{ fmt(tx.amount) }}</div>
                    </div>
                    <div v-if="!recentTransactions?.length" class="bp-empty">لا توجد معاملات</div>
                </div>
            </div>
        </div>
    </main>
</div>
</AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap');
.bp-root{display:flex;min-height:100vh;background:#060b18;font-family:'Cairo',system-ui,sans-serif;direction:rtl;color:#fff}
.bp-sidebar{width:220px;background:#0a0f1f;border-left:1px solid rgba(255,255,255,.05);flex-shrink:0;display:flex;flex-direction:column}
.bp-logo{padding:20px 16px;border-bottom:1px solid rgba(255,255,255,.05);text-align:center}
.bp-logo-text{font-family:'Inter',sans-serif;font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px}
.bp-logo-sub{font-size:10px;color:#10b981;font-weight:700;letter-spacing:2px;text-transform:uppercase}
.bp-nav{padding:12px 8px;flex:1;display:flex;flex-direction:column;gap:2px}
.bp-nav-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;font-size:13px;color:rgba(255,255,255,.4);text-decoration:none;font-weight:600;transition:all .2s;border:none;background:none;cursor:pointer;width:100%;text-align:right}
.bp-nav-item:hover{background:rgba(255,255,255,.04);color:rgba(255,255,255,.7)}
.bp-nav-active{background:rgba(16,185,129,.1)!important;color:#10b981!important;font-weight:700}
.bp-sidebar-footer{padding:8px;border-top:1px solid rgba(255,255,255,.05)}
.bp-logout{color:rgba(239,68,68,.6)!important}.bp-logout:hover{color:#ef4444!important;background:rgba(239,68,68,.08)!important}
.bp-main{flex:1;display:flex;flex-direction:column;overflow-y:auto}
.bp-topbar{display:flex;justify-content:space-between;align-items:center;padding:16px 28px;border-bottom:1px solid rgba(255,255,255,.05)}
.bp-title{font-size:18px;font-weight:800;color:#fff}
.bp-date{font-size:11px;color:rgba(255,255,255,.3);margin-top:2px}
.bp-header-badges{display:flex;gap:8px}
.bp-badge{font-size:11px;padding:5px 12px;border-radius:10px;background:rgba(255,255,255,.05);color:rgba(255,255,255,.5);font-weight:600}
.bp-badge-green{background:rgba(16,185,129,.1);color:#10b981}
.bp-content{padding:24px 28px;display:flex;flex-direction:column;gap:24px}
.bp-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.bp-stat{display:flex;align-items:center;gap:14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:16px;padding:18px 16px}
.bp-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.bp-stat-label{font-size:11px;color:rgba(255,255,255,.35);font-weight:600}
.bp-stat-value{font-size:22px;font-weight:900;margin-top:2px}
.bp-section-title{font-size:14px;font-weight:800;color:#fff;margin-bottom:12px}
.bp-accounts-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.bp-account-card{background:linear-gradient(135deg,rgba(255,255,255,.03),rgba(255,255,255,.01));border:1px solid rgba(255,255,255,.06);border-radius:16px;padding:18px;transition:all .2s}
.bp-account-card:hover{border-color:rgba(16,185,129,.2)}
.bp-acc-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.bp-acc-currency{font-size:24px}
.bp-acc-status{font-size:10px;color:#10b981;font-weight:600}
.bp-acc-balance{font-size:20px;font-weight:900;color:#fff;margin-bottom:4px}
.bp-acc-iban{font-size:10px;color:rgba(255,255,255,.25);font-family:monospace;margin-bottom:2px}
.bp-acc-code{font-size:10px;color:rgba(255,255,255,.15)}
.bp-tx-section{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:16px;overflow:hidden}
.bp-tx-list{max-height:400px;overflow-y:auto}
.bp-tx-row{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.03);transition:background .2s}
.bp-tx-row:hover{background:rgba(255,255,255,.02)}
.bp-tx-left{display:flex;align-items:center;gap:12px}
.bp-tx-icon{width:36px;height:36px;background:rgba(255,255,255,.05);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px}
.bp-tx-type{font-size:13px;font-weight:700;color:#fff}
.bp-tx-date{font-size:10px;color:rgba(255,255,255,.25)}
.bp-tx-amount{font-size:14px;font-weight:800;color:#fff}
.bp-tx-amount.green{color:#10b981}
.bp-empty{padding:40px;text-align:center;color:rgba(255,255,255,.2);font-size:14px}
@media(max-width:900px){.bp-sidebar{display:none}.bp-stats-grid{grid-template-columns:repeat(2,1fr)}.bp-accounts-grid{grid-template-columns:1fr}}
</style>
