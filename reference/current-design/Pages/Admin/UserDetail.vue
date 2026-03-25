<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed, watch, nextTick, onMounted } from 'vue';

const props = defineProps({ user: Object, accounts: Array, cards: Array, kycDocuments: Array, transactions: Array, cardTransactions: Array, totalBalance: Number, loginHistory: Array, adminNotes: Array, userLimits: Array, globalLimits: Array });
const flash = computed(() => usePage().props.flash || {});

const fmt = (a, s = '€') => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(a) + ' ' + s;
const fmtDate = (d) => new Date(d).toLocaleDateString('ar-EG', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
const fmtShort = (d) => new Date(d).toLocaleDateString('ar-EG', { day: 'numeric', month: 'short', year: 'numeric' });

// Status & KYC forms
const statusForm = useForm({ status: props.user.status });
const kycForm = useForm({ kyc_status: props.user.kyc_status });
const updateStatus = () => statusForm.patch(route('admin.users.status', props.user.id));
const updateKyc = () => kycForm.patch(route('admin.users.kyc', props.user.id));

// Profile edit form
const showEditProfile = ref(false);
const profileForm = useForm({
  full_name: props.user.full_name, email: props.user.email, phone: props.user.phone || '',
  nationality: props.user.nationality || '', address: props.user.address || '',
  city: props.user.city || '', country: props.user.country || '',
  governorate: props.user.governorate || '', employment: props.user.employment || '',
  date_of_birth: props.user.date_of_birth || '', postal_code: props.user.postal_code || '',
});
const saveProfile = () => profileForm.patch(route('admin.users.update-profile', props.user.id), { onSuccess: () => showEditProfile.value = false });

// Countries list (matching preregister page)
const countries = [
  {code:'SY',ar:'سوريا 🇸🇾'},{code:'TR',ar:'تركيا 🇹🇷'},{code:'LB',ar:'لبنان 🇱🇧'},{code:'JO',ar:'الأردن 🇯🇴'},
  {code:'IQ',ar:'العراق 🇮🇶'},{code:'EG',ar:'مصر 🇪🇬'},{code:'SA',ar:'السعودية 🇸🇦'},{code:'AE',ar:'الإمارات 🇦🇪'},
  {code:'DE',ar:'ألمانيا 🇩🇪'},{code:'SE',ar:'السويد 🇸🇪'},{code:'NL',ar:'هولندا 🇳🇱'},{code:'DK',ar:'الدنمارك 🇩🇰'},
  {code:'NO',ar:'النرويج 🇳🇴'},{code:'FR',ar:'فرنسا 🇫🇷'},{code:'GB',ar:'بريطانيا 🇬🇧'},{code:'US',ar:'أمريكا 🇺🇸'},
  {code:'CA',ar:'كندا 🇨🇦'},{code:'AU',ar:'أستراليا 🇦🇺'},{code:'OTHER',ar:'دولة أخرى 🌍'},
];
const governorates = [
  {en:'Damascus',ar:'دمشق'},{en:'Aleppo',ar:'حلب'},{en:'Homs',ar:'حمص'},{en:'Hama',ar:'حماة'},
  {en:'Latakia',ar:'اللاذقية'},{en:'Tartus',ar:'طرطوس'},{en:'Idlib',ar:'إدلب'},{en:'Deir ez-Zor',ar:'دير الزور'},
  {en:'Raqqa',ar:'الرقة'},{en:'Hasakah',ar:'الحسكة'},{en:'Daraa',ar:'درعا'},{en:'Sweida',ar:'السويداء'},
  {en:'Quneitra',ar:'القنيطرة'},{en:'Rural Damascus',ar:'ريف دمشق'},
];
const employmentOptions = [
  {v:'employed',l:'موظف'},{v:'self_employed',l:'عمل حر'},{v:'student',l:'طالب'},
  {v:'unemployed',l:'غير موظف'},{v:'retired',l:'متقاعد'},{v:'other',l:'أخرى'},
];
const isSyria = computed(() => profileForm.country === 'SY');
const getCountryName = (code) => countries.find(c => c.code === code)?.ar || code || '—';
const getGovName = (en) => governorates.find(g => g.en === en)?.ar || en || '—';
const getEmpName = (v) => employmentOptions.find(e => e.v === v)?.l || v || '—';

// Admin actions
const resetPassword = () => { if (confirm('هل أنت متأكد من إعادة تعيين كلمة المرور؟')) router.post(route('admin.users.reset-password', props.user.id)); };
const freezeAll = () => { if (confirm('تجميد جميع الحسابات والبطاقات؟')) router.post(route('admin.users.freeze-all', props.user.id)); };
const unfreezeAll = () => router.post(route('admin.users.unfreeze-all', props.user.id));

// Send notification
const showSendNote = ref(false);
const noteForm = useForm({ note: '' });
const sendNote = () => noteForm.post(route('admin.users.send-note', props.user.id), { onSuccess: () => { showSendNote.value = false; noteForm.reset(); } });

// Internal notes
const adminNoteForm = useForm({ content: '', category: 'general' });
const addAdminNote = () => adminNoteForm.post(route('admin.users.add-note', props.user.id), { onSuccess: () => adminNoteForm.reset(), preserveScroll: true });
const deleteNote = (id) => { if (confirm('حذف الملاحظة؟')) router.delete(route('admin.notes.delete', id), { preserveScroll: true }); };
const togglePin = (id) => router.patch(route('admin.notes.pin', id), {}, { preserveScroll: true });
const catLabels = { general: '📝 عامة', kyc: '🪪 KYC', support: '🎧 دعم', risk: '🛡️ مخاطر' };
const catColors = { general: '#64748b', kyc: '#3b82f6', support: '#8b5cf6', risk: '#ef4444' };

// ──── Limits Management ────
const userLimitTypes = [
  { type: 'daily_transfer', label: 'حد التحويل اليومي', icon: '📤' },
  { type: 'monthly_transfer', label: 'حد التحويل الشهري', icon: '📅' },
  { type: 'single_transaction', label: 'حد العملية الواحدة', icon: '💰' },
  { type: 'daily_withdrawal', label: 'حد السحب اليومي', icon: '🏧' },
];
const syriaLimitTypes = [
  { type: 'syria_remittance_daily', label: 'حوالات سوريا — حد يومي', icon: '🇸🇾' },
  { type: 'syria_remittance_monthly', label: 'حوالات سوريا — حد شهري', icon: '🇸🇾' },
];

// Build editable limit objects from existing data
const buildLimitData = (limitTypes) => limitTypes.map(lt => {
  const existing = (props.userLimits || []).find(l => l.limit_type === lt.type);
  return { limit_type: lt.type, label: lt.label, icon: lt.icon, amount: existing ? parseFloat(existing.amount) : 0, is_active: existing ? existing.is_active : false, currency: existing?.currency || 'EUR' };
});
const editUserLimits = ref(buildLimitData(userLimitTypes));
const editSyriaLimits = ref(buildLimitData(syriaLimitTypes));
const limitsProcessing = ref(false);

const saveUserLimits = () => {
  limitsProcessing.value = true;
  const allLimits = [...editUserLimits.value, ...editSyriaLimits.value].filter(l => l.is_active);
  router.post(route('admin.users.limits', props.user.id), { limits: allLimits }, {
    preserveScroll: true,
    onFinish: () => limitsProcessing.value = false,
  });
};

// Per-wallet (account) limits
const editAccountLimits = ref((props.accounts || []).map(acc => ({
  id: acc.id, account_number: acc.account_number, currency_code: acc.currency?.code || '?', currency_symbol: acc.currency?.symbol || '€',
  daily_transfer_limit: acc.daily_transfer_limit ?? '', monthly_transfer_limit: acc.monthly_transfer_limit ?? '',
  single_transaction_limit: acc.single_transaction_limit ?? '', daily_withdrawal_limit: acc.daily_withdrawal_limit ?? '',
})));
const walletProcessing = ref({});
const saveAccountLimit = (acc) => {
  walletProcessing.value[acc.id] = true;
  router.post(route('admin.accounts.limits', acc.id), {
    daily_transfer_limit: acc.daily_transfer_limit === '' ? null : acc.daily_transfer_limit,
    monthly_transfer_limit: acc.monthly_transfer_limit === '' ? null : acc.monthly_transfer_limit,
    single_transaction_limit: acc.single_transaction_limit === '' ? null : acc.single_transaction_limit,
    daily_withdrawal_limit: acc.daily_withdrawal_limit === '' ? null : acc.daily_withdrawal_limit,
  }, {
    preserveScroll: true,
    onFinish: () => walletProcessing.value[acc.id] = false,
  });
};

// Veriff media
const veriffMedia = ref([]);
const veriffDecision = ref(null);
const veriffLoading = ref(false);
const veriffError = ref('');

const loadVeriffMedia = async () => {
  if (!props.user.veriff_session_id) return;
  veriffLoading.value = true;
  veriffError.value = '';
  try {
    const res = await fetch(`/admin/veriff/user/${props.user.id}/media`);
    const data = await res.json();
    veriffMedia.value = data.media || [];
    veriffDecision.value = data.decision || null;
  } catch (e) {
    veriffError.value = 'خطأ في تحميل بيانات Veriff';
    console.error(e);
  }
  veriffLoading.value = false;
};

onMounted(() => {
  if (props.user.veriff_session_id) loadVeriffMedia();
});

const veriffStatusLabel = (code) => {
  const labels = { 9001: '✅ مقبول', 9102: '❌ مرفوض', 9103: '🔄 إعادة تقديم', 9104: '⏰ منتهي' };
  return labels[code] || `كود: ${code}`;
};

const getGlobalLimit = (type) => {
  const kycLevel = props.user.kyc_status === 'verified' ? 'verified' : 'basic';
  const gl = (props.globalLimits || []).find(l => l.kyc_level === kycLevel && l.limit_type === type);
  return gl ? Number(gl.amount).toLocaleString('en') + ' €' : '—';
};

const activeTab = ref('overview');
const tabs = [
  { id: 'overview', label: 'نظرة عامة', icon: '📊' },
  { id: 'accounts', label: 'الحسابات', icon: '🏦' },
  { id: 'cards', label: 'البطاقات', icon: '💳' },
  { id: 'transactions', label: 'المعاملات', icon: '💸' },
  { id: 'purchases', label: 'المشتريات', icon: '🛒' },
  { id: 'limits', label: 'الحدود والقيود', icon: '📊' },
  { id: 'kyc', label: 'KYC', icon: '🪪' },
  { id: 'security', label: 'الأمان', icon: '🔒' },
  { id: 'notes', label: 'ملاحظات', icon: '📝' },
  { id: 'actions', label: 'تحكم إداري', icon: '⚡' },
];

const statusBadge = { active: 'ud-badge-green', pending: 'ud-badge-yellow', suspended: 'ud-badge-red', blocked: 'ud-badge-red', verified: 'ud-badge-green', submitted: 'ud-badge-yellow', rejected: 'ud-badge-red', completed: 'ud-badge-green', failed: 'ud-badge-red', frozen: 'ud-badge-blue' };
const typeLabels = { transfer: 'تحويل', deposit: 'إيداع', withdrawal: 'سحب', exchange: 'صرف عملات', card_payment: 'دفع بالبطاقة', fee: 'رسوم', refund: 'استرداد' };
const typeIcons = { transfer: '↗️', deposit: '💰', withdrawal: '📤', exchange: '💱', card_payment: '💳', fee: '📋', refund: '↩️' };

const totalIn = computed(() => props.transactions.filter(t => props.accounts.some(a => a.id === t.to_account_id)).reduce((s, t) => s + parseFloat(t.amount || 0), 0));
const totalOut = computed(() => props.transactions.filter(t => props.accounts.some(a => a.id === t.from_account_id)).reduce((s, t) => s + parseFloat(t.amount || 0), 0));
</script>

<template>
  <Head :title="`${user.full_name} - تفاصيل العميل`" />
  <AdminLayout title="👤 تفاصيل العميل">
    <div class="ud-root">
      <!-- HEADER -->
      <div class="ud-header">
        <div class="max-w-7xl mx-auto px-6 py-6">
          <div class="flex items-center gap-3 mb-4">
            <Link :href="route('admin.users')" class="ud-back">← العودة للعملاء</Link>
          </div>
          <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-center gap-5">
              <div class="ud-avatar-lg">{{ user.full_name?.charAt(0) }}</div>
              <div>
                <h1 class="text-2xl font-bold text-[#0f172a]">{{ user.full_name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-sm text-[#475569] flex-wrap">
                  <span class="font-mono bg-[#F0F4FF] text-[#1E5EFF] px-3 py-0.5 rounded-lg text-xs font-bold border border-[#1E5EFF]/10">رقم العميل: {{ user.customer_number }}</span>
                  <span>📧 {{ user.email }}</span>
                  <span>📱 {{ user.phone }}</span>
                </div>
                <div class="flex items-center gap-2 mt-2">
                  <span :class="statusBadge[user.status]" class="ud-badge">{{ user.status }}</span>
                  <span :class="statusBadge[user.kyc_status]" class="ud-badge">KYC: {{ user.kyc_status }}</span>
                  <span class="ud-badge ud-badge-gray">انضم {{ fmtShort(user.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="ud-balance-card">
              <div class="text-xs text-[#475569]">الرصيد الإجمالي (EUR)</div>
              <div class="text-3xl font-black text-[#1E5EFF]">{{ fmt(totalBalance) }}</div>
              <div class="flex gap-3 mt-2 text-xs">
                <span class="text-emerald-600">↓ وارد: {{ fmt(totalIn) }}</span>
                <span class="text-red-500">↑ صادر: {{ fmt(totalOut) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FLASH -->
      <div v-if="flash.success" class="max-w-7xl mx-auto px-6 mt-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-emerald-700 text-sm">✓ {{ flash.success }}</div>
      </div>

      <!-- TABS -->
      <div class="max-w-7xl mx-auto px-6 mt-6">
        <div class="ud-tabs">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" class="ud-tab" :class="{ 'ud-tab-active': activeTab === tab.id }">
            <span>{{ tab.icon }}</span> {{ tab.label }}
          </button>
        </div>
      </div>

      <!-- CONTENT -->
      <div class="max-w-7xl mx-auto px-6 py-6 space-y-6">

        <!-- OVERVIEW TAB -->
        <template v-if="activeTab === 'overview'">
          <div class="grid lg:grid-cols-3 gap-4">
            <div class="ud-card"><h3 class="ud-card-title mb-4">إحصائيات العميل</h3><div class="space-y-3"><div class="ud-info-row"><span>الحسابات</span><span class="font-bold text-[#0f172a]">{{ accounts.length }}</span></div><div class="ud-info-row"><span>البطاقات</span><span class="font-bold text-[#0f172a]">{{ cards.length }}</span></div><div class="ud-info-row"><span>المعاملات</span><span class="font-bold text-[#0f172a]">{{ transactions.length }}</span></div><div class="ud-info-row"><span>إجمالي الوارد</span><span class="font-bold text-emerald-600">{{ fmt(totalIn) }}</span></div><div class="ud-info-row"><span>إجمالي الصادر</span><span class="font-bold text-red-500">{{ fmt(totalOut) }}</span></div></div></div>
            <div class="ud-card"><h3 class="ud-card-title mb-4">المعلومات الشخصية</h3><div class="space-y-3">
              <div class="ud-info-row"><span>الاسم</span><span class="text-[#0f172a] font-semibold">{{ user.full_name }}</span></div>
              <div class="ud-info-row"><span>البريد</span><span class="text-[#0f172a]">{{ user.email }}</span></div>
              <div class="ud-info-row"><span>الهاتف</span><span class="text-[#0f172a] font-mono">{{ user.phone }}</span></div>
              <div class="ud-info-row"><span>الجنسية</span><span class="text-[#0f172a]">{{ user.nationality || '—' }}</span></div>
              <div class="ud-info-row"><span>تاريخ الميلاد</span><span class="text-[#0f172a]">{{ user.date_of_birth || '—' }}</span></div>
              <div class="ud-info-row"><span>🌍 الدولة</span><span class="text-[#0f172a] font-semibold">{{ getCountryName(user.country) }}</span></div>
              <div class="ud-info-row"><span>🏙️ المحافظة</span><span class="text-[#0f172a]">{{ getGovName(user.governorate) }}</span></div>
              <div class="ud-info-row"><span>📍 المدينة</span><span class="text-[#0f172a]">{{ user.city || '—' }}</span></div>
              <div class="ud-info-row"><span>📮 العنوان</span><span class="text-[#0f172a]">{{ user.address || '—' }}</span></div>
              <div class="ud-info-row"><span>📬 الرمز البريدي</span><span class="text-[#0f172a] font-mono">{{ user.postal_code || '—' }}</span></div>
              <div class="ud-info-row"><span>💼 الحالة الاجتماعية</span><span class="text-[#0f172a] font-semibold">{{ getEmpName(user.employment) }}</span></div>
            </div></div>
            <!-- Quick Admin Actions -->
            <div class="ud-card">
              <h3 class="ud-card-title mb-4">⚡ تحكم سريع</h3>
              <div class="space-y-3">
                <div><label class="text-xs text-[#475569] font-medium block mb-1">حالة الحساب</label><div class="flex gap-2"><select v-model="statusForm.status" class="ud-select flex-1"><option value="pending">معلّق</option><option value="active">نشط</option><option value="suspended">موقوف</option><option value="blocked">محظور</option></select><button @click="updateStatus" :disabled="statusForm.processing" class="ud-btn-blue">حفظ</button></div></div>
                <div><label class="text-xs text-[#475569] font-medium block mb-1">حالة KYC</label><div class="flex gap-2"><select v-model="kycForm.kyc_status" class="ud-select flex-1"><option value="pending">معلّق</option><option value="submitted">مقدّم</option><option value="verified">مُوثّق</option><option value="rejected">مرفوض</option></select><button @click="updateKyc" :disabled="kycForm.processing" class="ud-btn-green">حفظ</button></div></div>
                <hr class="border-gray-200">
                <button @click="showEditProfile = true" class="ud-action-btn w-full">✏️ تعديل بيانات العميل</button>
                <button @click="resetPassword" class="ud-action-btn ud-action-warn w-full">🔑 إعادة تعيين كلمة المرور</button>
                <button @click="showSendNote = true" class="ud-action-btn ud-action-info w-full">📩 إرسال إشعار للعميل</button>
                <div class="flex gap-2">
                  <button @click="freezeAll" class="ud-action-btn ud-action-danger flex-1">❄️ تجميد الكل</button>
                  <button @click="unfreezeAll" class="ud-action-btn ud-action-success flex-1">✅ إلغاء التجميد</button>
                </div>
              </div>
            </div>
          </div>
          <!-- Recent activity -->
          <div class="ud-card">
            <h3 class="ud-card-title mb-4">آخر 5 معاملات</h3>
            <div class="ud-table">
              <div v-for="t in transactions.slice(0, 5)" :key="t.id" class="ud-table-row">
                <div class="flex items-center gap-3"><div class="ud-tx-icon">{{ typeIcons[t.type] || '📄' }}</div><div><div class="text-sm font-semibold text-[#0f172a]">{{ typeLabels[t.type] || t.type }}</div><div class="text-xs text-[#475569]">{{ t.reference_number }}</div></div></div>
                <div class="text-sm text-[#475569]">{{ fmtDate(t.created_at) }}</div>
                <div class="text-sm font-bold text-[#0f172a]">{{ fmt(t.amount, t.currency?.symbol) }}</div>
                <span :class="statusBadge[t.status]" class="ud-badge">{{ t.status }}</span>
              </div>
              <div v-if="!transactions.length" class="py-8 text-center text-[#475569]">لا توجد معاملات</div>
            </div>
          </div>
        </template>

        <!-- ACCOUNTS TAB -->
        <template v-if="activeTab === 'accounts'">
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="acc in accounts" :key="acc.id" class="ud-card ud-account-card">
              <div class="flex items-center justify-between mb-3"><div class="text-3xl">{{ acc.currency?.symbol }}</div><span :class="statusBadge[acc.status]" class="ud-badge">{{ acc.status }}</span></div>
              <div class="text-2xl font-black text-[#0f172a] mb-1">{{ fmt(acc.balance, acc.currency?.symbol) }}</div>
              <div class="text-xs text-[#475569] font-mono mb-1">{{ acc.account_number }}</div>
              <div class="text-xs text-[#475569]">{{ acc.currency?.code }} — {{ acc.currency?.name_ar }}</div>
              <div class="ud-info-row mt-3"><span>رقم الحساب</span><span class="font-mono">{{ acc.account_number }}</span></div>
              <div class="ud-info-row"><span>تاريخ الإنشاء</span><span>{{ fmtShort(acc.created_at) }}</span></div>
            </div>
          </div>
        </template>

        <!-- CARDS TAB -->
        <template v-if="activeTab === 'cards'">
          <div class="grid md:grid-cols-2 gap-4">
            <div v-for="card in cards" :key="card.id" class="ud-card">
              <div class="flex items-center justify-between mb-4"><div class="flex items-center gap-2"><span class="text-xl">💳</span><span class="font-bold text-[#0f172a]">{{ card.card_type || 'Mastercard' }}</span></div><span :class="statusBadge[card.status]" class="ud-badge">{{ card.status }}</span></div>
              <div class="text-xl font-mono text-[#0f172a] tracking-wider mb-3">{{ card.card_number_masked }}</div>
              <div class="grid grid-cols-2 gap-3">
                <div class="ud-info-row"><span>حامل البطاقة</span><span>{{ card.card_holder_name }}</span></div>
                <div class="ud-info-row"><span>الصلاحية</span><span>{{ card.formatted_expiry }}</span></div>
                <div class="ud-info-row"><span>الحد اليومي</span><span>{{ fmt(card.daily_limit || 0) }}</span></div>
                <div class="ud-info-row"><span>الحد الشهري</span><span>{{ fmt(card.monthly_limit || 0) }}</span></div>
                <div class="ud-info-row"><span>الدفع أونلاين</span><span>{{ card.online_payment_enabled ? '✅' : '❌' }}</span></div>
                <div class="ud-info-row"><span>اللاتلامسي</span><span>{{ card.contactless_enabled ? '✅' : '❌' }}</span></div>
              </div>
              <div v-if="card.account" class="mt-3 p-3 bg-[#F0F4FF] rounded-xl text-xs">
                <span class="text-[#475569]">حساب:</span> <span class="font-bold text-[#1E5EFF]">{{ card.account?.currency?.code }} — {{ fmt(card.account?.balance || 0, card.account?.currency?.symbol) }}</span>
              </div>
            </div>
          </div>
          <div v-if="!cards.length" class="ud-card text-center py-10 text-[#475569]">لا توجد بطاقات</div>
        </template>

        <!-- TRANSACTIONS TAB -->
        <template v-if="activeTab === 'transactions'">
          <div class="ud-card"><div class="flex items-center justify-between mb-4"><h3 class="ud-card-title">سجل المعاملات الكامل</h3><span class="text-sm text-[#475569]">{{ transactions.length }} معاملة</span></div>
            <div class="ud-table-scroll"><table class="ud-full-table"><thead><tr><th>المرجع</th><th>النوع</th><th>المبلغ</th><th>العملة</th><th>من</th><th>إلى</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
              <tr v-for="t in transactions" :key="t.id"><td class="font-mono text-xs text-[#1E5EFF]">{{ t.reference_number }}</td><td><span class="ud-type-badge">{{ typeIcons[t.type] }} {{ typeLabels[t.type] || t.type }}</span></td><td class="font-bold">{{ fmt(t.amount, t.currency?.symbol) }}</td><td>{{ t.currency?.code }}</td><td class="text-xs">{{ t.from_account?.currency?.code || '—' }}</td><td class="text-xs">{{ t.to_account?.currency?.code || '—' }}</td><td><span :class="statusBadge[t.status]" class="ud-badge">{{ t.status }}</span></td><td class="text-xs">{{ fmtDate(t.created_at) }}</td></tr>
            </tbody></table><div v-if="!transactions.length" class="py-10 text-center text-[#475569]">لا توجد معاملات</div></div>
          </div>
        </template>

        <!-- PURCHASES TAB -->
        <template v-if="activeTab === 'purchases'">
          <div class="ud-card"><div class="flex items-center justify-between mb-4"><h3 class="ud-card-title">سجل المشتريات بالبطاقة</h3><span class="text-sm text-[#475569]">{{ (cardTransactions || []).length }} عملية</span></div>
            <div class="ud-table-scroll" v-if="cardTransactions && cardTransactions.length"><table class="ud-full-table"><thead><tr><th>التاجر</th><th>المبلغ</th><th>العملة</th><th>البطاقة</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
              <tr v-for="ct in cardTransactions" :key="ct.id"><td class="font-semibold">{{ ct.merchant_name || 'غير محدد' }}</td><td class="font-bold">{{ fmt(ct.amount, ct.currency_code) }}</td><td>{{ ct.currency_code }}</td><td class="font-mono text-xs">****{{ ct.card_id }}</td><td><span :class="statusBadge[ct.status]" class="ud-badge">{{ ct.status }}</span></td><td class="text-xs">{{ fmtDate(ct.created_at) }}</td></tr>
            </tbody></table></div><div v-else class="py-10 text-center text-[#475569]"><div class="text-4xl mb-3">🛒</div>لا توجد مشتريات بالبطاقة بعد</div>
          </div>
        </template>

        <!-- KYC TAB -->
        <template v-if="activeTab === 'kyc'">
          <div class="grid md:grid-cols-2 gap-4">
            <div class="ud-card"><h3 class="ud-card-title mb-4">حالة التحقق</h3><div class="space-y-3"><div class="ud-info-row"><span>الحالة</span><span :class="statusBadge[user.kyc_status]" class="ud-badge">{{ user.kyc_status }}</span></div><div class="ud-info-row"><span>الجنسية</span><span>{{ user.nationality || 'غير محدد' }}</span></div><div class="ud-info-row"><span>الدولة</span><span>{{ user.country || 'غير محدد' }}</span></div><div class="ud-info-row"><span>تاريخ التسجيل</span><span>{{ fmtShort(user.created_at) }}</span></div>
              <template v-if="user.veriff_session_id">
                <div class="ud-info-row"><span>🔗 Veriff Session</span><span class="font-mono text-xs">{{ user.veriff_session_id?.substring(0,12) }}...</span></div>
                <div class="ud-info-row" v-if="user.veriff_decision_code"><span>📋 رمز القرار</span><span class="font-semibold">{{ veriffStatusLabel(user.veriff_decision_code) }}</span></div>
                <div class="ud-info-row" v-if="user.veriff_decision_at"><span>📅 تاريخ القرار</span><span>{{ fmtShort(user.veriff_decision_at) }}</span></div>
              </template>
            </div></div>
            <div class="ud-card"><h3 class="ud-card-title mb-4">المستندات</h3>
              <div v-if="kycDocuments && kycDocuments.length" class="space-y-3">
                <div v-for="doc in kycDocuments" :key="doc.id" class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-200">
                  <div class="flex items-center gap-3"><span class="text-xl">📄</span><div><div class="text-sm font-semibold text-[#0f172a]">{{ doc.document_type }}</div><div class="text-xs text-[#475569]">{{ fmtShort(doc.created_at) }}</div></div></div>
                  <div class="flex items-center gap-2"><span :class="statusBadge[doc.status]" class="ud-badge">{{ doc.status }}</span><a :href="route('admin.kyc.view', doc.id)" target="_blank" class="ud-btn-sm">عرض</a></div>
                </div>
              </div><div v-else-if="!user.veriff_session_id" class="py-6 text-center text-[#475569]">لم يرفع أي مستندات</div>
              <div v-else class="py-4 text-center text-[#475569] text-sm">📋 يتم التحقق عبر Veriff — انظر أدناه</div>
            </div>
          </div>

          <!-- Veriff Media Section -->
          <div v-if="user.veriff_session_id" class="ud-card mt-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="ud-card-title">🔍 مستندات Veriff</h3>
              <button @click="loadVeriffMedia" :disabled="veriffLoading" class="ud-btn-sm">{{ veriffLoading ? '⏳ جاري التحميل...' : '🔄 تحديث' }}</button>
            </div>

            <!-- Decision Details -->
            <div v-if="veriffDecision?.verification" class="mb-4 p-4 rounded-xl border" :class="user.veriff_decision_code == 9001 ? 'bg-emerald-50 border-emerald-200' : user.veriff_decision_code == 9102 ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'">
              <div class="text-sm font-bold mb-2">{{ veriffStatusLabel(user.veriff_decision_code) }}</div>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div v-if="veriffDecision.verification.person?.firstName"><span class="text-[#64748b]">الاسم:</span> {{ veriffDecision.verification.person.firstName }} {{ veriffDecision.verification.person.lastName }}</div>
                <div v-if="veriffDecision.verification.person?.dateOfBirth"><span class="text-[#64748b]">الميلاد:</span> {{ veriffDecision.verification.person.dateOfBirth }}</div>
                <div v-if="veriffDecision.verification.person?.nationality"><span class="text-[#64748b]">الجنسية:</span> {{ veriffDecision.verification.person.nationality }}</div>
                <div v-if="veriffDecision.verification.document?.type"><span class="text-[#64748b]">نوع المستند:</span> {{ veriffDecision.verification.document.type }}</div>
                <div v-if="veriffDecision.verification.document?.number"><span class="text-[#64748b]">رقم المستند:</span> <span class="font-mono">{{ veriffDecision.verification.document.number }}</span></div>
                <div v-if="veriffDecision.verification.document?.country"><span class="text-[#64748b]">بلد المستند:</span> {{ veriffDecision.verification.document.country }}</div>
                <div v-if="veriffDecision.verification.document?.validUntil"><span class="text-[#64748b]">صالح حتى:</span> {{ veriffDecision.verification.document.validUntil }}</div>
              </div>
            </div>

            <!-- Media Images -->
            <div v-if="veriffLoading" class="py-8 text-center text-[#94a3b8]">⏳ جاري تحميل المستندات من Veriff...</div>
            <div v-else-if="veriffError" class="py-6 text-center text-red-500 text-sm">{{ veriffError }}</div>
            <div v-else-if="veriffMedia.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="(media, i) in veriffMedia" :key="i" class="vf-media-card">
                <img v-if="media.id" :src="`/admin/veriff/media/${media.id}`" class="vf-media-img" loading="lazy" @error="$event.target.style.display='none'" />
                <div class="vf-media-label">{{ media.name || media.context || ('مستند ' + (i+1)) }}</div>
              </div>
            </div>
            <div v-else class="py-6 text-center text-[#94a3b8] text-sm">لا توجد صور من Veriff بعد — ربما لم يكتمل التحقق</div>
          </div>
        </template>

        <!-- SECURITY TAB -->
        <template v-if="activeTab === 'security'">
          <div class="grid md:grid-cols-2 gap-4">
            <div class="ud-card"><h3 class="ud-card-title mb-4">🔒 معلومات الأمان</h3>
              <div class="space-y-3">
                <div class="ud-info-row"><span>آخر تسجيل دخول</span><span>{{ user.last_login_at ? fmtDate(user.last_login_at) : 'لم يسجل دخول بعد' }}</span></div>
                <div class="ud-info-row"><span>آخر IP</span><span class="font-mono">{{ user.last_login_ip || '—' }}</span></div>
                <div class="ud-info-row"><span>تأكيد البريد</span><span>{{ user.email_verified_at ? '✅ مؤكد' : '❌ غير مؤكد' }}</span></div>
                <div class="ud-info-row"><span>كود الإحالة</span><span class="font-mono">{{ user.referral_code || '—' }}</span></div>
              </div>
            </div>
            <div class="ud-card"><h3 class="ud-card-title mb-4">📋 سجل تسجيل الدخول</h3>
              <div v-if="loginHistory && loginHistory.length" class="space-y-2">
                <div v-for="log in loginHistory" :key="log.id" class="flex items-center justify-between p-2 bg-white rounded-lg border border-gray-200 text-xs">
                  <div><span class="font-mono text-[#1E5EFF]">{{ log.ip_address }}</span><span class="text-[#475569] mx-2">·</span><span class="text-[#475569]">{{ log.user_agent?.substring(0, 40) }}...</span></div>
                  <span class="text-[#475569]">{{ fmtDate(log.created_at) }}</span>
                </div>
              </div><div v-else class="py-6 text-center text-[#475569]">لا توجد سجلات</div>
            </div>
          </div>
        </template>

        <!-- NOTES TAB -->
        <template v-if="activeTab === 'notes'">
          <div class="ud-card">
            <h3 class="ud-card-title mb-4">📝 ملاحظات داخلية — {{ (adminNotes || []).length }} ملاحظة</h3>
            <!-- Add note form -->
            <div class="ud-note-form mb-4">
              <div class="flex gap-2 mb-2">
                <select v-model="adminNoteForm.category" class="ud-select" style="width:auto;min-width:140px">
                  <option v-for="(label, key) in catLabels" :key="key" :value="key">{{ label }}</option>
                </select>
              </div>
              <div class="flex gap-2">
                <textarea v-model="adminNoteForm.content" rows="2" class="ud-input flex-1" placeholder="أضف ملاحظة داخلية..."></textarea>
                <button @click="addAdminNote" :disabled="!adminNoteForm.content || adminNoteForm.processing" class="ud-action-btn ud-btn-green" style="align-self:flex-end">➕ إضافة</button>
              </div>
              <div v-if="adminNoteForm.errors.content" class="text-red-500 text-xs mt-1">{{ adminNoteForm.errors.content }}</div>
            </div>
            <!-- Notes list -->
            <div v-for="note in (adminNotes || [])" :key="note.id" class="ud-note-item" :class="{ 'ud-note-pinned': note.is_pinned }">
              <div class="ud-note-header">
                <div class="flex items-center gap-2">
                  <span v-if="note.is_pinned" class="ud-pin-icon">📌</span>
                  <span class="ud-note-cat" :style="{ color: catColors[note.category], background: catColors[note.category] + '15' }">{{ catLabels[note.category] || note.category }}</span>
                  <span class="ud-note-admin">{{ note.admin?.full_name }}</span>
                  <span class="ud-note-date">{{ fmtDate(note.created_at) }}</span>
                </div>
                <div class="flex gap-1">
                  <button @click="togglePin(note.id)" class="ud-note-action" :title="note.is_pinned ? 'إلغاء التثبيت' : 'تثبيت'">{{ note.is_pinned ? '📌' : '📍' }}</button>
                  <button @click="deleteNote(note.id)" class="ud-note-action ud-note-del" title="حذف">🗑️</button>
                </div>
              </div>
              <div class="ud-note-content">{{ note.content }}</div>
            </div>
            <div v-if="!(adminNotes || []).length" class="ud-empty py-8">📝 لا توجد ملاحظات بعد</div>
          </div>
        </template>

        <!-- LIMITS TAB -->
        <template v-if="activeTab === 'limits'">
          <!-- Section 1: Per-Customer Limits -->
          <div class="ud-card">
            <div class="flex items-center justify-between mb-5">
              <h3 class="ud-card-title">📊 حدود العميل الشخصية</h3>
              <button @click="saveUserLimits" :disabled="limitsProcessing" class="lim-save-btn">💾 حفظ الحدود</button>
            </div>
            <p class="text-xs text-[#475569] mb-4">تجاوز الحدود العامة بحدود مخصصة لهذا العميل. عطّل التفعيل لاستخدام الحد العام.</p>
            <div class="lim-grid">
              <div v-for="lim in editUserLimits" :key="lim.limit_type" class="lim-card" :class="{ 'lim-card-active': lim.is_active }">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <span class="text-xl">{{ lim.icon }}</span>
                    <span class="text-sm font-bold text-[#0f172a]">{{ lim.label }}</span>
                  </div>
                  <label class="lim-toggle">
                    <input type="checkbox" v-model="lim.is_active" />
                    <span class="lim-toggle-slider"></span>
                  </label>
                </div>
                <div v-if="lim.is_active" class="lim-inp-wrap">
                  <span class="lim-cur">€</span>
                  <input v-model.number="lim.amount" type="number" class="lim-inp" placeholder="0" />
                </div>
                <div v-else class="lim-global">
                  <span class="text-xs text-[#94a3b8]">الحد العام:</span>
                  <span class="text-xs font-bold text-[#1E5EFF]">{{ getGlobalLimit(lim.limit_type) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Syria Remittance Limits -->
          <div class="ud-card">
            <h3 class="ud-card-title mb-4">🇸🇾 حدود الحوالات لسوريا</h3>
            <p class="text-xs text-[#475569] mb-4">حدود خاصة بحوالات هذا العميل إلى سوريا.</p>
            <div class="lim-grid">
              <div v-for="lim in editSyriaLimits" :key="lim.limit_type" class="lim-card" :class="{ 'lim-card-active': lim.is_active }">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <span class="text-xl">{{ lim.icon }}</span>
                    <span class="text-sm font-bold text-[#0f172a]">{{ lim.label }}</span>
                  </div>
                  <label class="lim-toggle">
                    <input type="checkbox" v-model="lim.is_active" />
                    <span class="lim-toggle-slider"></span>
                  </label>
                </div>
                <div v-if="lim.is_active" class="lim-inp-wrap">
                  <span class="lim-cur">€</span>
                  <input v-model.number="lim.amount" type="number" class="lim-inp" placeholder="0" />
                </div>
                <div v-else class="lim-global">
                  <span class="text-xs text-[#94a3b8]">لا يوجد حد مخصص — يستخدم الإعدادات العامة</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: Per-Wallet (Account) Limits -->
          <div class="ud-card">
            <h3 class="ud-card-title mb-4">🏦 حدود المحافظ</h3>
            <p class="text-xs text-[#475569] mb-4">حدود مخصصة لكل محفظة. اتركها فارغة لاستخدام الحد العام.</p>
            <div v-if="editAccountLimits.length" class="space-y-4">
              <div v-for="acc in editAccountLimits" :key="acc.id" class="lim-wallet-card">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <div class="lim-wallet-icon">{{ acc.currency_symbol }}</div>
                    <div>
                      <div class="text-sm font-bold text-[#0f172a]">محفظة {{ acc.currency_code }}</div>
                      <div class="text-xs text-[#94a3b8] font-mono">{{ acc.account_number }}</div>
                    </div>
                  </div>
                  <button @click="saveAccountLimit(acc)" :disabled="walletProcessing[acc.id]" class="lim-wallet-save">💾 حفظ</button>
                </div>
                <div class="lim-wallet-grid">
                  <div class="lim-wallet-field">
                    <label class="lim-wallet-label">📤 تحويل يومي</label>
                    <div class="lim-inp-wrap-sm">
                      <span class="lim-cur-sm">{{ acc.currency_symbol }}</span>
                      <input v-model.number="acc.daily_transfer_limit" type="number" class="lim-inp-sm" placeholder="الحد العام" />
                    </div>
                  </div>
                  <div class="lim-wallet-field">
                    <label class="lim-wallet-label">📅 تحويل شهري</label>
                    <div class="lim-inp-wrap-sm">
                      <span class="lim-cur-sm">{{ acc.currency_symbol }}</span>
                      <input v-model.number="acc.monthly_transfer_limit" type="number" class="lim-inp-sm" placeholder="الحد العام" />
                    </div>
                  </div>
                  <div class="lim-wallet-field">
                    <label class="lim-wallet-label">💰 عملية واحدة</label>
                    <div class="lim-inp-wrap-sm">
                      <span class="lim-cur-sm">{{ acc.currency_symbol }}</span>
                      <input v-model.number="acc.single_transaction_limit" type="number" class="lim-inp-sm" placeholder="الحد العام" />
                    </div>
                  </div>
                  <div class="lim-wallet-field">
                    <label class="lim-wallet-label">🏧 سحب يومي</label>
                    <div class="lim-inp-wrap-sm">
                      <span class="lim-cur-sm">{{ acc.currency_symbol }}</span>
                      <input v-model.number="acc.daily_withdrawal_limit" type="number" class="lim-inp-sm" placeholder="الحد العام" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="ud-empty py-8">لا توجد محافظ لهذا العميل</div>
          </div>
        </template>

        <!-- ADMIN ACTIONS TAB -->
        <template v-if="activeTab === 'actions'">
          <div class="grid lg:grid-cols-3 gap-4">
            <!-- Status Control -->
            <div class="ud-card">
              <h3 class="ud-card-title mb-4">🔧 تحكم بحالة العميل</h3>
              <div class="space-y-4">
                <div><label class="text-xs text-[#475569] font-medium block mb-1">حالة الحساب</label><div class="flex gap-2"><select v-model="statusForm.status" class="ud-select flex-1"><option value="pending">معلّق</option><option value="active">نشط</option><option value="suspended">موقوف</option><option value="blocked">محظور</option></select><button @click="updateStatus" :disabled="statusForm.processing" class="ud-btn-blue">حفظ</button></div></div>
                <div><label class="text-xs text-[#475569] font-medium block mb-1">حالة KYC</label><div class="flex gap-2"><select v-model="kycForm.kyc_status" class="ud-select flex-1"><option value="pending">معلّق</option><option value="submitted">مقدّم</option><option value="verified">مُوثّق</option><option value="rejected">مرفوض</option></select><button @click="updateKyc" :disabled="kycForm.processing" class="ud-btn-green">حفظ</button></div></div>
              </div>
            </div>
            <!-- Account Actions -->
            <div class="ud-card">
              <h3 class="ud-card-title mb-4">🏦 إجراءات الحساب</h3>
              <div class="space-y-3">
                <button @click="freezeAll" class="ud-action-btn ud-action-danger w-full">❄️ تجميد جميع الحسابات والبطاقات</button>
                <button @click="unfreezeAll" class="ud-action-btn ud-action-success w-full">✅ إلغاء تجميد الكل</button>
                <button @click="resetPassword" class="ud-action-btn ud-action-warn w-full">🔑 إعادة تعيين كلمة المرور</button>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">⚠️ إعادة تعيين كلمة المرور ستولّد كلمة مرور جديدة وتظهر لك مباشرة</div>
              </div>
            </div>
            <!-- Communication -->
            <div class="ud-card">
              <h3 class="ud-card-title mb-4">📨 التواصل والبيانات</h3>
              <div class="space-y-3">
                <button @click="showSendNote = true" class="ud-action-btn ud-action-info w-full">📩 إرسال إشعار للعميل</button>
                <button @click="showEditProfile = true" class="ud-action-btn w-full">✏️ تعديل بيانات العميل</button>
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-700">ℹ️ الإشعارات تظهر للعميل في صفحة الإشعارات داخل تطبيقه</div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Edit Profile Modal -->
      <Teleport to="body">
        <div v-if="showEditProfile" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showEditProfile = false">
          <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-gray-200" style="direction:rtl">
            <h3 class="text-xl font-bold text-[#0f172a] mb-5">✏️ تعديل بيانات العميل</h3>
            <form @submit.prevent="saveProfile" class="space-y-4" style="max-height:70vh;overflow-y:auto;padding-left:4px">
              <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs text-[#475569] mb-1 font-medium">الاسم الكامل</label><input v-model="profileForm.full_name" class="ud-modal-input" required /></div>
                <div><label class="block text-xs text-[#475569] mb-1 font-medium">البريد</label><input v-model="profileForm.email" type="email" class="ud-modal-input" required /></div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs text-[#475569] mb-1 font-medium">الهاتف</label><input v-model="profileForm.phone" class="ud-modal-input" /></div>
                <div><label class="block text-xs text-[#475569] mb-1 font-medium">الجنسية</label><input v-model="profileForm.nationality" class="ud-modal-input" /></div>
              </div>
              <div><label class="block text-xs text-[#475569] mb-1 font-medium">تاريخ الميلاد</label><input v-model="profileForm.date_of_birth" type="date" class="ud-modal-input" /></div>
              <hr class="border-gray-100" />
              <p class="text-xs font-bold text-[#1E5EFF]">🌍 بيانات الإقامة</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-[#475569] mb-1 font-medium">الدولة</label>
                  <select v-model="profileForm.country" class="ud-modal-input">
                    <option value="">اختر الدولة...</option>
                    <option v-for="c in countries" :key="c.code" :value="c.code">{{ c.ar }}</option>
                  </select>
                </div>
                <div v-if="isSyria">
                  <label class="block text-xs text-[#475569] mb-1 font-medium">المحافظة</label>
                  <select v-model="profileForm.governorate" class="ud-modal-input">
                    <option value="">اختر المحافظة...</option>
                    <option v-for="g in governorates" :key="g.en" :value="g.en">{{ g.ar }}</option>
                  </select>
                </div>
                <div v-else>
                  <label class="block text-xs text-[#475569] mb-1 font-medium">المحافظة / المنطقة</label>
                  <input v-model="profileForm.governorate" class="ud-modal-input" placeholder="المحافظة أو المنطقة..." />
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs text-[#475569] mb-1 font-medium">المدينة</label><input v-model="profileForm.city" class="ud-modal-input" /></div>
                <div><label class="block text-xs text-[#475569] mb-1 font-medium">الرمز البريدي</label><input v-model="profileForm.postal_code" class="ud-modal-input" /></div>
              </div>
              <div><label class="block text-xs text-[#475569] mb-1 font-medium">العنوان التفصيلي</label><input v-model="profileForm.address" class="ud-modal-input" placeholder="الشارع، البناء، الطابق..." /></div>
              <hr class="border-gray-100" />
              <p class="text-xs font-bold text-[#1E5EFF]">💼 الوضع الاجتماعي</p>
              <div>
                <label class="block text-xs text-[#475569] mb-1 font-medium">الحالة المهنية</label>
                <select v-model="profileForm.employment" class="ud-modal-input">
                  <option value="">اختر...</option>
                  <option v-for="e in employmentOptions" :key="e.v" :value="e.v">{{ e.l }}</option>
                </select>
              </div>
              <div v-if="profileForm.errors" class="text-xs text-red-500"><div v-for="(e, k) in profileForm.errors" :key="k">{{ e }}</div></div>
              <div class="flex gap-3 pt-2"><button type="submit" :disabled="profileForm.processing" class="flex-1 bg-[#1E5EFF] hover:bg-[#1047b8] text-white py-3 rounded-xl font-semibold disabled:opacity-50">حفظ التغييرات</button><button type="button" @click="showEditProfile = false" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl">إلغاء</button></div>
            </form>
          </div>
        </div>
      </Teleport>

      <!-- Send Notification Modal -->
      <Teleport to="body">
        <div v-if="showSendNote" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showSendNote = false">
          <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-gray-200" style="direction:rtl">
            <h3 class="text-xl font-bold text-[#0f172a] mb-1">📩 إرسال إشعار</h3>
            <p class="text-[#475569] text-sm mb-4">إلى: {{ user.full_name }}</p>
            <form @submit.prevent="sendNote" class="space-y-4">
              <textarea v-model="noteForm.note" placeholder="اكتب رسالة الإشعار..." rows="4" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[#0f172a] outline-none focus:border-[#1E5EFF] text-sm resize-none" required></textarea>
              <div class="flex gap-3"><button type="submit" :disabled="noteForm.processing || !noteForm.note" class="flex-1 bg-[#1E5EFF] hover:bg-[#1047b8] text-white py-3 rounded-xl font-semibold disabled:opacity-50">إرسال</button><button type="button" @click="showSendNote = false" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl">إلغاء</button></div>
            </form>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>

<style>
@import '../../../css/admin.css';
@import '../../../css/admin.css';
.ud-root{min-height:100vh;background:#f1f5f9;direction:rtl}
.ud-header{background:#ffffff;border-bottom:1px solid #e2e8f0}
.ud-back{font-size:13px;color:#3b82f6;font-weight:600;text-decoration:none}.ud-back:hover{color:#1047b8}
.ud-avatar-lg{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:24px;flex-shrink:0}
.ud-balance-card{background:linear-gradient(135deg,rgba(30,94,255,0.04),rgba(30,94,255,0.02));border:1px solid rgba(16,185,129,0.2);border-radius:16px;padding:16px 24px;text-align:right}
.ud-tabs{display:flex;gap:4px;border-bottom:2px solid #e2e8f0;overflow-x:auto}
.ud-tab{padding:10px 16px;font-size:13px;font-weight:500;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;cursor:pointer;background:none;border-top:none;border-left:none;border-right:none;display:flex;align-items:center;gap:6px;white-space:nowrap}.ud-tab:hover{color:#3b82f6}
.ud-tab-active{color:#3b82f6;border-bottom-color:#3b82f6;font-weight:700}
.ud-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;transition:box-shadow .3s}.ud-card:hover{box-shadow:0 4px 15px rgba(0,0,0,0.04)}
.ud-card-title{font-size:16px;font-weight:700;color:#0f172a}
.ud-account-card{border-right:4px solid #1E5EFF}
.ud-info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#64748b}.ud-info-row:last-child{border-bottom:none}
.ud-table-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;flex-wrap:wrap;gap:8px}.ud-table-row:last-child{border-bottom:none}
.ud-tx-icon{width:36px;height:36px;border-radius:10px;background:#ffffff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.ud-table-scroll{overflow-x:auto;max-height:500px;overflow-y:auto}
.ud-full-table{width:100%;border-collapse:collapse;font-size:13px}
.ud-full-table th{text-align:right;padding:10px 12px;background:#ffffff;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:1}
.ud-full-table td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;vertical-align:middle}
.ud-full-table tr:hover td{background:#ffffff}
.ud-badge{font-size:11px;padding:2px 10px;border-radius:100px;font-weight:600}
.ud-badge-green{background:rgba(16,185,129,0.1);color:#059669}.ud-badge-yellow{background:rgba(245,158,11,0.1);color:#d97706}.ud-badge-red{background:rgba(239,68,68,0.1);color:#dc2626}.ud-badge-blue{background:rgba(59,130,246,0.15);color:#3b82f6}.ud-badge-gray{background:#f1f5f9;color:#64748b}
.ud-type-badge{display:inline-flex;align-items:center;gap:4px;font-size:12px}
.ud-btn-blue{padding:8px 16px;background:#10b981;color:#fff;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer}.ud-btn-blue:hover{background:#1047b8}.ud-btn-blue:disabled{opacity:.5}
.ud-btn-green{padding:8px 16px;background:#10b981;color:#fff;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer}.ud-btn-green:hover{background:#059669}.ud-btn-green:disabled{opacity:.5}
.ud-btn-sm{padding:4px 12px;background:#ffffff;color:#3b82f6;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(30,94,255,0.15)}.ud-btn-sm:hover{background:#10b981;color:#fff}
.ud-select{padding:8px 12px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;font-size:13px;color:#0f172a;outline:none}.ud-select:focus{border-color:#10b981}
.ud-action-btn{padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid #e2e8f0;background:#ffffff;color:#334155;transition:all .2s;text-align:center}.ud-action-btn:hover{background:#ffffff;border-color:#10b981;color:#3b82f6}
.ud-action-warn{border-color:rgba(245,158,11,0.2);color:#d97706}.ud-action-warn:hover{background:rgba(245,158,11,0.05);border-color:#f59e0b;color:#d97706}
.ud-action-danger{border-color:rgba(239,68,68,0.2);color:#dc2626}.ud-action-danger:hover{background:rgba(239,68,68,0.05);border-color:#ef4444;color:#dc2626}
.ud-action-success{border-color:rgba(16,185,129,0.2);color:#059669}.ud-action-success:hover{background:rgba(16,185,129,0.05);border-color:#10b981;color:#059669}
.ud-action-info{border-color:rgba(30,94,255,0.2);color:#3b82f6}.ud-action-info:hover{background:rgba(30,94,255,0.05);border-color:#10b981}
.ud-modal-input{width:100%;border:1px solid #e2e8f0;border-radius:12px;padding:10px 14px;font-size:13px;color:#0f172a;outline:none}.ud-modal-input:focus{border-color:#10b981}
.ud-table-scroll::-webkit-scrollbar{width:4px;height:4px}.ud-table-scroll::-webkit-scrollbar-track{background:transparent}.ud-table-scroll::-webkit-scrollbar-thumb{background:#E2E8F0;border-radius:4px}
/* Notes */
.ud-note-form{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px}
.ud-note-item{border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:8px;transition:all .15s}
.ud-note-item:hover{border-color:#cbd5e1}
.ud-note-pinned{border-color:#fbbf24;background:#fffbeb}
.ud-note-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.ud-note-cat{font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px}
.ud-note-admin{font-size:12px;font-weight:600;color:#334155}
.ud-note-date{font-size:11px;color:#94a3b8}
.ud-note-content{font-size:14px;color:#334155;line-height:1.6;white-space:pre-wrap}
.ud-note-action{background:none;border:none;cursor:pointer;font-size:16px;padding:4px;border-radius:6px;transition:background .15s}
.ud-note-action:hover{background:#f1f5f9}
.ud-note-del:hover{background:#fef2f2}
.ud-pin-icon{font-size:14px}
.ud-empty{text-align:center;color:#94a3b8;font-size:14px}
/* Limits Tab */
.lim-save-btn{padding:8px 20px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;transition:all .2s}.lim-save-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(16,185,129,0.3)}.lim-save-btn:disabled{opacity:.5;transform:none}
.lim-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.lim-card{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:14px;padding:16px;transition:all .2s}
.lim-card-active{background:#f0fdf4;border-color:#10b981}
.lim-toggle{position:relative;display:inline-block;width:40px;height:22px}
.lim-toggle input{opacity:0;width:0;height:0}
.lim-toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;border-radius:22px;transition:.3s}
.lim-toggle-slider::before{content:'';position:absolute;height:16px;width:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
.lim-toggle input:checked+.lim-toggle-slider{background:#10b981}
.lim-toggle input:checked+.lim-toggle-slider::before{transform:translateX(18px)}
.lim-inp-wrap{display:flex;align-items:center;gap:6px}
.lim-cur{font-size:16px;font-weight:700;color:#94a3b8}
.lim-inp{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:16px;font-weight:700;color:#0f172a;outline:none;text-align:left;direction:ltr}.lim-inp:focus{border-color:#10b981}
.lim-global{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f1f5f9;border-radius:8px}
.lim-wallet-card{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px;padding:20px;transition:all .2s}.lim-wallet-card:hover{border-color:#3b82f6}
.lim-wallet-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:18px;flex-shrink:0}
.lim-wallet-save{padding:6px 14px;background:#3b82f6;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:12px;cursor:pointer;transition:all .2s}.lim-wallet-save:hover{background:#1d4ed8}.lim-wallet-save:disabled{opacity:.5}
.lim-wallet-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.lim-wallet-field{}
.lim-wallet-label{display:block;font-size:11px;font-weight:600;color:#64748b;margin-bottom:4px}
.lim-inp-wrap-sm{display:flex;align-items:center;gap:4px}
.lim-cur-sm{font-size:13px;font-weight:700;color:#94a3b8}
.lim-inp-sm{width:100%;padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#0f172a;outline:none;text-align:left;direction:ltr}.lim-inp-sm:focus{border-color:#3b82f6}
@media(max-width:768px){.lim-grid{grid-template-columns:1fr}.lim-wallet-grid{grid-template-columns:1fr}}
/* Veriff Media */
.vf-media-card{border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;transition:all .2s}.vf-media-card:hover{border-color:#10b981;box-shadow:0 4px 12px rgba(0,0,0,.06)}
.vf-media-img{width:100%;height:180px;object-fit:cover;display:block;background:#f1f5f9}
.vf-media-label{padding:8px 12px;font-size:12px;font-weight:600;color:#334155;text-align:center;background:#f8fafc;border-top:1px solid #e2e8f0}
</style>
