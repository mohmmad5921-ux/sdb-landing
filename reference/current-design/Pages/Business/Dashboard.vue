<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const stats = computed(() => page.props.stats || {});
const accounts = computed(() => page.props.accounts || []);
const recentTx = computed(() => page.props.recentTransactions || []);

// Currency switcher
const currencies = computed(() => {
    const seen = {};
    (accounts.value || []).forEach(a => {
        const code = a.currency?.code || 'USD';
        if (!seen[code]) seen[code] = { code, symbol: a.currency?.symbol || '$' };
    });
    return Object.values(seen);
});
const activeCurrency = ref(null);
const currentCurrency = computed(() => activeCurrency.value || currencies.value[0]?.code || 'USD');
const currentSymbol = computed(() => currencies.value.find(c => c.code === currentCurrency.value)?.symbol || '$');

const filteredAccounts = computed(() => {
    if (!currentCurrency.value) return accounts.value;
    return (accounts.value || []).filter(a => (a.currency?.code || 'USD') === currentCurrency.value);
});

const totalBalance = computed(() => filteredAccounts.value.reduce((s, a) => s + Number(a.balance || 0), 0));

const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
</script>

<template>
<BusinessLayout title="لوحة التحكم">
    <div class="db-grid">
        <!-- Currency Switcher -->
        <div class="db-currency-bar" v-if="currencies.length > 1">
            <button v-for="c in currencies" :key="c.code"
                :class="['db-cur-btn', currentCurrency === c.code && 'db-cur-active']"
                @click="activeCurrency = c.code">
                {{ c.symbol }} {{ c.code }}
            </button>
        </div>

        <!-- KPI Stats -->
        <div class="db-stats">
            <div class="db-stat">
                <div class="db-stat-icon db-green">💰</div>
                <div>
                    <div class="db-stat-label">الرصيد الإجمالي</div>
                    <div class="db-stat-value">{{ currentSymbol }}{{ fmt(totalBalance) }}</div>
                    <div class="db-stat-sub">{{ filteredAccounts.length }} حساب · {{ currentCurrency }}</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat-icon db-blue">📈</div>
                <div>
                    <div class="db-stat-label">الوارد هذا الشهر</div>
                    <div class="db-stat-value db-green-text">+{{ fmt(stats.monthlyIn) }}</div>
                    <div class="db-stat-sub">📬 واردات</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat-icon db-red">📉</div>
                <div>
                    <div class="db-stat-label">المنفق هذا الشهر</div>
                    <div class="db-stat-value db-red-text">-{{ fmt(stats.monthlyOut) }}</div>
                    <div class="db-stat-sub">📤 مصروفات</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat-icon db-purple">🧾</div>
                <div>
                    <div class="db-stat-label">معاملات معلقة</div>
                    <div class="db-stat-value">{{ stats.pendingCount || 0 }}</div>
                    <div class="db-stat-sub">✅ {{ stats.completedCount || 0 }} مكتمل</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="db-actions">
            <Link href="/business/invoices" class="db-action"><span>🧾</span> إنشاء فاتورة</Link>
            <Link href="/business/pos" class="db-action"><span>🏪</span> نقطة بيع</Link>
            <Link href="/business/installments" class="db-action"><span>📋</span> عقد ائتمان</Link>
            <Link href="/business/payroll" class="db-action"><span>💵</span> دورة رواتب</Link>
            <Link href="/business/customers" class="db-action"><span>👥</span> إضافة عميل</Link>
        </div>

        <div class="db-bottom">
            <!-- Accounts -->
            <div class="db-section">
                <div class="db-section-header">
                    <h3>💰 حساباتي</h3>
                    <Link href="/business/accounts" class="db-link">عرض الكل ←</Link>
                </div>
                <div class="db-accounts">
                    <div v-for="acc in accounts" :key="acc.id" class="db-acc" :class="{ 'db-acc-dim': activeCurrency && (acc.currency?.code || 'USD') !== currentCurrency }">
                        <div class="db-acc-top">
                            <span class="db-acc-currency">{{ acc.currency?.code || 'USD' }}</span>
                            <span :class="['db-acc-status', acc.status === 'active' ? 'active' : '']">{{ acc.status === 'active' ? 'نشط' : acc.status }}</span>
                        </div>
                        <div class="db-acc-balance">{{ acc.currency?.symbol || '$' }} {{ fmt(acc.balance) }}</div>
                        <div class="db-acc-num">🏦 {{ acc.account_number }}</div>
                    </div>
                    <div v-if="!accounts.length" class="db-empty">لا توجد حسابات</div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="db-section">
                <div class="db-section-header">
                    <h3>📋 آخر المعاملات</h3>
                    <Link href="/business/transactions" class="db-link">عرض الكل ←</Link>
                </div>
                <div class="db-tx-list">
                    <div v-for="tx in recentTx" :key="tx.id" class="db-tx">
                        <div :class="['db-tx-icon', tx.type === 'credit' ? 'db-tx-in' : 'db-tx-out']">
                            {{ tx.type === 'credit' ? '↓' : '↑' }}
                        </div>
                        <div class="db-tx-info">
                            <div class="db-tx-desc">{{ tx.description || tx.type }}</div>
                            <div class="db-tx-date">{{ fmtDate(tx.created_at) }}</div>
                        </div>
                        <div :class="['db-tx-amount', tx.type === 'credit' ? 'db-green-text' : 'db-red-text']">
                            {{ tx.type === 'credit' ? '+' : '-' }}{{ fmt(tx.amount) }}
                        </div>
                    </div>
                    <div v-if="!recentTx.length" class="db-empty">لا توجد معاملات بعد</div>
                </div>
            </div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.db-grid { display: flex; flex-direction: column; gap: 24px; }

/* Currency Switcher */
.db-currency-bar { display: flex; gap: 8px; }
.db-cur-btn { padding: 8px 20px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; font-size: 13px; font-weight: 700; color: #64748b; cursor: pointer; font-family: 'Cairo'; transition: all .2s; }
.db-cur-btn:hover { border-color: #106F24; color: #106F24; }
.db-cur-active { background: #106F24 !important; color: #fff !important; border-color: #106F24 !important; }

/* Stats */
.db-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.db-stat { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 16px; transition: box-shadow .2s; }
.db-stat:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.db-stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.db-green { background: #ecfdf5; }
.db-blue { background: #eff6ff; }
.db-red { background: #fef2f2; }
.db-purple { background: #f5f3ff; }
.db-stat-label { font-size: 12px; color: #9ca3af; font-weight: 600; }
.db-stat-value { font-size: 24px; font-weight: 900; color: #1a1a2e; margin-top: 2px; }
.db-stat-sub { font-size: 11px; color: #9ca3af; margin-top: 2px; }
.db-green-text { color: #059669 !important; }
.db-red-text { color: #dc2626 !important; }

/* Actions */
.db-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.db-action { display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: #fff; border: 1px solid #e8ecf1; border-radius: 12px; font-size: 13px; font-weight: 700; color: #1a1a2e; text-decoration: none; transition: all .2s; }
.db-action:hover { border-color: #106F24; color: #106F24; box-shadow: 0 2px 8px rgba(16,111,36,.08); }

/* Bottom */
.db-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.db-section { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; }
.db-section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.db-section-header h3 { font-size: 15px; font-weight: 800; color: #1a1a2e; }
.db-link { font-size: 12px; color: #106F24; font-weight: 700; text-decoration: none; }

/* Accounts */
.db-accounts { display: flex; flex-direction: column; gap: 10px; }
.db-acc { padding: 16px; background: #f8f9fb; border-radius: 12px; border: 1px solid #e8ecf1; transition: opacity .2s; }
.db-acc-dim { opacity: .4; }
.db-acc-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.db-acc-currency { font-size: 14px; font-weight: 800; color: #1a1a2e; background: #ecfdf5; padding: 2px 10px; border-radius: 6px; }
.db-acc-status { font-size: 10px; color: #9ca3af; font-weight: 600; }
.db-acc-status.active { color: #059669; }
.db-acc-balance { font-size: 22px; font-weight: 900; color: #1a1a2e; }
.db-acc-num { font-size: 11px; color: #9ca3af; margin-top: 4px; }

/* Transactions */
.db-tx-list { display: flex; flex-direction: column; }
.db-tx { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
.db-tx:last-child { border-bottom: none; }
.db-tx-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; flex-shrink: 0; }
.db-tx-in { background: #ecfdf5; color: #059669; }
.db-tx-out { background: #fef2f2; color: #dc2626; }
.db-tx-info { flex: 1; }
.db-tx-desc { font-size: 13px; font-weight: 700; color: #1a1a2e; }
.db-tx-date { font-size: 11px; color: #9ca3af; }
.db-tx-amount { font-size: 14px; font-weight: 800; }

.db-empty { text-align: center; padding: 30px; color: #9ca3af; font-size: 13px; }

@media (max-width: 900px) {
    .db-stats { grid-template-columns: repeat(2, 1fr); }
    .db-bottom { grid-template-columns: 1fr; }
}
</style>
