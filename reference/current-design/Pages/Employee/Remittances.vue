<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ remittances: Object, governorates: Array, districts: Array, agents: Array, stats: Object, filters: Object });
const f = (n,s='€') => s + Number(n||0).toLocaleString('en', {minimumFractionDigits:2});
const sl = s => ({ready:'جاهزة',collected:'مسلّمة',cancelled:'ملغية',expired:'منتهية'}[s]||s);

// Search
const search = ref(p.filters?.search || '');
const statusFilter = ref(p.filters?.status || '');
const applySearch = () => {
  router.get('/employee/remittances', { search: search.value || undefined, status: statusFilter.value || undefined }, { preserveState: true });
};

// Cancel
const cancelId = ref(null);
const cancelConfirm = () => {
  router.delete('/employee/remittances/' + cancelId.value + '/cancel', { onSuccess: () => { cancelId.value = null; } });
};

// Redirect (cascading)
const redirectId = ref(null);
const selGov = ref('');
const selDist = ref('');
const selAgent = ref('');

const filteredDists = computed(() => selGov.value ? (p.districts || []).filter(d => d.governorate_id == selGov.value) : []);
const filteredAgents = computed(() => selDist.value ? (p.agents || []).filter(a => a.district_id == selDist.value) : []);

const openRedirect = (id) => { redirectId.value = id; selGov.value = ''; selDist.value = ''; selAgent.value = ''; };
const closeRedirect = () => { redirectId.value = null; };
const confirmRedirect = () => {
  if (!selAgent.value) return;
  router.patch('/employee/remittances/' + redirectId.value + '/redirect', { agent_id: selAgent.value }, {
    onSuccess: () => { redirectId.value = null; }
  });
};
</script>
<template>
<Head title="الحوالات" />
<div class="er">
  <h1 class="er-title">💸 الحوالات</h1>
  <div class="er-stats">
    <div class="er-stat"><span class="er-stat-n">{{ stats.total }}</span><span>إجمالي</span></div>
    <div class="er-stat"><span class="er-stat-n" style="color:#d97706">{{ stats.ready }}</span><span>جاهزة</span></div>
    <div class="er-stat"><span class="er-stat-n" style="color:#059669">{{ stats.collected }}</span><span>مسلّمة</span></div>
  </div>

  <!-- Search & Filter -->
  <div class="er-filters">
    <input v-model="search" @keyup.enter="applySearch" placeholder="🔍 بحث بالاسم أو رقم الهاتف أو رقم الحوالة..." class="er-search" />
    <select v-model="statusFilter" @change="applySearch" class="er-filter-sel">
      <option value="">كل الحالات</option>
      <option value="ready">جاهزة</option>
      <option value="collected">مسلّمة</option>
      <option value="cancelled">ملغية</option>
    </select>
    <button @click="applySearch" class="er-search-btn">بحث</button>
  </div>

  <div class="er-card">
    <table class="er-tbl">
      <thead><tr><th>#</th><th>المرسل</th><th>المستلم</th><th>المبلغ</th><th>الوكيل/الفرع</th><th>المنطقة</th><th>الحالة</th><th>التاريخ</th><th>إجراء</th></tr></thead>
      <tbody>
        <tr v-for="r in remittances.data" :key="r.id" :class="{'er-row-ready': r.status==='ready'}">
          <td>{{ r.id }}</td>
          <td class="er-bold">{{ r.sender_name }}</td>
          <td class="er-bold">{{ r.recipient_name }}<br><span class="er-sub">{{ r.recipient_phone }}</span></td>
          <td class="er-bold">{{ f(r.amount) }}</td>
          <td>{{ r.agent_name }}</td>
          <td class="er-sub">{{ r.governorate_ar }} — {{ r.district_ar }}</td>
          <td><span class="er-badge" :class="'er-b-' + r.status">{{ sl(r.status) }}</span></td>
          <td class="er-date">{{ new Date(r.created_at).toLocaleDateString('ar') }}</td>
          <td class="er-actions">
            <button v-if="r.status === 'ready'" @click="openRedirect(r.id)" class="er-btn">🔄 تغيير</button>
            <button v-if="r.status === 'ready'" @click="cancelId = r.id" class="er-btn er-btn-red">❌ إلغاء</button>
          </td>
        </tr>
        <tr v-if="!remittances.data?.length"><td colspan="9" class="er-empty">لا توجد حوالات</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div v-if="remittances.last_page > 1" class="er-pag">
    <button v-for="page in remittances.last_page" :key="page" @click="router.get(remittances.path + '?page=' + page + (search ? '&search=' + search : '') + (statusFilter ? '&status=' + statusFilter : ''))" class="er-pag-btn" :class="{ active: page === remittances.current_page }">{{ page }}</button>
  </div>

  <!-- Cancel Modal -->
  <div v-if="cancelId" class="er-modal-overlay" @click.self="cancelId = null">
    <div class="er-modal">
      <h3 class="er-modal-title">❌ إلغاء الحوالة #{{ cancelId }}</h3>
      <p class="er-modal-desc">هل أنت متأكد من إلغاء هذه الحوالة؟ سيتم إرجاع المبلغ لحساب العميل.</p>
      <div class="er-modal-actions">
        <button @click="cancelConfirm" class="er-modal-ok er-modal-red">تأكيد الإلغاء ❌</button>
        <button @click="cancelId = null" class="er-modal-cancel">تراجع</button>
      </div>
    </div>
  </div>

  <!-- Redirect Modal (Cascading) -->
  <div v-if="redirectId" class="er-modal-overlay" @click.self="closeRedirect">
    <div class="er-modal">
      <h3 class="er-modal-title">🔄 تغيير وجهة الحوالة #{{ redirectId }}</h3>

      <label class="er-modal-lbl">المحافظة:</label>
      <select v-model="selGov" @change="selDist = ''; selAgent = ''" class="er-modal-select">
        <option value="">— اختر المحافظة —</option>
        <option v-for="g in governorates" :key="g.id" :value="g.id">{{ g.name_ar }}</option>
      </select>

      <label v-if="selGov" class="er-modal-lbl">المنطقة:</label>
      <select v-if="selGov" v-model="selDist" @change="selAgent = ''" class="er-modal-select">
        <option value="">— اختر المنطقة —</option>
        <option v-for="d in filteredDists" :key="d.id" :value="d.id">{{ d.name_ar }}</option>
      </select>

      <label v-if="selDist" class="er-modal-lbl">المكتب/الفرع:</label>
      <select v-if="selDist" v-model="selAgent" class="er-modal-select">
        <option value="">— اختر المكتب —</option>
        <option v-for="a in filteredAgents" :key="a.id" :value="a.id">{{ a.name_ar }}</option>
      </select>

      <div v-if="filteredAgents.length === 0 && selDist" class="er-no-agents">⚠️ لا يوجد مكاتب نشطة في هذه المنطقة</div>

      <div class="er-modal-actions">
        <button @click="confirmRedirect" class="er-modal-ok" :disabled="!selAgent">تأكيد ✅</button>
        <button @click="closeRedirect" class="er-modal-cancel">إلغاء</button>
      </div>
    </div>
  </div>
</div>
</template>
<style scoped>
.er{direction:rtl;max-width:1200px;margin:0 auto}
.er-title{font-size:22px;font-weight:900;color:#0f172a;margin-bottom:16px}
.er-stats{display:flex;gap:14px;margin-bottom:16px}
.er-stat{background:#fff;border:1px solid #e8ebf0;border-radius:12px;padding:14px 24px;display:flex;flex-direction:column;align-items:center;gap:2px;font-size:11px;color:#64748b;font-weight:600}
.er-stat-n{font-size:24px;font-weight:900;color:#0f172a}
/* Filters */
.er-filters{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.er-search{flex:1;min-width:250px;padding:10px 16px;border:2px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;font-family:inherit;direction:rtl}
.er-search:focus{border-color:#2563eb}
.er-filter-sel{padding:10px 16px;border:2px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;min-width:130px}
.er-search-btn{padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}
.er-search-btn:hover{background:#1d4ed8}
/* Table */
.er-card{background:#fff;border:1px solid #e8ebf0;border-radius:16px;padding:16px;overflow-x:auto}
.er-tbl{width:100%;border-collapse:collapse;min-width:900px}
.er-tbl th{font-size:11px;font-weight:700;color:#94a3b8;text-align:right;padding:10px 12px;border-bottom:2px solid #e8ebf0;background:#fafbfc}
.er-tbl td{font-size:13px;color:#334155;padding:10px 12px;border-bottom:1px solid #f1f5f9}
.er-tbl tr:hover td{background:#fafbfc}
.er-bold{font-weight:700;color:#0f172a}
.er-sub{font-size:10px;color:#94a3b8}
.er-date{font-size:11px;color:#94a3b8}
.er-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:6px}
.er-b-ready{background:#fef3c7;color:#d97706}.er-b-collected{background:#ecfdf5;color:#059669}.er-b-cancelled{background:#fef2f2;color:#dc2626}
.er-row-ready td{background:#fffbeb !important}
.er-actions{display:flex;gap:6px;flex-wrap:nowrap}
.er-btn{padding:4px 10px;background:#fff;border:1px solid #2563eb;border-radius:8px;color:#2563eb;font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;white-space:nowrap}
.er-btn:hover{background:#2563eb;color:#fff}
.er-btn-red{border-color:#dc2626;color:#dc2626}
.er-btn-red:hover{background:#dc2626;color:#fff}
.er-empty{text-align:center;color:#cbd5e1;padding:20px}
/* Pagination */
.er-pag{display:flex;gap:6px;justify-content:center;margin-top:16px}
.er-pag-btn{padding:6px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;cursor:pointer;font-weight:600}
.er-pag-btn.active{background:#2563eb;color:#fff;border-color:#2563eb}
/* Modal */
.er-modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;z-index:999}
.er-modal{background:#fff;border-radius:16px;padding:24px;min-width:420px;max-width:520px;direction:rtl}
.er-modal-title{font-size:16px;font-weight:900;color:#0f172a;margin:0 0 12px}
.er-modal-desc{font-size:13px;color:#64748b;margin-bottom:16px;line-height:1.6}
.er-modal-lbl{font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;margin-top:10px}
.er-modal-select{width:100%;padding:10px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;margin-bottom:4px;font-family:inherit}
.er-modal-select:focus{border-color:#2563eb}
.er-no-agents{font-size:12px;color:#d97706;background:#fef3c7;padding:8px 12px;border-radius:8px;margin:8px 0}
.er-modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px}
.er-modal-ok{padding:8px 20px;background:#059669;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}
.er-modal-ok:disabled{background:#94a3b8;cursor:not-allowed}
.er-modal-red{background:#dc2626}.er-modal-red:hover{background:#b91c1c}
.er-modal-cancel{padding:8px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}
</style>
