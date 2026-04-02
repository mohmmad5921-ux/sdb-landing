<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ tickets: Object, stats: Object, currentStatus: String });
const filter = (s) => router.get(route('employee.tickets'), { status: s }, { preserveState: true });
const sl = s => ({open:'مفتوحة',in_progress:'قيد المعالجة',resolved:'محلولة',closed:'مغلقة'}[s]||s);
</script>
<template>
<Head title="تذاكر الدعم" />
<div class="et">
  <h1 class="et-title">🎫 تذاكر الدعم</h1>
  <div class="et-stats">
    <button @click="filter('all')" class="et-stat" :class="{'et-stat-act': currentStatus==='all'}"><span class="et-stat-n">{{ stats.total }}</span><span>الكل</span></button>
    <button @click="filter('open')" class="et-stat" :class="{'et-stat-act': currentStatus==='open'}"><span class="et-stat-n" style="color:#dc2626">{{ stats.open }}</span><span>مفتوحة</span></button>
    <button @click="filter('in_progress')" class="et-stat" :class="{'et-stat-act': currentStatus==='in_progress'}"><span class="et-stat-n" style="color:#d97706">{{ stats.in_progress }}</span><span>قيد المعالجة</span></button>
    <button @click="filter('resolved')" class="et-stat" :class="{'et-stat-act': currentStatus==='resolved'}"><span class="et-stat-n" style="color:#059669">{{ stats.resolved }}</span><span>محلولة</span></button>
  </div>
  <div class="et-list">
    <Link v-for="t in tickets.data" :key="t.id" :href="route('employee.tickets.show', t.id)" class="et-item">
      <div class="et-item-top">
        <span class="et-item-id">#{{ t.id }}</span>
        <span class="et-item-subj">{{ t.subject }}</span>
        <span class="et-badge" :class="'et-b-' + t.status">{{ sl(t.status) }}</span>
      </div>
      <div class="et-item-bot">
        <span>{{ t.user_name || 'زائر' }}</span>
        <span>{{ t.user_email }}</span>
        <span>{{ new Date(t.created_at).toLocaleDateString('ar') }}</span>
      </div>
    </Link>
    <div v-if="!tickets.data?.length" class="et-empty">لا توجد تذاكر</div>
  </div>
</div>
</template>
<style scoped>
.et{direction:rtl;max-width:1000px;margin:0 auto}
.et-title{font-size:22px;font-weight:900;color:#0f172a;margin-bottom:16px}
.et-stats{display:flex;gap:10px;margin-bottom:20px}
.et-stat{background:#fff;border:1px solid #e8ebf0;border-radius:12px;padding:12px 20px;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:2px;font-size:11px;color:#64748b;font-weight:600;transition:all .15s;flex:1}
.et-stat:hover{border-color:#2563eb}
.et-stat-act{border-color:#2563eb;background:#eff6ff}
.et-stat-n{font-size:22px;font-weight:900;color:#0f172a}
.et-list{display:flex;flex-direction:column;gap:8px}
.et-item{display:block;background:#fff;border:1px solid #e8ebf0;border-radius:12px;padding:14px 16px;text-decoration:none;transition:all .15s}
.et-item:hover{border-color:#2563eb;transform:translateY(-1px);box-shadow:0 2px 8px rgba(0,0,0,.04)}
.et-item-top{display:flex;align-items:center;gap:10px}
.et-item-id{font-size:12px;font-weight:800;color:#94a3b8}
.et-item-subj{flex:1;font-size:14px;font-weight:700;color:#0f172a}
.et-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:6px}
.et-b-open{background:#fef2f2;color:#dc2626}.et-b-in_progress{background:#fef3c7;color:#d97706}.et-b-resolved{background:#ecfdf5;color:#059669}.et-b-closed{background:#f1f5f9;color:#6b7280}
.et-item-bot{display:flex;gap:16px;margin-top:6px;font-size:11px;color:#94a3b8}
.et-empty{text-align:center;color:#cbd5e1;font-size:14px;padding:40px;background:#fff;border:1px solid #e8ebf0;border-radius:14px}
</style>
