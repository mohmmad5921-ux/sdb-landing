<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    agent: Object,
    remittances: Array,
    stats: Object,
});

const fmt = (a, s = '€') => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + s;
const fmtK = (a) => { a = Number(a || 0); if (a >= 1000000) return (a/1000000).toFixed(1) + 'M'; if (a >= 1000) return (a/1000).toFixed(1) + 'K'; return a.toFixed(0); };

const statusLabels = { pending: 'معلق', ready: 'جاهز', collected: 'مستلم', expired: 'منتهي', cancelled: 'ملغي' };
const statusColors = { pending: '#f59e0b', ready: '#10b981', collected: '#6366f1', expired: '#6b7280', cancelled: '#ef4444' };

const navItems = [
    { label: 'لوحة التحكم', icon: '🏠', href: '/agent/dashboard', active: true },
    { label: 'الحوالات', icon: '💸', href: '/agent/dashboard' },
    { label: 'الفروع', icon: '🏪', href: '/agent/dashboard' },
    { label: 'الموظفين', icon: '👥', href: '/agent/dashboard' },
    { label: 'التقارير', icon: '📊', href: '/agent/dashboard' },
    { label: 'الحسابات', icon: '💰', href: '/agent/dashboard' },
    { label: 'الإعدادات', icon: '⚙️', href: '/agent/dashboard' },
];
</script>

<template>
<Head title="لوحة التحكم — وكيل الحوالات" />
<AuthenticatedLayout>
<div class="ap-root">
    <!-- Sidebar -->
    <aside class="ap-sidebar">
        <div class="ap-logo">
            <div class="ap-logo-text">SDB</div>
            <div class="ap-logo-sub">Agent Portal</div>
        </div>
        <nav class="ap-nav">
            <Link v-for="n in navItems" :key="n.label" :href="n.href"
                  :class="['ap-nav-item', n.active ? 'ap-nav-active' : '']">
                <span>{{ n.icon }}</span>
                <span>{{ n.label }}</span>
            </Link>
        </nav>
        <div class="ap-sidebar-footer">
            <Link href="/profile" class="ap-nav-item"><span>👤</span><span>الملف الشخصي</span></Link>
            <Link :href="route('logout')" method="post" as="button" class="ap-nav-item ap-logout"><span>🚪</span><span>تسجيل الخروج</span></Link>
        </div>
    </aside>

    <!-- Main -->
    <main class="ap-main">
        <header class="ap-topbar">
            <div>
                <h1 class="ap-title">مرحباً، {{ user?.full_name?.split(' ')[0] }} 👋</h1>
                <p class="ap-date">{{ new Date().toLocaleDateString('ar-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            </div>
            <div class="ap-header-badges">
                <span class="ap-badge ap-badge-purple">🏦 وكيل حوالات</span>
                <span v-if="agent?.name_ar" class="ap-badge">{{ agent.name_ar }}</span>
            </div>
        </header>

        <div class="ap-content">
            <!-- Stats -->
            <div class="ap-stats-grid">
                <div class="ap-stat">
                    <div class="ap-stat-icon" style="background:rgba(16,185,129,.12)">💸</div>
                    <div>
                        <div class="ap-stat-label">إجمالي الحوالات</div>
                        <div class="ap-stat-value" style="color:#10b981">{{ stats?.totalRemittances || 0 }}</div>
                    </div>
                </div>
                <div class="ap-stat">
                    <div class="ap-stat-icon" style="background:rgba(245,158,11,.12)">⏳</div>
                    <div>
                        <div class="ap-stat-label">حوالات معلقة</div>
                        <div class="ap-stat-value" style="color:#f59e0b">{{ stats?.pendingRemittances || 0 }}</div>
                    </div>
                </div>
                <div class="ap-stat">
                    <div class="ap-stat-icon" style="background:rgba(139,92,246,.12)">🏪</div>
                    <div>
                        <div class="ap-stat-label">الفروع</div>
                        <div class="ap-stat-value" style="color:#8b5cf6">{{ stats?.totalBranches || 0 }}</div>
                    </div>
                </div>
                <div class="ap-stat">
                    <div class="ap-stat-icon" style="background:rgba(99,102,241,.12)">📊</div>
                    <div>
                        <div class="ap-stat-label">الحجم الشهري</div>
                        <div class="ap-stat-value" style="color:#6366f1">{{ fmtK(stats?.monthlyVolume) }}</div>
                    </div>
                </div>
            </div>

            <!-- Agent Info -->
            <div v-if="agent" class="ap-agent-card">
                <div class="ap-agent-top">
                    <div class="ap-agent-avatar">🏦</div>
                    <div>
                        <div class="ap-agent-name">{{ agent.name_ar }}</div>
                        <div class="ap-agent-loc">{{ agent.district?.name_ar || '' }} · {{ agent.phone }}</div>
                    </div>
                    <span class="ap-agent-status">{{ agent.is_active ? '✅ نشط' : '⏸ غير نشط' }}</span>
                </div>

                <!-- Branches -->
                <div v-if="agent.branches?.length" class="ap-branches">
                    <h3 class="ap-sub-title">🏪 الفروع</h3>
                    <div class="ap-branches-grid">
                        <div v-for="b in agent.branches" :key="b.id" class="ap-branch-card">
                            <div class="ap-branch-name">{{ b.name_ar }}</div>
                            <div class="ap-branch-meta">{{ b.manager_name || '—' }} · {{ b.phone || '—' }}</div>
                            <span :class="b.is_active ? 'ap-b-active' : 'ap-b-inactive'">{{ b.is_active ? 'نشط' : 'مغلق' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Remittances -->
            <div class="ap-tx-section">
                <h2 class="ap-section-title">💸 آخر الحوالات</h2>
                <div class="ap-tx-list">
                    <div v-for="r in remittances" :key="r.id" class="ap-tx-row">
                        <div class="ap-tx-left">
                            <div class="ap-tx-dot" :style="{ background: statusColors[r.status] || '#6b7280' }"></div>
                            <div>
                                <div class="ap-tx-name">{{ r.recipient_name }}</div>
                                <div class="ap-tx-phone">{{ r.recipient_phone }} · {{ r.notification_code }}</div>
                            </div>
                        </div>
                        <div class="ap-tx-right">
                            <div class="ap-tx-amount">{{ fmt(r.amount) }}</div>
                            <div class="ap-tx-status" :style="{ color: statusColors[r.status] }">{{ statusLabels[r.status] || r.status }}</div>
                        </div>
                    </div>
                    <div v-if="!remittances?.length" class="ap-empty">لا توجد حوالات</div>
                </div>
            </div>
        </div>
    </main>
</div>
</AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap');
.ap-root{display:flex;min-height:100vh;background:#060b18;font-family:'Cairo',system-ui,sans-serif;direction:rtl;color:#fff}
.ap-sidebar{width:220px;background:#0a0f1f;border-left:1px solid rgba(255,255,255,.05);flex-shrink:0;display:flex;flex-direction:column}
.ap-logo{padding:20px 16px;border-bottom:1px solid rgba(255,255,255,.05);text-align:center}
.ap-logo-text{font-family:'Inter',sans-serif;font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px}
.ap-logo-sub{font-size:10px;color:#8b5cf6;font-weight:700;letter-spacing:2px;text-transform:uppercase}
.ap-nav{padding:12px 8px;flex:1;display:flex;flex-direction:column;gap:2px}
.ap-nav-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;font-size:13px;color:rgba(255,255,255,.4);text-decoration:none;font-weight:600;transition:all .2s;border:none;background:none;cursor:pointer;width:100%;text-align:right}
.ap-nav-item:hover{background:rgba(255,255,255,.04);color:rgba(255,255,255,.7)}
.ap-nav-active{background:rgba(139,92,246,.1)!important;color:#8b5cf6!important;font-weight:700}
.ap-sidebar-footer{padding:8px;border-top:1px solid rgba(255,255,255,.05)}
.ap-logout{color:rgba(239,68,68,.6)!important}.ap-logout:hover{color:#ef4444!important;background:rgba(239,68,68,.08)!important}
.ap-main{flex:1;display:flex;flex-direction:column;overflow-y:auto}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;padding:16px 28px;border-bottom:1px solid rgba(255,255,255,.05)}
.ap-title{font-size:18px;font-weight:800;color:#fff}
.ap-date{font-size:11px;color:rgba(255,255,255,.3);margin-top:2px}
.ap-header-badges{display:flex;gap:8px}
.ap-badge{font-size:11px;padding:5px 12px;border-radius:10px;background:rgba(255,255,255,.05);color:rgba(255,255,255,.5);font-weight:600}
.ap-badge-purple{background:rgba(139,92,246,.1);color:#8b5cf6}
.ap-content{padding:24px 28px;display:flex;flex-direction:column;gap:24px}
.ap-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.ap-stat{display:flex;align-items:center;gap:14px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:16px;padding:18px 16px}
.ap-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.ap-stat-label{font-size:11px;color:rgba(255,255,255,.35);font-weight:600}
.ap-stat-value{font-size:22px;font-weight:900;margin-top:2px}
.ap-section-title{font-size:14px;font-weight:800;color:#fff;margin-bottom:12px}
.ap-agent-card{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:16px;padding:20px}
.ap-agent-top{display:flex;align-items:center;gap:14px}
.ap-agent-avatar{width:48px;height:48px;background:rgba(139,92,246,.12);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.ap-agent-name{font-size:16px;font-weight:800;color:#fff}
.ap-agent-loc{font-size:11px;color:rgba(255,255,255,.3)}
.ap-agent-status{margin-right:auto;font-size:11px;color:#10b981;font-weight:600}
.ap-sub-title{font-size:12px;font-weight:700;color:rgba(255,255,255,.5);margin:16px 0 10px;padding-top:14px;border-top:1px solid rgba(255,255,255,.05)}
.ap-branches-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.ap-branch-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:14px}
.ap-branch-name{font-size:13px;font-weight:700;color:#fff;margin-bottom:3px}
.ap-branch-meta{font-size:10px;color:rgba(255,255,255,.25);margin-bottom:6px}
.ap-b-active{font-size:10px;color:#10b981;font-weight:600}.ap-b-inactive{font-size:10px;color:#ef4444;font-weight:600}
.ap-tx-section{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:16px;overflow:hidden}
.ap-tx-list{max-height:400px;overflow-y:auto}
.ap-tx-row{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.03);transition:background .2s}
.ap-tx-row:hover{background:rgba(255,255,255,.02)}
.ap-tx-left{display:flex;align-items:center;gap:12px}
.ap-tx-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.ap-tx-name{font-size:13px;font-weight:700;color:#fff}
.ap-tx-phone{font-size:10px;color:rgba(255,255,255,.25);font-family:monospace}
.ap-tx-right{text-align:left}
.ap-tx-amount{font-size:14px;font-weight:800;color:#fff}
.ap-tx-status{font-size:10px;font-weight:600}
.ap-empty{padding:40px;text-align:center;color:rgba(255,255,255,.2);font-size:14px}
@media(max-width:900px){.ap-sidebar{display:none}.ap-stats-grid{grid-template-columns:repeat(2,1fr)}.ap-branches-grid{grid-template-columns:1fr}}
</style>
