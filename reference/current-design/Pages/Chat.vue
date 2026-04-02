<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { initiateAdminCall } from '@/Composables/useAdminCall';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ conversations: Array });
const activeUser = ref(null);
const messages = ref([]);
const loading = ref(false);
const msg = ref('');
const sending = ref(false);
const chatBox = ref(null);

const openChat = async (userId) => {
  loading.value = true;
  activeUser.value = p.conversations.find(c => c.id === userId);
  try {
    const res = await fetch('/employee/chat/' + userId + '/messages');
    const data = await res.json();
    messages.value = data.messages || [];
    // Mark as read locally
    const conv = p.conversations.find(c => c.id === userId);
    if (conv) conv.unread_count = 0;
  } catch(e) { console.error(e); }
  loading.value = false;
  setTimeout(() => { if(chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight; }, 100);
};

const sendMsg = () => {
  if (!msg.value.trim() || !activeUser.value || sending.value) return;
  sending.value = true;
  router.post('/employee/chat/' + activeUser.value.id + '/reply', { message: msg.value }, {
    preserveState: true,
    onSuccess: () => {
      messages.value.push({ content: msg.value, sender_type: 'admin', created_at: new Date().toISOString() });
      msg.value = '';
      sending.value = false;
      setTimeout(() => { if(chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight; }, 100);
    },
    onError: () => { sending.value = false; }
  });
};

// Auto-open if URL has user param
onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('user')) openChat(parseInt(params.get('user')));
});
</script>
<template>
<Head title="الشات" />
<div class="ch">
  <div class="ch-sidebar">
    <h2 class="ch-sidebar-title">💬 المحادثات</h2>
    <div class="ch-list">
      <div v-for="c in conversations" :key="c.id" class="ch-conv" :class="{'ch-conv-active': activeUser?.id === c.id}" @click="openChat(c.id)">
        <div class="ch-conv-avatar" v-if="c.profile_photo"><img :src="c.profile_photo" /></div>
        <div class="ch-conv-avatar ch-conv-init" v-else>{{ c.full_name?.charAt(0) }}</div>
        <div class="ch-conv-info">
          <div class="ch-conv-name">{{ c.full_name }}</div>
          <div class="ch-conv-email">{{ c.email }}</div>
        </div>
        <span v-if="c.unread_count > 0" class="ch-unread">{{ c.unread_count }}</span>
      </div>
      <div v-if="!conversations?.length" class="ch-empty">لا توجد محادثات</div>
    </div>
  </div>
  <div class="ch-main">
    <div v-if="!activeUser" class="ch-placeholder">اختر محادثة من القائمة</div>
    <template v-else>
      <div class="ch-header">
        <span class="ch-header-name">{{ activeUser.full_name }}</span>
        <span class="ch-header-email">{{ activeUser.email }}</span>
        <button class="ch-call-btn" @click="initiateAdminCall(activeUser.id, activeUser.full_name)" title="اتصال صوتي">
          📞 اتصال
        </button>
      </div>
      <div class="ch-messages" ref="chatBox">
        <div v-if="loading" class="ch-loading">جاري التحميل...</div>
        <div v-for="(m, i) in messages" :key="i" class="ch-bubble" :class="m.sender_type === 'admin' ? 'ch-bubble-admin' : 'ch-bubble-user'">
          <div class="ch-bubble-text">{{ m.content }}</div>
          <div class="ch-bubble-time">{{ new Date(m.created_at).toLocaleTimeString('ar', {hour:'2-digit',minute:'2-digit'}) }}</div>
        </div>
      </div>
      <div class="ch-input-bar">
        <input v-model="msg" @keyup.enter="sendMsg" class="ch-input" placeholder="اكتب رسالتك..." />
        <button @click="sendMsg" class="ch-send" :disabled="sending || !msg.trim()">إرسال</button>
      </div>
    </template>
  </div>
</div>
</template>
<style scoped>
.ch{display:flex;direction:rtl;height:calc(100vh - 48px);gap:0;background:#fff;border-radius:16px;border:1px solid #e8ebf0;overflow:hidden}
.ch-sidebar{width:300px;border-left:1px solid #e8ebf0;display:flex;flex-direction:column;flex-shrink:0}
.ch-sidebar-title{font-size:14px;font-weight:800;color:#1e293b;padding:16px;margin:0;border-bottom:1px solid #f1f5f9}
.ch-list{flex:1;overflow-y:auto}
.ch-conv{display:flex;align-items:center;gap:10px;padding:12px 16px;cursor:pointer;transition:background .15s;border-bottom:1px solid #f8fafc}
.ch-conv:hover{background:#f8fafc}
.ch-conv-active{background:#eff6ff !important;border-right:3px solid #2563eb}
.ch-conv-avatar{width:36px;height:36px;border-radius:10px;overflow:hidden;flex-shrink:0}
.ch-conv-avatar img{width:100%;height:100%;object-fit:cover}
.ch-conv-init{background:#e0f2fe;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#0284c7}
.ch-conv-info{flex:1;min-width:0}
.ch-conv-name{font-size:13px;font-weight:700;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ch-conv-email{font-size:10px;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ch-unread{background:#ef4444;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:10px;min-width:18px;text-align:center}
.ch-main{flex:1;display:flex;flex-direction:column}
.ch-placeholder{flex:1;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:15px}
.ch-header{padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;gap:12px;align-items:center}
.ch-header-name{font-size:15px;font-weight:800;color:#0f172a}
.ch-header-email{font-size:12px;color:#94a3b8;flex:1}
.ch-call-btn{padding:6px 16px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;transition:background .15s;font-family:inherit}
.ch-call-btn:hover{background:#059669}
.ch-messages{flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:8px}
.ch-loading{text-align:center;color:#94a3b8;padding:20px}
.ch-bubble{max-width:70%;padding:10px 14px;border-radius:14px;position:relative}
.ch-bubble-user{align-self:flex-end;background:#e0f2fe;border-bottom-right-radius:4px}
.ch-bubble-admin{align-self:flex-start;background:#f0fdf4;border-bottom-left-radius:4px}
.ch-bubble-text{font-size:14px;color:#0f172a;line-height:1.5;white-space:pre-wrap}
.ch-bubble-time{font-size:9px;color:#94a3b8;margin-top:4px;text-align:left}
.ch-input-bar{display:flex;gap:8px;padding:12px 20px;border-top:1px solid #f1f5f9}
.ch-input{flex:1;padding:10px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;transition:border .2s}
.ch-input:focus{border-color:#2563eb}
.ch-send{padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s}
.ch-send:hover{background:#1d4ed8}.ch-send:disabled{background:#94a3b8;cursor:not-allowed}
.ch-empty{text-align:center;color:#cbd5e1;font-size:13px;padding:30px}
@media(max-width:768px){.ch-sidebar{width:200px}}
</style>
