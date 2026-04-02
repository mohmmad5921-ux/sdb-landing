<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
const props = defineProps({ employees: Array });
const roleLabel = (r) => ({ owner: 'مالك', admin: 'مدير', manager: 'مدير تنفيذي', accountant: 'محاسب', cashier: 'كاشير', support: 'دعم', employee: 'موظف' }[r] || r);
const roleColor = (r) => ({ owner: '#f59e0b', admin: '#ef4444', manager: '#6366f1', accountant: '#10b981' }[r] || '#6b7280');
const permLabel = (p) => ({ '*': 'كل الصلاحيات', view_balance: 'عرض الرصيد', make_transfers: 'تحويلات', manage_invoices: 'فواتير', manage_staff: 'موظفين', view_reports: 'تقارير' }[p] || p);
</script>

<template>
<BusinessLayout title="الموظفين">
    <div class="sf-grid">
        <div class="sf-header"><h2>👨‍💼 فريق العمل ({{ employees?.length || 0 }})</h2></div>

        <div class="sf-list">
            <div v-for="emp in employees" :key="emp.id" class="sf-card">
                <div class="sf-avatar">{{ (emp.full_name || '?')[0] }}</div>
                <div class="sf-info">
                    <div class="sf-name">{{ emp.full_name }}</div>
                    <div class="sf-contact">{{ emp.email }} {{ emp.phone ? '· ' + emp.phone : '' }}</div>
                    <div class="sf-position" v-if="emp.position">{{ emp.position }}</div>
                </div>
                <div class="sf-role" :style="{ color: roleColor(emp.role), borderColor: roleColor(emp.role) + '33', background: roleColor(emp.role) + '15' }">
                    {{ roleLabel(emp.role) }}
                </div>
                <div class="sf-perms">
                    <span v-for="p in (JSON.parse(emp.permissions || '[]')).slice(0, 3)" :key="p" class="sf-perm">{{ permLabel(p) }}</span>
                </div>
                <div :class="['sf-status', emp.status === 'active' ? 'green' : 'red']">{{ emp.status === 'active' ? '● نشط' : '● غير نشط' }}</div>
                <div class="sf-salary" v-if="emp.salary > 0">{{ Number(emp.salary).toLocaleString() }} {{ emp.salary_currency || 'EUR' }}</div>
            </div>
        </div>
        <div v-if="!employees?.length" class="sf-empty">لا يوجد موظفين</div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.sf-grid { display: flex; flex-direction: column; gap: 16px; }
.sf-header { display: flex; justify-content: space-between; align-items: center; }
.sf-header h2 { font-size: 16px; font-weight: 800; }
.sf-list { display: flex; flex-direction: column; gap: 8px; }
.sf-card { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 16px; transition: all .2s; }
.sf-card:hover { border-color: rgba(99,102,241,.15); }
.sf-avatar { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 17px; font-weight: 900; flex-shrink: 0; }
.sf-info { flex: 1; }
.sf-name { font-size: 14px; font-weight: 800; }
.sf-contact { font-size: 11px; color: #6b7280; }
.sf-position { font-size: 10px; color: #9ca3af; margin-top: 2px; }
.sf-role { font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 8px; border: 1px solid; }
.sf-perms { display: flex; gap: 4px; flex-wrap: wrap; }
.sf-perm { font-size: 9px; padding: 2px 8px; border-radius: 6px; background: #f1f5f9; color: #6b7280; font-weight: 600; }
.sf-status { font-size: 11px; font-weight: 700; }
.sf-status.green { color: #10b981; }
.sf-status.red { color: #ef4444; }
.sf-salary { font-size: 13px; font-weight: 800; color: #10b981; }
.sf-empty { text-align: center; padding: 40px; color: #9ca3af; }
</style>
