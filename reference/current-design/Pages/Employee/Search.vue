<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ results: Array, query: String });
const q = ref(p.query || '');
let timer = null;
const doSearch = () => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(route('employee.search'), { q: q.value }, { preserveState: true, preserveScroll: true });
  }, 400);
};
const statusLabel = s => ({active:'نشط',frozen:'مجمّد',suspended:'موقف',inactive:'غير نشط'}[s]||s);
const kycLabel = s => ({verified:'موثّق',pending:'قيد المراجعة',rejected:'مرفوض',none:'لم يقدم'}[s]||s);
</script>
<template>
<Head title="بحث عن عميل" />
<div class="es">
  <!-- Hero Section -->
  <div class="es-hero" v-if="!q">
    <img src="/images/sdb-staff-logo.png" alt="SDB Wallet" class="es-logo" />
    <h1 class="es-title">بحث عن عميل</h1>
    <p class="es-subtitle">بوابة موظفي الدعم — SDB Wallet</p>
  </div>

  <!-- Search Bar -->
  <div class="es-search-wrap" :class="{ 'es-search-center': !q }">
    <div class="es-search-icon">🔍</div>
    <input v-model="q" @input="doSearch" class="es-input" placeholder="ابحث برقم الهاتف، الإيميل، الاسم، رقم الحساب، IBAN..." autofocus />
  </div>
  <div class="es-hint" v-if="!q">اكتب رقم الهاتف، الإيميل، اسم العميل، رقم الحساب، أو رقم الهوية</div>

  <!-- Results -->
  <div v-if="q && results?.length" class="es-results">
    <div class="es-count">{{ results.length }} نتيجة</div>
    <Link v-for="r in results" :key="r.id" :href="route('employee.customer', r.id)" class="es-card">
      <div class="es-card-top">
        <div class="es-avatar" v-if="r.profile_photo"><img :src="r.profile_photo" /></div>
        <div class="es-avatar es-init" v-else>{{ r.full_name?.charAt(0) || '?' }}</div>
        <div class="es-info">
          <div class="es-name">{{ r.full_name }}</div>
          <div class="es-name-ar" v-if="r.full_name_ar">{{ r.full_name_ar }}</div>
          <div class="es-detail">{{ r.email }} · {{ r.phone }}</div>
        </div>
        <div class="es-badges">
          <span class="es-badge" :class="'es-b-' + r.status">{{ statusLabel(r.status) }}</span>
          <span class="es-badge" :class="'es-b-kyc-' + r.kyc_status">{{ kycLabel(r.kyc_status) }}</span>
        </div>
      </div>
      <div class="es-card-bottom">
        <span>رقم العميل: <b>{{ r.customer_number }}</b></span>
        <span>{{ r.country }}</span>
        <span>{{ new Date(r.created_at).toLocaleDateString('ar') }}</span>
      </div>
    </Link>
  </div>

  <div v-if="q && q.length >= 2 && !results?.length" class="es-empty">لا توجد نتائج لـ "{{ q }}"</div>
</div>
</template>
<style scoped>
.es{direction:rtl;max-width:900px;margin:0 auto;padding-top:20px}
/* Hero */
.es-hero{display:flex;flex-direction:column;align-items:center;padding:40px 0 20px;text-align:center}
.es-logo{width:140px;height:auto;margin-bottom:16px}
.es-title{font-size:26px;font-weight:900;color:#0f172a;margin:0}
.es-subtitle{font-size:13px;color:#94a3b8;margin-top:4px;font-weight:600}
/* Search */
.es-search-wrap{position:relative;margin-bottom:12px}
.es-search-center{max-width:600px;margin:0 auto 12px}
.es-search-icon{position:absolute;top:50%;right:16px;transform:translateY(-50%);font-size:18px;pointer-events:none}
.es-input{width:100%;padding:16px 48px 16px 18px;border:2px solid #e2e8f0;border-radius:16px;font-size:15px;font-weight:600;outline:none;transition:all .25s;background:#fff;font-family:inherit}
.es-input:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.1)}
.es-hint{text-align:center;color:#94a3b8;font-size:13px;padding:20px}
/* Results */
.es-count{font-size:12px;color:#94a3b8;font-weight:700;margin-bottom:10px}
.es-results{display:flex;flex-direction:column;gap:10px}
.es-card{display:block;background:#fff;border:1px solid #e8ebf0;border-radius:14px;padding:16px;text-decoration:none;transition:all .15s}
.es-card:hover{border-color:#2563eb;box-shadow:0 4px 16px rgba(0,0,0,.06);transform:translateY(-1px)}
.es-card-top{display:flex;align-items:center;gap:12px}
.es-avatar{width:48px;height:48px;border-radius:12px;overflow:hidden;flex-shrink:0}
.es-avatar img{width:100%;height:100%;object-fit:cover}
.es-init{background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#2563eb}
.es-info{flex:1;min-width:0}
.es-name{font-size:15px;font-weight:800;color:#0f172a}
.es-name-ar{font-size:12px;color:#64748b}
.es-detail{font-size:12px;color:#64748b;margin-top:2px}
.es-badges{display:flex;gap:6px;flex-shrink:0}
.es-badge{font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px}
.es-b-active{background:#ecfdf5;color:#059669}.es-b-frozen{background:#fef2f2;color:#dc2626}.es-b-suspended{background:#fef3c7;color:#d97706}.es-b-inactive{background:#f1f5f9;color:#6b7280}
.es-b-kyc-verified{background:#ecfdf5;color:#059669}.es-b-kyc-pending{background:#fef3c7;color:#d97706}.es-b-kyc-rejected{background:#fef2f2;color:#dc2626}.es-b-kyc-none{background:#f1f5f9;color:#6b7280}
.es-card-bottom{display:flex;gap:16px;margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9;font-size:11px;color:#94a3b8}
.es-card-bottom b{color:#0f172a;font-weight:700}
.es-empty{text-align:center;color:#cbd5e1;font-size:14px;padding:40px;background:#fff;border:1px solid #e8ebf0;border-radius:14px}
</style>
