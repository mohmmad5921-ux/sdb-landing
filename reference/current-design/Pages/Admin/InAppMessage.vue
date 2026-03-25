<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, reactive } from 'vue';

const props = defineProps({ messages: Array });

const templates = [
  { label: '🧪 وضع التجريب', ar: 'نحن حالياً بوضع التجريب. جميع العمليات تجريبية وستُعاد ضبطها عند الإطلاق الرسمي.', en: 'We are currently in testing mode. All transactions are for testing and will be reset upon official launch.' },
  { label: '🚀 الإطلاق قريباً', ar: 'الإطلاق الرسمي قريباً! نعمل بجد لإطلاق خدماتنا. تابعونا!', en: 'Official launch is coming soon! We are working hard to launch our services. Stay tuned!' },
  { label: '📅 تاريخ الإطلاق', ar: 'سيتم إطلاق الخدمات المصرفية بالكامل بتاريخ 1/5/2026.', en: 'Full banking services will launch on 01/05/2026.' },
  { label: '🔧 صيانة', ar: 'نقوم حالياً بإجراء صيانة دورية للنظام. بعض الخدمات قد تكون محدودة مؤقتاً.', en: 'We are currently performing routine system maintenance. Some services may be temporarily limited.' },
  { label: '✨ ميزة جديدة', ar: 'ميزات جديدة قادمة قريباً! نعمل على تحسينات كبيرة لتجربتكم المصرفية.', en: 'New features coming soon! We are working on major improvements to your banking experience.' },
  { label: '👋 ترحيب', ar: 'مرحباً بك في SDB Wallet! حسابك جاهز. استكشف خدماتنا المصرفية الرقمية.', en: 'Welcome to SDB Wallet! Your account is ready. Explore our digital banking services.' },
];

// New message form
const showAdd = ref(false);
const newMsg = reactive({ message_ar: '', message_en: '', active: true });

const selectTemplate = (tpl) => {
  newMsg.message_ar = tpl.ar;
  newMsg.message_en = tpl.en;
};

const addMessage = () => {
  router.post(route('admin.in-app-message.store'), { message_ar: newMsg.message_ar, message_en: newMsg.message_en, active: newMsg.active ? 1 : 0, sort_order: 0 }, {
    onSuccess: () => { newMsg.message_ar = ''; newMsg.message_en = ''; showAdd.value = false; }
  });
};

const toggleActive = (msg) => {
  router.put(route('admin.in-app-message.update', msg.id), { ...msg, active: msg.active ? 0 : 1 });
};

const deleteMessage = (msg) => {
  if (confirm('تأكيد حذف هذه الرسالة؟')) {
    router.delete(route('admin.in-app-message.destroy', msg.id));
  }
};

// Edit mode
const editId = ref(null);
const editForm = reactive({ message_ar: '', message_en: '' });
const startEdit = (msg) => { editId.value = msg.id; editForm.message_ar = msg.message_ar; editForm.message_en = msg.message_en; };
const cancelEdit = () => { editId.value = null; };
const saveEdit = (msg) => {
  router.put(route('admin.in-app-message.update', msg.id), { message_ar: editForm.message_ar, message_en: editForm.message_en, active: msg.active ? 1 : 0, sort_order: msg.sort_order || 0 }, {
    onSuccess: () => { editId.value = null; }
  });
};
</script>

<template>
  <Head title="رسالة داخل التطبيق" />
  <AdminLayout>
    <div class="iam-root">
      <div class="iam-header">
        <div>
          <h1 class="iam-title">📩 رسائل داخل التطبيق</h1>
          <p class="iam-sub">إدارة الرسائل التي تظهر في الصفحة الرئيسية للمستخدمين</p>
        </div>
        <button @click="showAdd = !showAdd" class="iam-add-btn">{{ showAdd ? '✕ إلغاء' : '+ إضافة رسالة' }}</button>
      </div>

      <!-- Add new message -->
      <div v-if="showAdd" class="iam-card iam-new">
        <div class="iam-card-header">📝 رسالة جديدة</div>
        <div class="iam-card-body">
          <!-- Templates -->
          <div class="iam-section">
            <div class="iam-section-label">📋 اختر نص جاهز</div>
            <div class="iam-templates">
              <button v-for="(tpl, i) in templates" :key="i" type="button" @click="selectTemplate(tpl)"
                :class="['iam-tpl', newMsg.message_ar === tpl.ar ? 'selected' : '']">
                {{ tpl.label }}
              </button>
            </div>
          </div>
          <div class="iam-fields">
            <div>
              <label class="iam-field-label">🇸🇾 الرسالة بالعربية</label>
              <textarea v-model="newMsg.message_ar" rows="2" class="iam-textarea" dir="rtl" placeholder="اكتب الرسالة بالعربية..."></textarea>
            </div>
            <div>
              <label class="iam-field-label">🇬🇧 Message in English</label>
              <textarea v-model="newMsg.message_en" rows="2" class="iam-textarea" dir="ltr" placeholder="Write English message..."></textarea>
            </div>
          </div>
          <button @click="addMessage" class="iam-save" :disabled="!newMsg.message_ar.trim()">💾 إضافة الرسالة</button>
        </div>
      </div>

      <!-- Messages list -->
      <div v-if="messages.length === 0 && !showAdd" class="iam-empty">
        <div class="iam-empty-icon">📭</div>
        <div class="iam-empty-text">لا توجد رسائل بعد</div>
        <button @click="showAdd = true" class="iam-add-btn mt-3">+ إضافة أول رسالة</button>
      </div>

      <div v-for="msg in messages" :key="msg.id" class="iam-card" :class="{'iam-inactive': !msg.active}">
        <div class="iam-msg-row">
          <div class="iam-msg-content">
            <template v-if="editId !== msg.id">
              <div class="iam-msg-text" dir="rtl">{{ msg.message_ar }}</div>
              <div v-if="msg.message_en" class="iam-msg-en" dir="ltr">{{ msg.message_en }}</div>
            </template>
            <template v-else>
              <textarea v-model="editForm.message_ar" rows="2" class="iam-textarea" dir="rtl"></textarea>
              <textarea v-model="editForm.message_en" rows="2" class="iam-textarea mt-2" dir="ltr"></textarea>
            </template>
          </div>
          <div class="iam-msg-actions">
            <button @click="toggleActive(msg)" :class="['iam-toggle', msg.active ? 'on' : '']" :title="msg.active ? 'تعطيل' : 'تفعيل'">
              <span class="iam-dot"></span>
            </button>
            <template v-if="editId !== msg.id">
              <button @click="startEdit(msg)" class="iam-icon-btn" title="تعديل">✏️</button>
              <button @click="deleteMessage(msg)" class="iam-icon-btn iam-delete" title="حذف">🗑️</button>
            </template>
            <template v-else>
              <button @click="saveEdit(msg)" class="iam-icon-btn" title="حفظ">✅</button>
              <button @click="cancelEdit" class="iam-icon-btn" title="إلغاء">❌</button>
            </template>
          </div>
        </div>
        <div class="iam-msg-meta">
          <span :class="['iam-badge', msg.active ? 'iam-badge-on' : 'iam-badge-off']">{{ msg.active ? '✅ مفعّلة' : '⏸️ متوقفة' }}</span>
          <span class="iam-date">{{ new Date(msg.created_at).toLocaleDateString('ar') }}</span>
        </div>
      </div>

      <div v-if="messages.filter(m => m.active).length > 0" class="iam-info">
        💡 {{ messages.filter(m => m.active).length }} رسالة مفعّلة — تظهر بالتناوب في التطبيق كل 4 ثواني
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.iam-root { direction: rtl; max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px; }
.iam-header { display: flex; justify-content: space-between; align-items: center; }
.iam-title { font-size: 22px; font-weight: 800; color: #0f172a; }
.iam-sub { font-size: 13px; color: #64748b; margin-top: 4px; }

.iam-add-btn { padding: 10px 20px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-radius: 12px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all .2s; }
.iam-add-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.3); }

.iam-card { background: #fff; border: 2px solid #e2e8f0; border-radius: 16px; overflow: hidden; transition: all .3s; }
.iam-inactive { opacity: .6; }
.iam-new { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.1); }

.iam-card-header { padding: 14px 20px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9; }
.iam-card-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }

.iam-section-label { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
.iam-templates { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.iam-tpl { text-align: right; padding: 8px 10px; border-radius: 10px; border: 2px solid #e2e8f0; font-size: 11px; font-weight: 600; color: #334155; background: #fff; cursor: pointer; transition: all .2s; }
.iam-tpl:hover { border-color: #10b981; background: #ecfdf5; }
.iam-tpl.selected { border-color: #10b981; background: #ecfdf5; color: #065f46; }

.iam-fields { display: flex; flex-direction: column; gap: 12px; }
.iam-field-label { font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block; }
.iam-textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; font-size: 13px; color: #0f172a; outline: none; resize: vertical; transition: border .2s; box-sizing: border-box; }
.iam-textarea:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.1); }

.iam-save { padding: 10px 24px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border-radius: 12px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; align-self: flex-start; transition: all .2s; }
.iam-save:hover { transform: translateY(-1px); }
.iam-save:disabled { opacity: .4; transform: none; cursor: not-allowed; }

.iam-msg-row { display: flex; gap: 16px; padding: 16px 20px; align-items: flex-start; }
.iam-msg-content { flex: 1; min-width: 0; }
.iam-msg-text { font-size: 14px; font-weight: 600; color: #0f172a; line-height: 1.6; }
.iam-msg-en { font-size: 12px; color: #64748b; margin-top: 6px; line-height: 1.5; }

.iam-msg-actions { display: flex; flex-direction: column; gap: 6px; align-items: center; flex-shrink: 0; }
.iam-icon-btn { background: none; border: none; cursor: pointer; font-size: 16px; padding: 4px; border-radius: 6px; transition: background .2s; }
.iam-icon-btn:hover { background: #f1f5f9; }
.iam-delete:hover { background: #fef2f2; }

.iam-toggle { width: 42px; height: 24px; border-radius: 12px; background: #e2e8f0; border: none; cursor: pointer; position: relative; transition: background .3s; }
.iam-toggle.on { background: #10b981; }
.iam-dot { width: 18px; height: 18px; border-radius: 50%; background: #fff; position: absolute; top: 3px; left: 3px; transition: transform .3s; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
.iam-toggle.on .iam-dot { transform: translateX(18px); }

.iam-msg-meta { display: flex; justify-content: space-between; padding: 8px 20px 12px; font-size: 11px; }
.iam-badge { padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 11px; }
.iam-badge-on { background: #ecfdf5; color: #059669; }
.iam-badge-off { background: #f1f5f9; color: #64748b; }
.iam-date { color: #94a3b8; }

.iam-empty { text-align: center; padding: 48px; background: #fff; border-radius: 16px; border: 2px dashed #e2e8f0; }
.iam-empty-icon { font-size: 48px; margin-bottom: 12px; }
.iam-empty-text { font-size: 15px; color: #64748b; font-weight: 600; }

.iam-info { padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; font-size: 13px; color: #1e40af; font-weight: 600; text-align: center; }

.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
</style>
