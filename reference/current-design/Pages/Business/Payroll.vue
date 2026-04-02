<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ payrolls: Object, employees: Array });
const showCreate = ref(false);
const fmt = (a) => Number(a || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric' }) : '';
const statusLabel = (s) => ({ draft: 'مسودة', approved: 'معتمدة', processing: 'قيد التنفيذ', completed: 'مكتملة', failed: 'فاشلة' }[s] || s);

const form = useForm({
    title: new Date().toLocaleDateString('ar-u-nu-latn', { month: 'long', year: 'numeric' }),
    period_type: 'monthly',
    period_start: '', period_end: '', currency: 'EUR',
    items: [],
});

const addAllEmployees = () => {
    form.items = (props.employees || []).map(e => ({
        employee_id: e.id, employee_name: e.full_name,
        base_salary: Number(e.salary || 0), bonus: 0, deductions: 0,
    }));
};

const removeItem = (i) => form.items.splice(i, 1);
const totalPayroll = computed(() => form.items.reduce((s, i) => s + (Number(i.base_salary || 0) + Number(i.bonus || 0) - Number(i.deductions || 0)), 0));
const submit = () => form.post(route('business.payroll.create'), { onSuccess: () => { showCreate.value = false; form.reset(); } });
</script>

<template>
<BusinessLayout title="الرواتب">
    <div class="pr-grid">
        <div class="pr-top">
            <h2>💵 إدارة الرواتب</h2>
            <button class="pr-btn" @click="showCreate = !showCreate; if(showCreate && !form.items.length) addAllEmployees()">
                {{ showCreate ? '✕' : '+ دورة رواتب جديدة' }}
            </button>
        </div>

        <div v-if="showCreate" class="pr-form">
            <div class="pr-form-top">
                <div class="pr-f"><label>العنوان</label><input v-model="form.title" /></div>
                <div class="pr-f"><label>نوع الفترة</label><select v-model="form.period_type"><option value="monthly">شهري</option><option value="weekly">أسبوعي</option></select></div>
                <div class="pr-f"><label>من</label><input v-model="form.period_start" type="date" /></div>
                <div class="pr-f"><label>إلى</label><input v-model="form.period_end" type="date" /></div>
            </div>

            <div class="pr-items-header">
                <span>الموظف</span><span>الراتب</span><span>المكافأة</span><span>الخصومات</span><span>الصافي</span><span></span>
            </div>
            <div v-for="(item, i) in form.items" :key="i" class="pr-item">
                <span class="pr-item-name">{{ item.employee_name }}</span>
                <input v-model="item.base_salary" type="number" step="0.01" class="pr-item-input" />
                <input v-model="item.bonus" type="number" step="0.01" class="pr-item-input" />
                <input v-model="item.deductions" type="number" step="0.01" class="pr-item-input" />
                <span class="pr-item-net">{{ fmt(Number(item.base_salary||0) + Number(item.bonus||0) - Number(item.deductions||0)) }}</span>
                <button class="pr-item-del" @click="removeItem(i)">✕</button>
            </div>

            <div class="pr-total">
                <span>الإجمالي:</span>
                <strong>{{ fmt(totalPayroll) }} {{ form.currency }}</strong>
            </div>

            <button class="pr-submit" @click="submit"
                    :disabled="!form.items.length || !form.period_start || !form.period_end || form.processing">
                {{ form.processing ? '⏳' : '✓ إنشاء دورة الرواتب' }}
            </button>
        </div>

        <!-- Payroll History -->
        <div class="pr-history">
            <h3>📋 سجل الرواتب</h3>
            <div v-for="p in payrolls?.data" :key="p.id" class="pr-card">
                <div class="pr-card-title">{{ p.title }}</div>
                <div class="pr-card-period">{{ fmtDate(p.period_start) }} — {{ fmtDate(p.period_end) }}</div>
                <div class="pr-card-stats">
                    <span>👥 {{ p.employee_count }} موظف</span>
                    <span>💰 {{ fmt(p.total_amount) }} {{ p.currency }}</span>
                    <span :class="p.status === 'completed' ? 'green' : ''">{{ statusLabel(p.status) }}</span>
                </div>
            </div>
            <div v-if="!payrolls?.data?.length" class="pr-empty">لا توجد دورات رواتب سابقة</div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.pr-grid { display: flex; flex-direction: column; gap: 16px; }
.pr-top { display: flex; justify-content: space-between; align-items: center; }
.pr-top h2 { font-size: 16px; font-weight: 800; }
.pr-btn { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #1a1a2e; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; }

.pr-form { background: #fff; border: 1px solid rgba(99,102,241,.15); border-radius: 16px; padding: 24px; }
.pr-form-top { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
.pr-f { display: flex; flex-direction: column; gap: 4px; }
.pr-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.pr-f input, .pr-f select { background: #f1f5f9; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px; color: #1a1a2e; font-family: 'Cairo'; font-size: 13px; outline: none; }
.pr-f select 

.pr-items-header { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr 40px; gap: 8px; padding: 8px 12px; font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; }
.pr-item { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr 40px; gap: 8px; padding: 8px 12px; align-items: center; border-bottom: 1px solid #f8f9fb; }
.pr-item-name { font-size: 13px; font-weight: 700; }
.pr-item-input { background: #f1f5f9; border: 1px solid #e8ecf1; border-radius: 8px; padding: 8px; color: #1a1a2e; font-family: 'Cairo'; font-size: 12px; outline: none; text-align: center; }
.pr-item-input:focus { border-color: #6366f1; }
.pr-item-net { font-size: 13px; font-weight: 800; color: #10b981; text-align: center; }
.pr-item-del { background: none; border: none; color: rgba(239,68,68,.5); cursor: pointer; font-size: 14px; }

.pr-total { display: flex; justify-content: space-between; padding: 14px 12px; margin-top: 8px; font-size: 16px; }
.pr-total strong { color: #10b981; font-size: 20px; }
.pr-submit { width: 100%; margin-top: 12px; background: linear-gradient(135deg, #10b981, #059669); color: #1a1a2e; border: none; padding: 14px; border-radius: 12px; font-weight: 800; font-family: 'Cairo'; cursor: pointer; font-size: 15px; }
.pr-submit:disabled { opacity: .4; }

.pr-history { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 18px; }
.pr-history h3 { font-size: 14px; font-weight: 800; margin-bottom: 12px; }
.pr-card { padding: 12px; border-bottom: 1px solid #f1f5f9; }
.pr-card:last-child { border-bottom: none; }
.pr-card-title { font-size: 14px; font-weight: 700; }
.pr-card-period { font-size: 11px; color: #9ca3af; }
.pr-card-stats { display: flex; gap: 16px; font-size: 12px; color: #6b7280; margin-top: 4px; }
.green { color: #10b981; }
.pr-empty { text-align: center; padding: 20px; color: #9ca3af; }
</style>
