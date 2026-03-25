<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
defineOptions({ layout: AdminLayout });
const p = defineProps({ frozenUsers: Array, history: Array, activeFrozen: Number });
const show = ref(false);
const form = useForm({user_id:'',reason:'',notes:''});
const freeze = () => form.post(route('admin.frozen.freeze'), {onSuccess:()=>{show.value=false;form.reset()}});
const unfreeze = (id) => { if(confirm('فك تجميد هذا الحساب؟')) router.post(route('admin.frozen.unfreeze', id)); };
</script>
<template>
<Head title="الحسابات المجمّدة" />
<div class="fz">
  <div class="fz-top">
    <h1 class="fz-h">🧊 الحسابات المجمّدة</h1>
    <div class="fz-topR"><span class="fz-cnt">{{ p.activeFrozen }} حساب مجمّد</span><button @click="show=!show" class="fz-add">+ تجميد حساب</button></div>
  </div>

  <div v-if="show" class="fz-form">
    <input v-model="form.user_id" placeholder="رقم العميل (ID)" class="fz-inp" type="number" />
    <input v-model="form.reason" placeholder="السبب" class="fz-inp" />
    <textarea v-model="form.notes" placeholder="ملاحظات (اختياري)" class="fz-ta" rows="2"></textarea>
    <button @click="freeze" class="fz-frz" :disabled="form.processing">🧊 تجميد</button>
  </div>

  <!-- Active frozen users -->
  <h2 class="fz-sub">الحسابات المجمّدة حالياً</h2>
  <table class="fz-tbl">
    <thead><tr><th>العميل</th><th>البريد</th><th>الحالة</th><th>سبب التجميد</th><th>تاريخ التجميد</th><th>إجراء</th></tr></thead>
    <tbody>
      <tr v-for="u in p.frozenUsers" :key="u.id">
        <td class="fz-name">{{ u.full_name }}</td>
        <td>{{ u.email }}</td>
        <td><span class="fz-act fz-red">{{ u.status }}</span></td>
        <td>{{ u.frozen_reason || '—' }}</td>
        <td class="fz-date">{{ u.frozen_at ? new Date(u.frozen_at).toLocaleString('ar') : '—' }}</td>
        <td><button @click="unfreeze(u.id)" class="fz-unf">✅ فك التجميد</button></td>
      </tr>
      <tr v-if="!p.frozenUsers?.length"><td colspan="6" class="fz-empty">لا توجد حسابات مجمّدة</td></tr>
    </tbody>
  </table>

  <!-- History log -->
  <h2 class="fz-sub" style="margin-top:32px">📋 سجل التجميد</h2>
  <table class="fz-tbl">
    <thead><tr><th>العميل</th><th>البريد</th><th>الإجراء</th><th>السبب</th><th>التاريخ</th></tr></thead>
    <tbody>
      <tr v-for="h in p.history" :key="h.id">
        <td class="fz-name">{{ h.user_name }}</td>
        <td>{{ h.user_email }}</td>
        <td><span class="fz-act" :class="h.action==='freeze'?'fz-red':'fz-grn'">{{ h.action==='freeze'?'تجميد':'فك تجميد' }}</span></td>
        <td>{{ h.reason }}</td>
        <td class="fz-date">{{ new Date(h.created_at).toLocaleDateString('ar') }}</td>
      </tr>
      <tr v-if="!p.history?.length"><td colspan="5" class="fz-empty">لا يوجد سجل</td></tr>
    </tbody>
  </table>
</div>
</template>
<style scoped>
.fz{padding:24px;direction:rtl}.fz-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.fz-h{font-size:22px;font-weight:800;color:#1e293b}.fz-topR{display:flex;align-items:center;gap:12px}
.fz-cnt{font-size:13px;font-weight:700;color:#dc2626;background:#fef2f2;padding:6px 14px;border-radius:8px}
.fz-add{padding:10px 20px;background:#0f172a;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px}
.fz-form{background:#fff;border-radius:14px;padding:20px;border:1px solid #f1f5f9;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
.fz-inp{padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;flex:1;min-width:150px}.fz-ta{padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;flex:2;min-width:200px;resize:none}
.fz-frz{padding:10px 20px;background:#dc2626;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:13px}
.fz-sub{font-size:16px;font-weight:700;color:#334155;margin-bottom:12px}
.fz-tbl{width:100%;background:#fff;border-radius:16px;border-collapse:collapse;margin-bottom:8px}
.fz-tbl th{font-size:11px;font-weight:700;color:#94a3b8;text-align:right;padding:12px 16px;border-bottom:1px solid #f1f5f9}
.fz-tbl td{font-size:13px;color:#334155;padding:12px 16px;border-bottom:1px solid #f8fafc}.fz-name{font-weight:700}
.fz-act{font-size:11px;font-weight:700;padding:4px 10px;border-radius:6px}.fz-red{background:#fef2f2;color:#dc2626}.fz-grn{background:#ecfdf5;color:#059669}
.fz-unf{padding:6px 14px;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;border-radius:8px;font-weight:700;cursor:pointer;font-size:12px}
.fz-unf:hover{background:#d1fae5}
.fz-date{color:#94a3b8;font-size:12px}.fz-empty{text-align:center;color:#cbd5e1;font-style:italic}
</style>
