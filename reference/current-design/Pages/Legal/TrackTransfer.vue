<script setup>
import { Head, router } from '@inertiajs/vue3';
import { inject, computed, ref } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
defineOptions({ layout: SiteLayout });
const isAr = inject('isAr', computed(() => true));
const props = defineProps({ transfer: Object, reference: String });
const searchRef = ref(props.reference || '');
const searching = ref(false);

function search() {
  if (!searchRef.value.trim()) return;
  searching.value = true;
  router.get(`/track/${searchRef.value.trim()}`, {}, {
    preserveState: false,
    onFinish: () => searching.value = false,
  });
}

const statusMap = {
  completed: { ar: 'مكتمل', en: 'Completed', color: '#059669', bg: 'rgba(16,185,129,.08)', icon: '✓' },
  pending: { ar: 'قيد المعالجة', en: 'Pending', color: '#D97706', bg: 'rgba(245,158,11,.08)', icon: '⏳' },
  failed: { ar: 'فشل', en: 'Failed', color: '#DC2626', bg: 'rgba(220,38,38,.08)', icon: '✗' },
  cancelled: { ar: 'ملغي', en: 'Cancelled', color: '#6B7280', bg: 'rgba(107,114,128,.08)', icon: '—' },
};

const st = computed(() => statusMap[props.transfer?.status] || statusMap.pending);
const tx = computed(() => props.transfer);
</script>
<template>
<Head :title="isAr ? 'تتبع التحويل — SDB Wallet' : 'Track Transfer — SDB Wallet'"/>
<section class="hero"><div class="sw tc">
  <div class="hero-tag">{{ isAr ? 'تتبع التحويلات' : 'Track Transfer' }}</div>
  <h1 class="t2">{{ isAr ? 'تتبع حوالتك' : 'Track Your Transfer' }}<br><span class="t2-em">{{ isAr ? 'بخطوة واحدة.' : 'In one step.' }}</span></h1>
  <p class="t2-sub tc" style="max-width:500px;margin:0 auto">{{ isAr ? 'أدخل رقم المرجع لمعرفة حالة التحويل فوراً' : 'Enter your reference number to check transfer status instantly' }}</p>
</div></section>

<section class="sec"><div class="sw">
  <!-- Search Box -->
  <div class="search-box">
    <input v-model="searchRef" @keyup.enter="search" :placeholder="isAr ? 'رقم المرجع مثال: TRF-XXXXXXXXXX' : 'Reference e.g. TRF-XXXXXXXXXX'"
      class="search-input" :dir="'ltr'" />
    <button @click="search" :disabled="searching || !searchRef.trim()" class="search-btn">
      <span v-if="searching" class="spin">⏳</span>
      <span v-else>{{ isAr ? '🔍 بحث' : '🔍 Search' }}</span>
    </button>
  </div>

  <!-- Result -->
  <div v-if="reference && !tx" class="not-found">
    <div class="nf-icon">🔍</div>
    <h3>{{ isAr ? 'لم يتم العثور على الحوالة' : 'Transfer Not Found' }}</h3>
    <p>{{ isAr ? 'تأكد من رقم المرجع وحاول مجدداً' : 'Check the reference number and try again' }}</p>
  </div>

  <div v-if="tx" class="receipt-card">
    <!-- Status Header -->
    <div class="receipt-header">
      <div class="status-circle" :style="{background: st.bg, color: st.color}">
        <span style="font-size:28px">{{ st.icon }}</span>
      </div>
      <h2 class="receipt-title" :style="{color: st.color}">{{ isAr ? st.ar : st.en }}</h2>
    </div>

    <!-- Amount -->
    <div class="amount-box">
      <div class="amount-label">{{ isAr ? 'المبلغ' : 'Amount' }}</div>
      <div class="amount-value">{{ tx.symbol }}{{ Number(tx.amount).toLocaleString('en', {minimumFractionDigits:2}) }}</div>
      <div class="amount-currency">{{ tx.currency }}</div>
    </div>

    <!-- Details Grid -->
    <div class="details-grid">
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'المرجع' : 'Reference' }}</span>
        <span class="detail-value mono">{{ tx.reference }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'المرسل' : 'Sender' }}</span>
        <span class="detail-value">{{ tx.sender }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'حساب المرسل' : 'Sender Account' }}</span>
        <span class="detail-value mono">{{ tx.sender_account }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'المستلم' : 'Recipient' }}</span>
        <span class="detail-value">{{ tx.recipient }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'حساب المستلم' : 'Recipient Account' }}</span>
        <span class="detail-value mono">{{ tx.recipient_account }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'الرسوم' : 'Fee' }}</span>
        <span class="detail-value" style="color:#059669;font-weight:700">{{ Number(tx.fee) === 0 ? (isAr ? 'مجاني ✓' : 'Free ✓') : tx.symbol + tx.fee }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'التاريخ' : 'Date' }}</span>
        <span class="detail-value">{{ tx.date }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">{{ isAr ? 'النوع' : 'Type' }}</span>
        <span class="detail-value">{{ tx.type === 'transfer' ? (isAr ? 'تحويل داخلي' : 'Internal Transfer') : tx.type }}</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="receipt-footer">
      <div class="secure-badge">🔒 {{ isAr ? 'تحويل مشفّر وآمن عبر SDB Wallet' : 'Encrypted & secure via SDB Wallet' }}</div>
    </div>
  </div>

  <!-- Empty State -->
  <div v-if="!reference" class="empty-state">
    <div class="es-icon">📋</div>
    <h3>{{ isAr ? 'أدخل رقم المرجع أعلاه' : 'Enter reference number above' }}</h3>
    <p>{{ isAr ? 'يمكنك العثور على رقم المرجع في إيصال التحويل أو الإشعارات' : 'You can find the reference in your transfer receipt or notifications' }}</p>
  </div>
</div></section>
</template>
<style scoped>
.hero{padding:160px 0 80px;background:linear-gradient(135deg,#059669 0%,#10B981 50%,#34D399 100%);color:#fff}
.hero-tag{font-size:12px;font-weight:800;letter-spacing:3px;color:rgba(255,255,255,.7);text-transform:uppercase;margin-bottom:24px}
.t2{font-size:clamp(2rem,4vw,3.2rem);font-weight:900;line-height:1.1;margin-bottom:16px;color:#fff}.t2-em{color:#D1FAE5}
.t2-sub{font-size:16px;color:rgba(255,255,255,.7);line-height:1.85}
.sec{padding:60px 0 100px}.sw{max-width:600px;margin:0 auto;padding:0 24px}.tc{text-align:center}

.search-box{display:flex;gap:10px;margin-bottom:32px}
.search-input{flex:1;height:52px;padding:0 20px;font-size:15px;font-weight:600;border:2px solid rgba(16,185,129,.15);border-radius:16px;background:#fff;color:#111;outline:none;letter-spacing:.5px;font-family:monospace;transition:border .2s}
.search-input:focus{border-color:#10B981}
.search-btn{height:52px;padding:0 24px;background:#059669;color:#fff;border:none;border-radius:16px;font-size:14px;font-weight:800;cursor:pointer;transition:all .2s;white-space:nowrap}
.search-btn:hover{background:#047857;transform:translateY(-1px)}.search-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.spin{display:inline-block;animation:sp .8s linear infinite}@keyframes sp{to{transform:rotate(360deg)}}

.not-found{text-align:center;padding:60px 20px;background:#fff;border-radius:20px;border:1px solid rgba(220,38,38,.08)}
.nf-icon{font-size:48px;margin-bottom:16px}.not-found h3{font-size:18px;font-weight:800;color:#1a1a1a;margin-bottom:8px}.not-found p{font-size:14px;color:#888}

.receipt-card{background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.06);border:1px solid rgba(16,185,129,.08)}
.receipt-header{text-align:center;padding:32px 24px 16px}
.status-circle{width:64px;height:64px;border-radius:32px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;font-weight:900}
.receipt-title{font-size:20px;font-weight:900;margin:0}

.amount-box{text-align:center;padding:16px 24px 28px;border-bottom:1px solid rgba(16,185,129,.06)}
.amount-label{font-size:12px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:2px;margin-bottom:8px}
.amount-value{font-size:36px;font-weight:900;color:#111;letter-spacing:-1px}.amount-currency{font-size:14px;font-weight:700;color:#888;margin-top:4px}

.details-grid{padding:20px 24px}
.detail-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid rgba(0,0,0,.03)}
.detail-row:last-child{border:none}
.detail-label{font-size:13px;font-weight:600;color:#888}.detail-value{font-size:13px;font-weight:700;color:#111}
.mono{font-family:monospace;letter-spacing:.5px}

.receipt-footer{padding:16px 24px 20px;background:rgba(16,185,129,.03);text-align:center}
.secure-badge{font-size:12px;font-weight:700;color:#059669}

.empty-state{text-align:center;padding:60px 20px;background:#fff;border-radius:20px;border:1px dashed rgba(16,185,129,.2)}
.es-icon{font-size:48px;margin-bottom:16px}.empty-state h3{font-size:16px;font-weight:800;color:#1a1a1a;margin-bottom:8px}.empty-state p{font-size:13px;color:#888}

@media(max-width:768px){
  .hero{padding:130px 0 50px}.t2{font-size:clamp(1.6rem,5vw,2.4rem)}
  .search-box{flex-direction:column}.search-btn{width:100%}
  .amount-value{font-size:28px}.detail-row{flex-direction:column;align-items:flex-start;gap:4px}
}
</style>
