<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ customer: Object, accounts: Array, recentTransactions: Array, remittances: Array, tickets: Array, documents: Array, savingsGoals: Array, cryptoWallets: Array, staffNotes: Array });
const f = (n, s='€') => s + Number(n||0).toLocaleString('en', {minimumFractionDigits:2});
const statusLabel = s => ({active:'نشط',frozen:'مجمّد',suspended:'موقف'}[s]||s);
const kycLabel = s => ({verified:'✅ موثّق',pending:'⏳ قيد المراجعة',rejected:'❌ مرفوض',none:'لم يقدم'}[s]||s);
const txType = t => ({deposit:'إيداع',withdrawal:'سحب',transfer:'تحويل',fee:'رسوم',card_payment:'دفع بطاقة',exchange:'تبديل',savings_deposit:'إيداع ادخار',savings_withdrawal:'سحب ادخار',savings_goal_created:'إنشاء صندوق',savings_goal_closed:'إلغاء صندوق'}[t]||t);
const remStatus = s => ({ready:'جاهزة',collected:'مسلّمة',cancelled:'ملغية'}[s]||s);
const savingsStatus = s => ({active:'نشط',completed:'مكتمل',cancelled:'ملغي'}[s]||s);
const goalProgress = g => g.target_amount > 0 ? Math.min(100, Math.round((g.current_amount / g.target_amount) * 100)) : 0;
const fmtDate = d => d ? d.split(' ')[0] : '—';
const docLabel = t => ({passport:'رقم الجواز',id_card:'رقم الهوية',national_id:'رقم الهوية الوطنية',driving_license:'رقم رخصة القيادة'}[t]||'رقم الوثيقة');
const cryptoIcon = c => ({ETH:'⟠',BTC:'₿',USDC:'🔵',USDT:'🟢'}[c]||'🪙');
const truncAddr = a => a ? a.slice(0,8) + '...' + a.slice(-6) : '—';
const docType = t => ({passport:'جواز سفر',id_card:'بطاقة هوية',national_id:'هوية وطنية',driving_license:'رخصة قيادة'}[t]||t);
const kycDocType = t => ({id_front:'الهوية - أمامي',id_back:'الهوية - خلفي',passport:'جواز السفر',selfie:'صورة شخصية',proof_of_address:'إثبات عنوان',other:'أخرى'}[t]||t);
const kycDocStatus = s => ({approved:'✅ معتمد',pending:'⏳ قيد المراجعة',rejected:'❌ مرفوض'}[s]||s);

// Edit info (address, governorate, country only)
const showEdit = ref(false);
const editForm = useForm({
  address: p.customer.address || '',
  governorate: p.customer.governorate || '',
  country: p.customer.country || '',
});
const saveInfo = () => {
  editForm.patch(route('employee.customer.update-info', p.customer.id), {
    onSuccess: () => showEdit.value = false,
  });
};

// Request change (email/phone — needs admin approval)
const showChangeReq = ref(false);
const changeForm = useForm({ change_type: 'phone', new_value: '', reason: '' });
const submitChange = () => {
  changeForm.post(route('employee.customer.request-change', p.customer.id), {
    onSuccess: () => { showChangeReq.value = false; changeForm.reset(); },
  });
};

// Complaint
const showComplaint = ref(false);
const complaintTxId = ref(null);
const complaintForm = useForm({ subject: '', message: '', transaction_id: null });
const openComplaint = (txId = null) => {
  complaintTxId.value = txId;
  complaintForm.transaction_id = txId;
  complaintForm.subject = txId ? `شكوى على معاملة #${txId}` : '';
  complaintForm.message = '';
  showComplaint.value = true;
};
const submitComplaint = () => {
  complaintForm.post(route('employee.customer.complaint', p.customer.id), {
    onSuccess: () => { showComplaint.value = false; complaintForm.reset(); },
  });
};

// Account freeze
const toggleFreeze = (accId, action) => {
  if (!confirm(action === 'freeze' ? 'هل أنت متأكد من تجميد هذا الحساب؟' : 'هل تريد إلغاء تجميد هذا الحساب؟')) return;
  router.patch(route('employee.customer.toggle-freeze', p.customer.id), {
    account_id: accId, action
  });
};

// Document preview
const showDoc = ref(null);

// Staff Notes
const showNoteForm = ref(false);
const noteForm = useForm({ content: '', category: 'general' });
const noteCategories = {
  general: { label: 'عام', icon: '📝', color: '#64748b' },
  kyc: { label: 'KYC', icon: '🪪', color: '#0284c7' },
  support: { label: 'دعم', icon: '🎧', color: '#7c3aed' },
  risk: { label: 'مخاطر', icon: '⚠️', color: '#dc2626' },
  finance: { label: 'مالية', icon: '💰', color: '#059669' },
  compliance: { label: 'امتثال', icon: '🛡️', color: '#d97706' },
};
const submitNote = () => {
  noteForm.post(route('employee.customer.note', p.customer.id), {
    onSuccess: () => { showNoteForm.value = false; noteForm.reset(); },
  });
};
</script>
<template>
<Head :title="'عميل — ' + customer.full_name" />
<div class="cv">
  <Link :href="route('employee.search')" class="cv-back">← العودة للبحث</Link>

  <!-- Header -->
  <div class="cv-header">
    <div class="cv-profile">
      <div class="cv-avatar" v-if="customer.profile_photo"><img :src="customer.profile_photo" /></div>
      <div class="cv-avatar cv-init" v-else>{{ customer.full_name?.charAt(0) }}</div>
      <div>
        <h1 class="cv-name">{{ customer.full_name }}</h1>
        <p class="cv-name-ar" v-if="customer.full_name_ar">{{ customer.full_name_ar }}</p>
        <div class="cv-meta">
          <span>📧 {{ customer.email }}</span>
          <span v-if="customer.phone">📞 {{ customer.phone }}</span>
          <span v-if="customer.customer_number">🆔 {{ customer.customer_number }}</span>
        </div>
      </div>
    </div>
    <div class="cv-header-actions">
      <div class="cv-badges">
        <span class="cv-badge" :class="'cv-b-' + customer.status">{{ statusLabel(customer.status) }}</span>
        <span class="cv-badge cv-b-kyc">{{ kycLabel(customer.kyc_status) }}</span>
      </div>
      <div class="cv-action-btns">
        <button @click="showEdit = true" class="cv-act-btn cv-act-edit">✏️ تعديل العنوان</button>
        <button @click="showChangeReq = true" class="cv-act-btn cv-act-change">🔄 طلب تغيير هاتف/إيميل</button>
        <button @click="openComplaint()" class="cv-act-btn cv-act-ticket">📋 فتح شكوى</button>
      </div>
    </div>
  </div>

  <!-- Info Grid -->
  <div class="cv-sec">
    <h2 class="cv-sec-title">📋 معلومات العميل</h2>
    <div class="cv-info-grid">
      <div class="cv-info-item"><span class="cv-info-lbl">البلد</span><span>{{ customer.country || '—' }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">المحافظة</span><span>{{ customer.governorate || '—' }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">العنوان</span><span>{{ customer.address || '—' }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">الجنسية</span><span>{{ customer.nationality || '—' }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">تاريخ الميلاد</span><span>{{ fmtDate(customer.date_of_birth) }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">{{ docLabel(customer.document_type) }}</span><span>{{ customer.document_number || '—' }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">نوع الوثيقة</span><span>{{ docType(customer.document_type) || '—' }}</span></div>
      <div class="cv-info-item"><span class="cv-info-lbl">التسجيل</span><span>{{ fmtDate(customer.created_at) }}</span></div>
    </div>
  </div>

  <!-- Accounts with Freeze -->
  <div class="cv-sec">
    <h2 class="cv-sec-title">🏦 الحسابات</h2>
    <div class="cv-acc-grid">
      <div v-for="a in accounts" :key="a.id" class="cv-acc">
        <div class="cv-acc-top">
          <span class="cv-acc-curr">{{ a.currency_symbol }} {{ a.currency_code }}</span>
          <span class="cv-acc-default" v-if="a.is_default">⭐ رئيسي</span>
        </div>
        <div class="cv-acc-bal">{{ f(a.balance, a.currency_symbol) }}</div>
        <div class="cv-acc-num">{{ a.iban }}</div>
        <div class="cv-acc-bottom">
          <span class="cv-acc-status" :class="'cv-as-' + a.status">{{ a.status === 'active' ? '🟢 نشط' : a.status === 'frozen' ? '🔴 مجمّد' : a.status }}</span>
          <button v-if="a.status === 'active'" @click="toggleFreeze(a.id, 'freeze')" class="cv-freeze-btn cv-freeze">🧊 تجميد</button>
          <button v-else-if="a.status === 'frozen'" @click="toggleFreeze(a.id, 'unfreeze')" class="cv-freeze-btn cv-unfreeze">🔓 إلغاء التجميد</button>
        </div>
      </div>
      <div v-if="!accounts?.length" class="cv-empty">لا توجد حسابات</div>
    </div>
    <div class="cv-readonly-note">⚠️ عرض فقط — لا يمكن تعديل الأرصدة</div>
  </div>

  <!-- KYC Documents -->
  <div class="cv-sec">
    <h2 class="cv-sec-title">📄 الوثائق</h2>
    <div class="cv-doc-grid" v-if="documents?.length">
      <div v-for="d in documents" :key="d.id" class="cv-doc-card" @click="showDoc = d">
        <div class="cv-doc-type">{{ kycDocType(d.document_type) }}</div>
        <div class="cv-doc-file">📎 {{ d.original_filename || 'ملف' }}</div>
        <div class="cv-doc-meta">
          <span class="cv-badge-sm" :class="d.status === 'approved' ? 'cv-ts-completed' : d.status === 'pending' ? 'cv-ts-pending' : 'cv-ts-failed'">{{ kycDocStatus(d.status) }}</span>
          <span class="cv-doc-date">{{ fmtDate(d.created_at) }}</span>
        </div>
      </div>
    </div>
    <div v-else class="cv-empty">لا توجد وثائق</div>
  </div>

  <!-- Document Preview Modal -->
  <div v-if="showDoc" class="cv-overlay" @click.self="showDoc = null">
    <div class="cv-modal cv-modal-lg">
      <h3 class="cv-modal-title">📄 {{ kycDocType(showDoc.document_type) }}</h3>
      <p class="cv-modal-sub">{{ showDoc.original_filename }} — {{ kycDocStatus(showDoc.status) }}</p>
      <div class="cv-doc-preview">
        <img :src="'/employee/document/' + showDoc.id" alt="Document" />
      </div>
      <div v-if="showDoc.rejection_reason" class="cv-doc-reject">❌ سبب الرفض: {{ showDoc.rejection_reason }}</div>
      <div class="cv-mactions"><button @click="showDoc = null" class="cv-m-cancel">إغلاق</button></div>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="cv-sec" v-if="recentTransactions?.length">
    <h2 class="cv-sec-title">💳 آخر المعاملات</h2>
    <div class="cv-card">
      <table class="cv-tbl">
        <thead><tr><th>النوع</th><th>المبلغ</th><th>الحالة</th><th>الوصف</th><th>التاريخ</th><th>إجراء</th></tr></thead>
        <tbody>
          <tr v-for="t in recentTransactions" :key="t.id">
            <td>{{ txType(t.type) }}</td>
            <td class="cv-tbl-bold">{{ f(t.amount) }}</td>
            <td><span class="cv-badge-sm" :class="'cv-ts-' + t.status">{{ t.status }}</span></td>
            <td class="cv-tbl-desc">{{ t.description || '—' }}</td>
            <td class="cv-tbl-date">{{ fmtDate(t.created_at) }}</td>
            <td><button @click="openComplaint(t.id)" class="cv-complaint-btn" title="فتح شكوى">📋</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Remittances -->
  <div class="cv-sec" v-if="remittances?.length">
    <h2 class="cv-sec-title">💸 الحوالات</h2>
    <div class="cv-card">
      <table class="cv-tbl">
        <thead><tr><th>#</th><th>المستلم</th><th>الوكيل</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr></thead>
        <tbody>
          <tr v-for="r in remittances" :key="r.id">
            <td>{{ r.id }}</td>
            <td class="cv-tbl-bold">{{ r.recipient_name }}</td>
            <td>{{ r.agent_name || '—' }}</td>
            <td class="cv-tbl-bold">{{ f(r.amount) }}</td>
            <td><span class="cv-badge-sm" :class="'cv-rs-' + r.status">{{ remStatus(r.status) }}</span></td>
            <td class="cv-tbl-date">{{ fmtDate(r.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Savings Goals -->
  <div class="cv-sec">
    <h2 class="cv-sec-title">🏦 صناديق الادخار</h2>
    <div class="cv-savings-grid" v-if="savingsGoals?.length">
      <div v-for="g in savingsGoals" :key="g.id" class="cv-savings-card">
        <div class="cv-savings-top">
          <span class="cv-savings-emoji">{{ g.emoji || '🎯' }}</span>
          <div class="cv-savings-info">
            <span class="cv-savings-name">{{ g.name }}</span>
            <span class="cv-savings-currency">{{ g.currency_symbol || '€' }} {{ g.currency_code || 'EUR' }}</span>
          </div>
          <span class="cv-badge-sm" :class="'cv-sg-' + g.status">{{ savingsStatus(g.status) }}</span>
        </div>
        <div class="cv-savings-amounts">
          <span>{{ f(g.current_amount, g.currency_symbol || '€') }}</span>
          <span class="cv-savings-of">من</span>
          <span>{{ f(g.target_amount, g.currency_symbol || '€') }}</span>
        </div>
        <div class="cv-savings-bar">
          <div class="cv-savings-fill" :style="{width: goalProgress(g) + '%'}"></div>
        </div>
        <div class="cv-savings-bottom">
          <span class="cv-savings-pct">{{ goalProgress(g) }}%</span>
          <span class="cv-savings-date">{{ fmtDate(g.created_at) }}</span>
        </div>
      </div>
    </div>
    <div v-else class="cv-empty">لا توجد صناديق ادخار</div>
    <div class="cv-readonly-note">⚠️ عرض فقط — لا يمكن تعديل الصناديق</div>
  </div>

  <!-- Support Tickets -->
  <div class="cv-sec">
    <h2 class="cv-sec-title">🎫 التذاكر</h2>
    <div class="cv-list" v-if="tickets?.length">
      <Link v-for="t in tickets" :key="t.id" :href="route('employee.tickets.show', t.id)" class="cv-ticket">
        <span class="cv-ticket-subj">{{ t.subject }}</span>
        <span class="cv-badge-sm" :class="'cv-ts-' + t.status">{{ t.status }}</span>
      </Link>
    </div>
    <div v-else class="cv-empty">لا توجد تذاكر</div>
  </div>

  <!-- Crypto Wallets -->
  <div class="cv-sec">
    <h2 class="cv-sec-title">🪙 محافظ الكريبتو</h2>
    <div class="cv-list" v-if="cryptoWallets?.length">
      <div v-for="w in cryptoWallets" :key="w.id" class="cv-goal">
        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-size:22px">{{ cryptoIcon(w.currency) }}</span>
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:8px">
              <strong>{{ w.currency }}</strong>
              <span class="cv-badge-sm" style="background:#f0fdf4;color:#166534">{{ Number(w.balance).toFixed(w.currency==='ETH'?6:2) }}</span>
            </div>
            <div style="font-size:11px;color:#888;margin-top:2px;direction:ltr;text-align:left">
              <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:10px">{{ truncAddr(w.address) }}</code>
              · {{ fmtDate(w.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="cv-empty">لا توجد محافظ كريبتو</div>
  </div>

  <!-- ═══ Staff Notes (shared between employees + admin) ═══ -->
  <div class="cv-sec">
    <div class="cv-sec-header">
      <h2 class="cv-sec-title">📝 ملاحظات الموظفين</h2>
      <button @click="showNoteForm = !showNoteForm" class="cv-add-note-btn">{{ showNoteForm ? '✕ إغلاق' : '+ إضافة ملاحظة' }}</button>
    </div>

    <!-- Add Note Form -->
    <div v-if="showNoteForm" class="cv-note-form">
      <form @submit.prevent="submitNote">
        <div class="cv-note-cats">
          <button v-for="(cat, key) in noteCategories" :key="key" type="button"
            class="cv-note-cat" :class="{ 'cv-note-cat-active': noteForm.category === key }"
            :style="noteForm.category === key ? { background: cat.color + '15', borderColor: cat.color, color: cat.color } : {}"
            @click="noteForm.category = key">
            {{ cat.icon }} {{ cat.label }}
          </button>
        </div>
        <textarea v-model="noteForm.content" required rows="3" class="cv-note-input" placeholder="اكتب ملاحظتك هنا..."></textarea>
        <div class="cv-note-actions">
          <button type="submit" class="cv-m-ok" :disabled="noteForm.processing || !noteForm.content">
            {{ noteForm.processing ? 'جاري الحفظ...' : 'حفظ الملاحظة ✅' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Notes List -->
    <div class="cv-notes-list" v-if="staffNotes?.length">
      <div v-for="note in staffNotes" :key="note.id" class="cv-note-item" :class="{ 'cv-note-pinned': note.is_pinned }">
        <div class="cv-note-top">
          <div class="cv-note-author">
            <span class="cv-note-avatar">{{ note.admin?.full_name?.charAt(0) || 'M' }}</span>
            <div>
              <span class="cv-note-name">{{ note.admin?.full_name || 'موظف' }}</span>
              <span class="cv-note-role" v-if="note.admin?.employee_role">{{ note.admin.employee_role === 'admin' ? 'مدير' : note.admin.employee_role === 'supervisor' ? 'مشرف' : 'موظف' }}</span>
            </div>
          </div>
          <div class="cv-note-meta">
            <span class="cv-note-cat-badge" :style="{ background: (noteCategories[note.category]?.color || '#64748b') + '15', color: noteCategories[note.category]?.color || '#64748b' }">
              {{ noteCategories[note.category]?.icon || '📝' }} {{ noteCategories[note.category]?.label || note.category }}
            </span>
            <span v-if="note.is_pinned" class="cv-note-pin">📌</span>
            <span class="cv-note-date">{{ fmtDate(note.created_at) }}</span>
          </div>
        </div>
        <div class="cv-note-content">{{ note.content }}</div>
      </div>
    </div>
    <div v-else class="cv-empty">لا توجد ملاحظات بعد — كن أول من يضيف ملاحظة</div>
  </div>

  <!-- Edit Info Modal (address, governorate, country only) -->
  <div v-if="showEdit" class="cv-overlay" @click.self="showEdit = false">
    <div class="cv-modal">
      <h3 class="cv-modal-title">✏️ تعديل العنوان</h3>
      <p class="cv-modal-sub">يمكن تعديل: العنوان، المحافظة، البلد فقط<br>⚠️ لتغيير الهاتف/الإيميل → استخدم "طلب تغيير"</p>
      <form @submit.prevent="saveInfo" class="cv-mform">
        <div class="cv-mfield"><label>البلد</label><input v-model="editForm.country" placeholder="البلد..." /></div>
        <div class="cv-mfield"><label>العنوان</label><input v-model="editForm.address" placeholder="العنوان..." /></div>
        <div class="cv-mfield"><label>المحافظة</label><input v-model="editForm.governorate" placeholder="المحافظة..." /></div>
        <div class="cv-mactions">
          <button type="submit" class="cv-m-ok" :disabled="editForm.processing">{{ editForm.processing ? 'جاري الحفظ...' : 'حفظ التعديلات ✅' }}</button>
          <button type="button" @click="showEdit = false" class="cv-m-cancel">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Request Change Modal (phone/email — admin approval) -->
  <div v-if="showChangeReq" class="cv-overlay" @click.self="showChangeReq = false">
    <div class="cv-modal">
      <h3 class="cv-modal-title">🔄 طلب تغيير بيانات</h3>
      <p class="cv-modal-sub">تغيير الهاتف أو الإيميل يحتاج موافقة الإدارة</p>
      <form @submit.prevent="submitChange" class="cv-mform">
        <div class="cv-mfield">
          <label>نوع التغيير</label>
          <select v-model="changeForm.change_type" class="cv-select">
            <option value="phone">📞 رقم الهاتف</option>
            <option value="email">📧 البريد الإلكتروني</option>
          </select>
        </div>
        <div class="cv-mfield">
          <label>القيمة الحالية</label>
          <input readonly :value="changeForm.change_type === 'email' ? customer.email : customer.phone" class="cv-readonly-input" />
        </div>
        <div class="cv-mfield"><label>القيمة الجديدة</label><input v-model="changeForm.new_value" required :placeholder="changeForm.change_type === 'email' ? 'email@example.com' : '+45...'" /></div>
        <div class="cv-mfield"><label>السبب</label><textarea v-model="changeForm.reason" required rows="2" placeholder="سبب التغيير..."></textarea></div>
        <div class="cv-mactions">
          <button type="submit" class="cv-m-ok cv-m-warn" :disabled="changeForm.processing">{{ changeForm.processing ? 'جاري الإرسال...' : 'إرسال للإدارة 🔄' }}</button>
          <button type="button" @click="showChangeReq = false" class="cv-m-cancel">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Complaint Modal -->
  <div v-if="showComplaint" class="cv-overlay" @click.self="showComplaint = false">
    <div class="cv-modal">
      <h3 class="cv-modal-title">📋 فتح شكوى للعميل</h3>
      <p class="cv-modal-sub" v-if="complaintTxId">شكوى على معاملة رقم #{{ complaintTxId }}</p>
      <p class="cv-modal-sub" v-else>سيتم إرسال إشعار بالشكوى للعميل والإدارة</p>
      <form @submit.prevent="submitComplaint" class="cv-mform">
        <div class="cv-mfield"><label>عنوان الشكوى</label><input v-model="complaintForm.subject" required placeholder="مثال: مشكلة في حوالة..." /></div>
        <div class="cv-mfield"><label>تفاصيل الشكوى</label><textarea v-model="complaintForm.message" required rows="4" placeholder="اشرح المشكلة..."></textarea></div>
        <div class="cv-mactions">
          <button type="submit" class="cv-m-ok" :disabled="complaintForm.processing">{{ complaintForm.processing ? 'جاري الإرسال...' : 'إرسال الشكوى 📨' }}</button>
          <button type="button" @click="showComplaint = false" class="cv-m-cancel">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>
</template>
<style scoped>
.cv{direction:rtl;max-width:1100px;margin:0 auto}
.cv-back{font-size:13px;color:#64748b;text-decoration:none;font-weight:600;display:inline-block;margin-bottom:12px}.cv-back:hover{color:#2563eb}
.cv-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.cv-profile{display:flex;gap:16px;align-items:center}
.cv-avatar{width:64px;height:64px;border-radius:16px;overflow:hidden;flex-shrink:0}
.cv-avatar img{width:100%;height:100%;object-fit:cover}
.cv-init{background:linear-gradient(135deg,#dbeafe,#93c5fd);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#1d4ed8}
.cv-name{font-size:22px;font-weight:900;color:#0f172a;margin:0}
.cv-name-ar{font-size:13px;color:#94a3b8;margin:2px 0}
.cv-meta{display:flex;gap:14px;font-size:12px;color:#64748b;flex-wrap:wrap}
.cv-header-actions{display:flex;flex-direction:column;gap:8px;align-items:flex-end}
.cv-badges{display:flex;gap:6px}
.cv-action-btns{display:flex;gap:6px;flex-wrap:wrap}
.cv-act-btn{padding:8px 14px;border:none;border-radius:10px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.cv-act-btn:hover{transform:translateY(-1px)}
.cv-act-edit{background:#dbeafe;color:#1d4ed8}
.cv-act-change{background:#f0fdf4;color:#166534}
.cv-act-ticket{background:#fef3c7;color:#92400e}
.cv-badge{font-size:12px;font-weight:700;padding:6px 14px;border-radius:10px}
.cv-b-active{background:#ecfdf5;color:#059669}.cv-b-frozen{background:#fef2f2;color:#dc2626}.cv-b-suspended{background:#fef3c7;color:#d97706}
.cv-b-kyc{background:#f0f9ff;color:#0284c7}
.cv-sec{margin-bottom:24px}
.cv-sec-title{font-size:15px;font-weight:800;color:#1e293b;margin:0 0 12px;padding-bottom:8px;border-bottom:1px solid #f1f5f9}
.cv-info-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.cv-info-item{background:#fff;border:1px solid #e8ebf0;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:4px}
.cv-info-lbl{font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase}
.cv-info-item span:last-child{font-size:13px;font-weight:700;color:#0f172a}
.cv-acc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
.cv-acc{background:#fff;border:1px solid #e8ebf0;border-radius:14px;padding:16px}
.cv-acc-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.cv-acc-curr{font-size:14px;font-weight:800;color:#2563eb}
.cv-acc-default{font-size:10px;font-weight:700;color:#d97706}
.cv-acc-bal{font-size:22px;font-weight:900;color:#0f172a}
.cv-acc-num{font-size:11px;color:#94a3b8;margin-top:4px;font-family:monospace}
.cv-acc-bottom{display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9}
.cv-acc-status{font-size:11px;font-weight:700}
.cv-as-active{color:#059669}.cv-as-frozen{color:#dc2626}
.cv-freeze-btn{padding:4px 10px;border:none;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.cv-freeze{background:#fef2f2;color:#dc2626}.cv-freeze:hover{background:#fee2e2}
.cv-unfreeze{background:#ecfdf5;color:#059669}.cv-unfreeze:hover{background:#d1fae5}
.cv-readonly-note{margin-top:10px;padding:8px 14px;background:#fef3c7;border:1px solid #fde68a;border-radius:10px;font-size:12px;font-weight:600;color:#92400e}
/* Documents */
.cv-doc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px}
.cv-doc-card{background:#fff;border:1px solid #e8ebf0;border-radius:12px;padding:14px;cursor:pointer;transition:all .15s}
.cv-doc-card:hover{border-color:#2563eb;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.cv-doc-type{font-size:13px;font-weight:800;color:#0f172a;margin-bottom:4px}
.cv-doc-file{font-size:11px;color:#64748b;margin-bottom:6px}
.cv-doc-meta{display:flex;justify-content:space-between;align-items:center}
.cv-doc-date{font-size:10px;color:#94a3b8}
.cv-doc-preview{margin:16px 0;text-align:center;max-height:400px;overflow:auto;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc}
.cv-doc-preview img{max-width:100%;height:auto}
.cv-doc-reject{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 14px;font-size:12px;color:#dc2626;font-weight:600;margin-bottom:10px}
.cv-modal-lg{max-width:640px}
/* Tables */
.cv-card{background:#fff;border:1px solid #e8ebf0;border-radius:14px;padding:14px;overflow-x:auto}
.cv-tbl{width:100%;border-collapse:collapse;min-width:600px}
.cv-tbl th{font-size:11px;font-weight:700;color:#94a3b8;text-align:right;padding:8px 10px;border-bottom:2px solid #e8ebf0}
.cv-tbl td{font-size:13px;color:#334155;padding:8px 10px;border-bottom:1px solid #f1f5f9}
.cv-tbl-bold{font-weight:700;color:#0f172a}
.cv-tbl-desc{font-size:11px;color:#64748b;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cv-tbl-date{font-size:11px;color:#94a3b8}
.cv-badge-sm{font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px}
.cv-ts-completed,.cv-ts-resolved,.cv-rs-collected{background:#ecfdf5;color:#059669}
.cv-ts-pending,.cv-ts-open,.cv-rs-ready{background:#fef3c7;color:#d97706}
.cv-ts-failed,.cv-ts-closed,.cv-rs-cancelled{background:#fef2f2;color:#dc2626}
.cv-ts-in_progress{background:#dbeafe;color:#2563eb}
.cv-complaint-btn{width:28px;height:28px;border:1px solid #fde68a;background:#fffbeb;border-radius:6px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.cv-complaint-btn:hover{transform:scale(1.1);background:#fef3c7}
.cv-list{display:flex;flex-direction:column;gap:6px}
.cv-ticket{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#fff;border:1px solid #e8ebf0;border-radius:10px;text-decoration:none;transition:all .15s}
.cv-ticket:hover{border-color:#2563eb}
.cv-ticket-subj{font-size:13px;font-weight:600;color:#0f172a}
.cv-empty{text-align:center;color:#cbd5e1;font-size:13px;padding:20px;background:#fff;border:1px solid #e8ebf0;border-radius:14px}
/* Modals */
.cv-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;z-index:999}
.cv-modal{background:#fff;border-radius:20px;padding:28px;min-width:420px;max-width:520px;direction:rtl}
.cv-modal-title{font-size:18px;font-weight:900;color:#0f172a;margin:0 0 4px}
.cv-modal-sub{font-size:12px;color:#64748b;margin-bottom:16px;line-height:1.6}
.cv-mform{display:flex;flex-direction:column;gap:12px}
.cv-mfield{display:flex;flex-direction:column;gap:4px}
.cv-mfield label{font-size:12px;font-weight:700;color:#64748b}
.cv-mfield input,.cv-mfield textarea,.cv-select{padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;transition:border .2s;font-family:inherit;resize:vertical;background:#fff}
.cv-mfield input:focus,.cv-mfield textarea:focus,.cv-select:focus{border-color:#2563eb}
.cv-readonly-input{background:#f8fafc !important;color:#94a3b8 !important}
.cv-select{appearance:none;cursor:pointer}
.cv-mactions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
.cv-m-ok{padding:10px 24px;background:#10481A;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit}
.cv-m-ok:disabled{opacity:.5;cursor:not-allowed}
.cv-m-warn{background:#d97706}
.cv-m-cancel{padding:10px 24px;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit}
/* Savings Goals */
.cv-savings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
.cv-savings-card{background:#fff;border:1px solid #e8ebf0;border-radius:14px;padding:16px}
.cv-savings-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.cv-savings-emoji{font-size:28px;width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:#f8fafc;border-radius:12px}
.cv-savings-info{flex:1;display:flex;flex-direction:column}
.cv-savings-name{font-size:14px;font-weight:800;color:#0f172a}
.cv-savings-currency{font-size:11px;font-weight:600;color:#64748b}
.cv-savings-amounts{display:flex;align-items:center;gap:6px;font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px}
.cv-savings-of{font-size:11px;font-weight:500;color:#94a3b8}
.cv-savings-bar{width:100%;height:8px;background:#f1f5f9;border-radius:4px;overflow:hidden}
.cv-savings-fill{height:100%;background:linear-gradient(90deg,#059669,#34d399);border-radius:4px;transition:width .5s ease}
.cv-savings-bottom{display:flex;justify-content:space-between;align-items:center;margin-top:6px}
.cv-savings-pct{font-size:12px;font-weight:800;color:#059669}
.cv-savings-date{font-size:10px;color:#94a3b8}
.cv-sg-active{background:#ecfdf5;color:#059669}
.cv-sg-completed{background:#dbeafe;color:#2563eb}
.cv-sg-cancelled{background:#fef2f2;color:#dc2626}
@media(max-width:900px){.cv-info-grid{grid-template-columns:repeat(2,1fr)}.cv-doc-grid{grid-template-columns:repeat(2,1fr)}.cv-savings-grid{grid-template-columns:1fr}}
/* Staff Notes */
.cv-sec-header{display:flex;justify-content:space-between;align-items:center}
.cv-add-note-btn{padding:6px 14px;background:#10481A;color:#fff;border:none;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.cv-add-note-btn:hover{background:#0d3a15}
.cv-note-form{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:12px}
.cv-note-cats{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}
.cv-note-cat{padding:4px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;background:#fff;color:#64748b;font-family:inherit;transition:all .15s}
.cv-note-cat:hover{border-color:#94a3b8}
.cv-note-cat-active{font-weight:800}
.cv-note-input{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;resize:vertical;outline:none;transition:border .2s;direction:rtl;box-sizing:border-box}
.cv-note-input:focus{border-color:#10481A}
.cv-note-actions{display:flex;justify-content:flex-end;margin-top:8px}
.cv-notes-list{display:flex;flex-direction:column;gap:8px}
.cv-note-item{background:#fff;border:1px solid #e8ebf0;border-radius:12px;padding:14px}
.cv-note-pinned{border-color:#fbbf24;background:#fffbeb}
.cv-note-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.cv-note-author{display:flex;align-items:center;gap:8px}
.cv-note-avatar{width:28px;height:28px;border-radius:8px;background:#10481A;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0}
.cv-note-name{font-size:12px;font-weight:700;color:#0f172a}
.cv-note-role{font-size:10px;color:#64748b;margin-right:6px}
.cv-note-meta{display:flex;align-items:center;gap:6px}
.cv-note-cat-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px}
.cv-note-pin{font-size:12px}
.cv-note-date{font-size:10px;color:#94a3b8}
.cv-note-content{font-size:13px;color:#334155;line-height:1.7;white-space:pre-wrap}
</style>
