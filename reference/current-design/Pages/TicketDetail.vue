<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ ticket: Object, replies: Array });
const msg = ref('');
const sending = ref(false);
const sendReply = () => {
  if (!msg.value.trim() || sending.value) return;
  sending.value = true;
  router.post(route('employee.tickets.reply', p.ticket.id), { message: msg.value }, {
    onSuccess: () => { msg.value = ''; sending.value = false; },
    onError: () => { sending.value = false; }
  });
};
const updateStatus = (s) => {
  router.patch(route('employee.tickets.status', p.ticket.id), { status: s });
};
const sl = s => ({open:'مفتوحة',in_progress:'قيد المعالجة',resolved:'محلولة',closed:'مغلقة'}[s]||s);
</script>
<template>
<Head :title="'تذكرة #' + ticket.id" />
<div class="td">
  <Link :href="route('employee.tickets')" class="td-back">← العودة للتذاكر</Link>
  <div class="td-header">
    <div>
      <h1 class="td-title">#{{ ticket.id }} — {{ ticket.subject }}</h1>
      <div class="td-meta">
        <span>{{ ticket.user_name || 'زائر' }}</span>
        <span>{{ ticket.user_email }}</span>
        <span v-if="ticket.user_phone">📞 {{ ticket.user_phone }}</span>
        <span>{{ new Date(ticket.created_at).toLocaleDateString('ar') }}</span>
        <Link v-if="ticket.user_id" :href="route('employee.customer', ticket.user_id)" class="td-profile-link">👤 بروفايل العميل</Link>
      </div>
    </div>
    <div class="td-actions">
      <span class="td-badge" :class="'td-b-' + ticket.status">{{ sl(ticket.status) }}</span>
      <select @change="updateStatus($event.target.value)" :value="ticket.status" class="td-select">
        <option value="open">مفتوحة</option>
        <option value="in_progress">قيد المعالجة</option>
        <option value="resolved">محلولة</option>
        <option value="closed">مغلقة</option>
      </select>
    </div>
  </div>

  <div class="td-body">
    <!-- Original message -->
    <div class="td-msg td-msg-user">
      <div class="td-msg-header">{{ ticket.user_name || 'العميل' }} <span class="td-msg-time">{{ new Date(ticket.created_at).toLocaleString('ar') }}</span></div>
      <div class="td-msg-text">{{ ticket.message || ticket.description }}</div>
    </div>

    <!-- Replies -->
    <div v-for="r in replies" :key="r.id" class="td-msg" :class="r.is_admin ? 'td-msg-admin' : 'td-msg-user'">
      <div class="td-msg-header">{{ r.is_admin ? '🛡️ الدعم' : ticket.user_name }} <span class="td-msg-time">{{ new Date(r.created_at).toLocaleString('ar') }}</span></div>
      <div class="td-msg-text">{{ r.message }}</div>
    </div>
  </div>

  <!-- Reply form -->
  <div class="td-reply">
    <textarea v-model="msg" class="td-textarea" placeholder="اكتب ردك هنا..." rows="3"></textarea>
    <button @click="sendReply" class="td-send" :disabled="sending || !msg.trim()">{{ sending ? 'جاري الإرسال...' : 'إرسال الرد ✉️' }}</button>
  </div>
</div>
</template>
<style scoped>
.td{direction:rtl;max-width:900px;margin:0 auto}
.td-back{font-size:13px;color:#64748b;text-decoration:none;font-weight:600;display:inline-block;margin-bottom:12px}.td-back:hover{color:#2563eb}
.td-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.td-title{font-size:18px;font-weight:900;color:#0f172a;margin:0}
.td-meta{display:flex;gap:12px;font-size:12px;color:#64748b;margin-top:6px;flex-wrap:wrap;align-items:center}
.td-profile-link{color:#2563eb;font-weight:700;text-decoration:none}.td-profile-link:hover{text-decoration:underline}
.td-actions{display:flex;gap:8px;align-items:center}
.td-badge{font-size:11px;font-weight:700;padding:4px 12px;border-radius:8px}
.td-b-open{background:#fef2f2;color:#dc2626}.td-b-in_progress{background:#fef3c7;color:#d97706}.td-b-resolved{background:#ecfdf5;color:#059669}.td-b-closed{background:#f1f5f9;color:#6b7280}
.td-select{padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#fff}
.td-body{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
.td-msg{padding:14px;border-radius:14px;border:1px solid #e8ebf0}
.td-msg-user{background:#f0f9ff;border-color:#bae6fd}
.td-msg-admin{background:#f0fdf4;border-color:#bbf7d0}
.td-msg-header{font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px;display:flex;justify-content:space-between}
.td-msg-time{font-size:10px;color:#94a3b8;font-weight:400}
.td-msg-text{font-size:14px;color:#334155;line-height:1.6;white-space:pre-wrap}
.td-reply{display:flex;flex-direction:column;gap:10px}
.td-textarea{width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;resize:vertical;outline:none;transition:border .2s;font-family:inherit}
.td-textarea:focus{border-color:#2563eb}
.td-send{align-self:flex-end;padding:10px 24px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s}
.td-send:hover{background:#1d4ed8}.td-send:disabled{background:#94a3b8;cursor:not-allowed}
</style>
