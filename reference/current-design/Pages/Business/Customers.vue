<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ customers: Object, search: String });
const showAdd = ref(false);
const searchVal = ref(props.search || '');

const form = useForm({ name: '', email: '', phone: '', address: '', notes: '' });
const submit = () => form.post(route('business.customers.add'), { onSuccess: () => { showAdd.value = false; form.reset(); } });
const doSearch = () => router.get('/business/customers', { search: searchVal.value }, { preserveState: true });
</script>

<template>
<BusinessLayout title="العملاء">
    <div class="cu-grid">
        <div class="cu-top">
            <div class="cu-search-wrap">
                <input v-model="searchVal" @keyup.enter="doSearch" class="cu-search" placeholder="🔍 بحث عن عميل..." />
                <button class="cu-search-btn" @click="doSearch">بحث</button>
            </div>
            <button class="cu-add-btn" @click="showAdd = !showAdd">{{ showAdd ? '✕' : '+ إضافة عميل' }}</button>
        </div>

        <div v-if="showAdd" class="cu-form">
            <h3>👤 عميل جديد</h3>
            <div class="cu-form-row">
                <div class="cu-f"><label>الاسم *</label><input v-model="form.name" placeholder="اسم العميل" /></div>
                <div class="cu-f"><label>البريد</label><input v-model="form.email" type="email" /></div>
                <div class="cu-f"><label>الهاتف</label><input v-model="form.phone" placeholder="+963..." /></div>
            </div>
            <div class="cu-form-row">
                <div class="cu-f"><label>العنوان</label><input v-model="form.address" /></div>
                <div class="cu-f"><label>ملاحظات</label><input v-model="form.notes" /></div>
            </div>
            <button class="cu-submit" @click="submit" :disabled="!form.name || form.processing">✓ إضافة</button>
        </div>

        <div class="cu-list">
            <div v-for="c in customers?.data" :key="c.id" class="cu-card">
                <div class="cu-avatar">{{ (c.name || '?')[0] }}</div>
                <div class="cu-info">
                    <div class="cu-name">{{ c.name }}</div>
                    <div class="cu-contact">
                        <span v-if="c.email">✉ {{ c.email }}</span>
                        <span v-if="c.phone">📞 {{ c.phone }}</span>
                    </div>
                </div>
                <div class="cu-meta">
                    <div>💰 {{ Number(c.total_spent || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</div>
                    <div class="cu-meta-sub">{{ c.total_transactions || 0 }} عملية · {{ c.total_contracts || 0 }} عقد</div>
                </div>
                <div :class="['cu-status', c.user_id ? 'cu-linked' : '']">
                    {{ c.user_id ? '🔗 مربوط بـ SDB' : '👤 خارجي' }}
                </div>
            </div>
        </div>
        <div v-if="!customers?.data?.length" class="cu-empty">لا يوجد عملاء</div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.cu-grid { display: flex; flex-direction: column; gap: 16px; }
.cu-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.cu-search-wrap { display: flex; gap: 8px; flex: 1; max-width: 400px; }
.cu-search { flex: 1; background: #f1f5f9; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px 14px; color: #1a1a2e; font-family: 'Cairo'; font-size: 13px; outline: none; }
.cu-search:focus { border-color: #6366f1; }
.cu-search-btn { background: rgba(99,102,241,.15); border: 1px solid rgba(99,102,241,.2); border-radius: 10px; padding: 10px 16px; color: #818cf8; font-family: 'Cairo'; font-size: 12px; font-weight: 700; cursor: pointer; }
.cu-add-btn { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #1a1a2e; border: none; padding: 10px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; }

.cu-form { background: #fff; border: 1px solid rgba(99,102,241,.15); border-radius: 16px; padding: 20px; }
.cu-form h3 { font-size: 15px; font-weight: 800; margin-bottom: 14px; }
.cu-form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 10px; }
.cu-f { display: flex; flex-direction: column; gap: 4px; }
.cu-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.cu-f input { background: #f1f5f9; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px; color: #1a1a2e; font-family: 'Cairo'; font-size: 13px; outline: none; }
.cu-f input:focus { border-color: #6366f1; }
.cu-submit { background: linear-gradient(135deg, #10b981, #059669); color: #1a1a2e; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 700; font-family: 'Cairo'; cursor: pointer; }
.cu-submit:disabled { opacity: .4; }

.cu-list { display: flex; flex-direction: column; gap: 6px; }
.cu-card { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 14px 18px; transition: all .2s; }
.cu-card:hover { border-color: rgba(99,102,241,.15); }
.cu-avatar { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; flex-shrink: 0; }
.cu-info { flex: 1; }
.cu-name { font-size: 14px; font-weight: 800; }
.cu-contact { font-size: 11px; color: #6b7280; display: flex; gap: 12px; }
.cu-meta { text-align: left; font-size: 13px; font-weight: 700; }
.cu-meta-sub { font-size: 10px; color: #9ca3af; font-weight: 600; }
.cu-status { font-size: 10px; padding: 4px 10px; border-radius: 8px; background: #e8ecf1; color: #6b7280; font-weight: 600; }
.cu-linked { background: rgba(16,185,129,.1); color: #10b981; }
.cu-empty { text-align: center; padding: 40px; color: #9ca3af; }
@media (max-width: 768px) { .cu-form-row { grid-template-columns: 1fr; } }
</style>
