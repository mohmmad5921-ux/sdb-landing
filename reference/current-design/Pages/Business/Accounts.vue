<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
const props = defineProps({ accounts: Array });
const fmt = (a, s = '$') => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + s;
</script>

<template>
<BusinessLayout title="الحسابات">
    <div class="ba-grid">
        <!-- Summary -->
        <div class="ba-summary">
            <div class="ba-sum-card ba-primary">
                <div class="ba-sum-label">إجمالي الأرصدة</div>
                <div class="ba-sum-value">{{ fmt(accounts?.reduce((s, a) => s + Number(a.balance || 0), 0)) }}</div>
            </div>
            <div class="ba-sum-card">
                <div class="ba-sum-label">حسابات نشطة</div>
                <div class="ba-sum-value">{{ accounts?.filter(a => a.status === 'active').length || 0 }}</div>
            </div>
            <div class="ba-sum-card">
                <div class="ba-sum-label">عملات</div>
                <div class="ba-sum-value">{{ [...new Set(accounts?.map(a => a.currency?.code))].length || 0 }}</div>
            </div>
        </div>

        <!-- Account Cards -->
        <div class="ba-accounts">
            <div v-for="acc in accounts" :key="acc.id" class="ba-card">
                <div class="ba-card-header">
                    <div class="ba-card-symbol">{{ acc.currency?.symbol || '$' }}</div>
                    <div :class="['ba-card-status', acc.status === 'active' ? 'green' : 'red']">
                        {{ acc.status === 'active' ? '● نشط' : '● مغلق' }}
                    </div>
                </div>
                <div class="ba-card-bal">{{ fmt(acc.balance, acc.currency?.symbol || '$') }}</div>
                <div class="ba-card-row">
                    <span class="ba-label">متاح</span>
                    <span>{{ fmt(acc.available_balance ?? acc.balance, acc.currency?.symbol || '$') }}</span>
                </div>
                <div class="ba-card-row">
                    <span class="ba-label">معلق</span>
                    <span>{{ fmt(acc.held_balance, acc.currency?.symbol || '$') }}</span>
                </div>
                <div class="ba-card-divider"></div>
                <div class="ba-card-detail"><span class="ba-label">الرقم</span> <span class="mono">{{ acc.account_number }}</span></div>
                <div class="ba-card-detail"><span class="ba-label">IBAN</span> <span class="mono">قريباً</span></div>
                <div class="ba-card-detail"><span class="ba-label">العملة</span> <span>{{ acc.currency?.code }} — {{ acc.currency?.name }}</span></div>
                <div v-if="acc.business_code" class="ba-card-detail"><span class="ba-label">كود تجاري</span> <span class="mono">{{ acc.business_code }}</span></div>
                <div class="ba-card-limits">
                    <div v-if="acc.daily_transfer_limit"><span class="ba-label">حد يومي</span> {{ fmt(acc.daily_transfer_limit) }}</div>
                    <div v-if="acc.monthly_transfer_limit"><span class="ba-label">حد شهري</span> {{ fmt(acc.monthly_transfer_limit) }}</div>
                </div>
            </div>
        </div>

        <div v-if="!accounts?.length" class="ba-empty">
            <div class="ba-empty-icon">💰</div>
            <div>لا توجد حسابات مصرفية</div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.ba-grid { display: flex; flex-direction: column; gap: 20px; }
.ba-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.ba-sum-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 18px; }
.ba-primary { background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(139,92,246,.04)); border-color: rgba(99,102,241,.15); }
.ba-sum-label { font-size: 11px; color: #6b7280; font-weight: 600; }
.ba-sum-value { font-size: 24px; font-weight: 900; margin-top: 4px; }
.ba-accounts { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.ba-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; transition: all .2s; }
.ba-card:hover { border-color: rgba(99,102,241,.2); }
.ba-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.ba-card-symbol { font-size: 28px; }
.ba-card-status { font-size: 11px; font-weight: 700; }
.ba-card-status.green { color: #10b981; }
.ba-card-status.red { color: #ef4444; }
.ba-card-bal { font-size: 26px; font-weight: 900; margin-bottom: 12px; }
.ba-card-row { display: flex; justify-content: space-between; font-size: 12px; color: rgba(255,255,255,.6); margin-bottom: 4px; }
.ba-card-divider { height: 1px; background: #e8ecf1; margin: 12px 0; }
.ba-card-detail { font-size: 11px; color: #6b7280; margin-bottom: 4px; display: flex; justify-content: space-between; }
.ba-card-limits { display: flex; gap: 16px; margin-top: 10px; font-size: 10px; color: #6b7280; }
.ba-label { color: #9ca3af; }
.mono { font-family: 'SF Mono', monospace; letter-spacing: .5px; }
.ba-empty { text-align: center; padding: 60px; color: #9ca3af; }
.ba-empty-icon { font-size: 48px; margin-bottom: 12px; }
@media (max-width: 900px) { .ba-accounts { grid-template-columns: 1fr; } .ba-summary { grid-template-columns: 1fr; } }
</style>
