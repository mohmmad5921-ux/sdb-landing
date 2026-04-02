<script setup>
import { ref, computed, inject } from 'vue';
const isAr = inject('isAr', computed(() => true));
const screen = ref('home');
const prevScreen = ref('');
const sendStep = ref(0);
const sendAmt = ref('250');
const cardFrozen = ref(false);
const walletIdx = ref(0);
const insightTab = ref(0);

function go(s) {
  if (s === screen.value) return;
  prevScreen.value = screen.value;
  if (s === 'send') sendStep.value = 0;
  screen.value = s;
}
function back() { go(prevScreen.value || 'home'); }
function doSend() {
  if (sendStep.value < 2) { sendStep.value++; }
  else { sendStep.value = 0; go('home'); }
}

const wallets = [
  { code: 'EUR', symbol: '€', flag: 'eu', balance: '1,000.00', color: '#3B82F6', grad: ['#1E40AF','#3B82F6'] },
  { code: 'SYP', symbol: 'ل.س', flag: 'sy', balance: '500,000', color: '#F59E0B', grad: ['#B45309','#F59E0B'] },
  { code: 'USD', symbol: '$', flag: 'us', balance: '0.00', color: '#10B981', grad: ['#047857','#10B981'] },
];
const txs = [
  { n: 'Netflix', a: '-€15.99', ic: 'N', c: '#EF4444', d: isAr.value?'اليوم':'Today', pos: false },
  { n: isAr.value?'راتب':'Salary', a: '+€850.00', ic: 'S', c: '#10B981', d: isAr.value?'أمس':'Yesterday', pos: true },
  { n: isAr.value?'تحويل لسوريا':'Transfer to Syria', a: '-€200.00', ic: 'T', c: '#3B82F6', d: '18 Mar', pos: false },
  { n: 'Spotify', a: '-€9.99', ic: 'S', c: '#1DB954', d: '17 Mar', pos: false },
  { n: isAr.value?'من خالد':'From Khaled', a: '+€120.00', ic: 'K', c: '#F59E0B', d: '16 Mar', pos: true },
  { n: isAr.value?'سوبرماركت':'Supermarket', a: '-€45.30', ic: 'M', c: '#EC4899', d: '15 Mar', pos: false },
];
const payOpts = [
  { ic: '↗', t: isAr.value?'إرسال':'Send', sub: isAr.value?'تحويل إلى حساب آخر':'Transfer to another account', act: 'send' },
  { ic: '💱', t: isAr.value?'تحويل عملات':'Exchange', sub: isAr.value?'تحويل بين العملات':'Convert currencies', act: '' },
  { ic: '+', t: isAr.value?'إضافة رصيد':'Add Money', sub: isAr.value?'إضافة أموال لحسابك':'Add funds', act: '' },
  { ic: '✈', t: isAr.value?'حوالات سوريا':'Syria Remittance', sub: isAr.value?'إرسال أموال لسوريا':'Send to Syria', act: '' },
];
const moreItems = [
  { ic: '🔒', t: isAr.value?'الأمان':'Security', sub: isAr.value?'كلمة المرور والبصمة':'Password & biometrics' },
  { ic: '🌍', t: isAr.value?'اللغة':'Language', sub: isAr.value?'العربية':'Arabic' },
  { ic: '💰', t: isAr.value?'العملة':'Currency', sub: 'EUR' },
  { ic: '🔔', t: isAr.value?'الإشعارات':'Notifications', sub: isAr.value?'إدارة التنبيهات':'Manage alerts' },
  { ic: '❓', t: isAr.value?'المساعدة':'Help', sub: isAr.value?'الأسئلة الشائعة':'FAQ & support' },
  { ic: '📧', t: isAr.value?'تواصل معنا':'Contact', sub: 'support@sdbwallet.com' },
];
const tabItems = [
  { id: 'home', ic: '⌂', l: isAr.value?'الرئيسية':'Home' },
  { id: 'cards', ic: '💳', l: isAr.value?'المحافظ':'Wallets' },
  { id: 'pay', ic: '⇅', l: isAr.value?'المدفوعات':'Payments' },
  { id: 'insights', ic: '📊', l: isAr.value?'التحليلات':'Insights' },
  { id: 'profile', ic: '👤', l: isAr.value?'المزيد':'More' },
];
const bars = [
  { label: isAr.value?'ديس':'Dec', spend: 420, income: 850 },
  { label: isAr.value?'يناير':'Jan', spend: 310, income: 900 },
  { label: isAr.value?'فبراير':'Feb', spend: 580, income: 820 },
  { label: isAr.value?'مارس':'Mar', spend: 270, income: 970 },
];
</script>
<template>
<div class="sim-phone">
  <div class="sim-island"></div>
  <div class="sim-screen">
    <div class="sim-status"><span>9:41</span><div class="sim-status-r"><span class="sim-sig">●●●●</span><span class="sim-wifi">⏐</span><div class="sim-batt"></div></div></div>

    <!-- ═══ HOME ═══ -->
    <div v-if="screen==='home'" class="sim-body sim-fade">
      <div class="sh-header"><span class="sh-logo">SDB</span><div class="sh-bell">🔔<span class="sh-bell-dot"></span></div></div>
      <div class="sh-greet">{{ isAr?'مرحباً، أحمد':'Hello, Ahmad' }}</div>
      <div class="sh-bal-card">
        <p class="sh-bal-lbl">{{ isAr?'إجمالي الرصيد':'Total Balance' }}</p>
        <h2 class="sh-bal">€1,000<span>.00</span></h2>
      </div>
      <div class="sh-wallets">
        <div v-for="w in wallets" :key="w.code" class="sh-wallet-pill">
          <span :class="'fi fi-' + w.flag" class="sh-wf"></span>
          <span class="sh-wc">{{ w.code }}</span>
          <span class="sh-wb">{{ w.symbol }}{{ w.balance }}</span>
        </div>
      </div>
      <div class="sh-actions">
        <div class="sh-pill" @click="go('send')">↗ {{ isAr?'إرسال':'Send' }}</div>
        <div class="sh-pill">💱 {{ isAr?'تحويل':'Exchange' }}</div>
        <div class="sh-pill" @click="go('cards')">💳 {{ isAr?'بطاقات':'Cards' }}</div>
        <div class="sh-pill">+ {{ isAr?'إيداع':'Deposit' }}</div>
      </div>
      <div class="sh-sec-hdr"><span class="sh-sec-title">{{ isAr?'آخر المعاملات':'Recent Transactions' }}</span><span class="sh-see-all" @click="go('insights')">{{ isAr?'عرض الكل':'See all' }} ›</span></div>
      <div v-for="(tx,i) in txs.slice(0,4)" :key="i" class="sh-tx">
        <div class="sh-tx-l"><div class="sh-tx-ic" :style="{background:tx.c}">{{ tx.ic }}</div><div><div class="sh-tx-n">{{ tx.n }}</div><div class="sh-tx-d">{{ tx.d }}</div></div></div>
        <span class="sh-tx-a" :class="{'sh-tx-pos':tx.pos}">{{ tx.a }}</span>
      </div>
      <div class="sh-ai" @click="go('insights')">
        <div class="sh-ai-ic">🤖</div>
        <div class="sh-ai-body"><div class="sh-ai-t">{{ isAr?'المساعد الذكي':'AI Assistant' }}</div><div class="sh-ai-s">{{ isAr?'كيف أقدر أساعدك؟':'How can I help?' }}</div></div>
        <span class="sh-ai-arr">›</span>
      </div>
    </div>

    <!-- ═══ CARDS/WALLETS ═══ -->
    <div v-if="screen==='cards'" class="sim-body sim-fade">
      <div class="sim-nav"><span></span><span class="sim-nav-t">{{ isAr?'المحافظ':'Wallets' }}</span><span></span></div>
      <div class="sc-card" :style="{background:'linear-gradient(135deg,'+wallets[walletIdx].grad[0]+','+wallets[walletIdx].grad[1]+')'}" :class="{'sc-frozen':cardFrozen}">
        <div class="sc-card-top"><span class="sc-brand">SDB</span><span class="sc-type">VISA</span></div>
        <p class="sc-num">•••• •••• •••• 4829</p>
        <div class="sc-card-bot"><div><p class="sc-lbl">{{ isAr?'الرصيد':'BALANCE' }}</p><p class="sc-name">{{ wallets[walletIdx].symbol }}{{ wallets[walletIdx].balance }}</p></div><div><p class="sc-lbl">EXPIRES</p><p class="sc-name">03/29</p></div></div>
        <div v-if="cardFrozen" class="sc-frozen-overlay">❄️ {{ isAr?'مجمّدة':'Frozen' }}</div>
      </div>
      <div class="sc-dots"><span v-for="(w,i) in wallets" :key="i" class="sc-dot" :class="{'sc-dot-active':walletIdx===i}" @click="walletIdx=i"></span></div>
      <div class="sc-actions">
        <div class="sc-act"><span>👁</span><span>{{ isAr?'إظهار':'Show' }}</span></div>
        <div class="sc-act"><span>🔢</span><span>PIN</span></div>
        <div class="sc-act" @click="cardFrozen=!cardFrozen"><span>{{ cardFrozen?'☀️':'❄️' }}</span><span>{{ cardFrozen?(isAr?'تفعيل':'Unfreeze'):(isAr?'تجميد':'Freeze') }}</span></div>
      </div>
      <p class="sh-sec-title" style="margin-top:12px">{{ isAr?'حساباتي':'My Accounts' }}</p>
      <div v-for="(w,i) in wallets" :key="w.code" class="sw-row" :class="{'sw-row-active':walletIdx===i}" @click="walletIdx=i">
        <span :class="'fi fi-' + w.flag" class="sw-flag"></span>
        <div class="sw-info"><div class="sw-code">{{ w.code }}</div><div class="sw-sub">IBAN: SY02...7890</div></div>
        <div class="sw-bal" :style="{color:w.color}">{{ w.symbol }}{{ w.balance }}</div>
      </div>
    </div>

    <!-- ═══ PAYMENTS ═══ -->
    <div v-if="screen==='pay'" class="sim-body sim-fade">
      <div class="sh-header"><div class="sp-hdr-ic">👤</div><span class="sh-logo" style="font-size:18px">{{ isAr?'المدفوعات':'Payments' }}</span><div class="sp-hdr-add" @click="go('send')">+</div></div>
      <div class="sp-card-group">
        <div v-for="(p,i) in payOpts" :key="i" class="sp-pay-item" @click="p.act?go(p.act):null">
          <div class="sp-pay-ic">{{ p.ic }}</div>
          <div class="sp-pay-body"><div class="sp-pay-t">{{ p.t }}</div><div class="sp-pay-s">{{ p.sub }}</div></div>
          <span class="sp-arr">›</span>
        </div>
      </div>
      <p class="sh-sec-title" style="margin-top:16px">{{ isAr?'عمليات متكررة':'Recurring' }}</p>
      <div class="sp-card-group">
        <div class="sp-pay-item"><div class="sp-pay-ic">🔄</div><div class="sp-pay-body"><div class="sp-pay-t">{{ isAr?'التحويلات المجدولة':'Scheduled' }}</div><div class="sp-pay-s">{{ isAr?'إدارة المتكررة':'Manage recurring' }}</div></div><span class="sp-arr">›</span></div>
      </div>
    </div>

    <!-- ═══ INSIGHTS ═══ -->
    <div v-if="screen==='insights'" class="sim-body sim-fade">
      <div class="sh-header"><div class="sp-hdr-ic">👤</div><span class="sh-logo" style="font-size:18px">{{ isAr?'التحليلات':'Insights' }}</span><div class="sp-hdr-add">⚙</div></div>
      <div class="si-tabs">
        <div class="si-tab" :class="{'si-tab-on':insightTab===0}" @click="insightTab=0">{{ isAr?'صادر':'Outgoing' }}</div>
        <div class="si-tab" :class="{'si-tab-on':insightTab===1}" @click="insightTab=1">{{ isAr?'وارد':'Incoming' }}</div>
        <div class="si-tab" :class="{'si-tab-on':insightTab===2}" @click="insightTab=2">{{ isAr?'نظرة عامة':'Overview' }}</div>
      </div>
      <div class="si-total">{{ insightTab===0?'-€271.28':insightTab===1?'+€970.00':'€698.72' }}</div>
      <div class="si-period">{{ isAr?'مارس 2026':'March 2026' }}</div>
      <div class="si-bars">
        <div v-for="(b,i) in bars" :key="i" class="si-bar-col">
          <div class="si-bar-wrap"><div class="si-bar" :style="{height:(insightTab===0?b.spend:b.income)/10+'px',background:i===3?(insightTab===0?'#0F172A':'#10B981'):'#E2E8F0'}"></div></div>
          <span class="si-bar-lbl" :class="{'si-bar-cur':i===3}">{{ b.label }}</span>
        </div>
      </div>
      <p class="sh-sec-title" style="margin-top:12px">{{ isAr?'التصنيفات':'Categories' }}</p>
      <div class="si-cat"><div class="si-cat-l"><span class="si-cat-ic" style="background:#3B82F6">↗</span><span>{{ isAr?'تحويلات':'Transfers' }}</span></div><span class="si-cat-v">€200.00</span></div>
      <div class="si-cat"><div class="si-cat-l"><span class="si-cat-ic" style="background:#EF4444">♪</span><span>{{ isAr?'اشتراكات':'Subscriptions' }}</span></div><span class="si-cat-v">€25.98</span></div>
      <div class="si-cat"><div class="si-cat-l"><span class="si-cat-ic" style="background:#EC4899">🛒</span><span>{{ isAr?'تسوق':'Shopping' }}</span></div><span class="si-cat-v">€45.30</span></div>
    </div>

    <!-- ═══ MORE/PROFILE ═══ -->
    <div v-if="screen==='profile'" class="sim-body sim-fade">
      <div class="sim-nav"><span></span><span class="sim-nav-t">{{ isAr?'المزيد':'More' }}</span><span></span></div>
      <div class="sm-header">
        <div class="sm-avatar">أ</div>
        <h3 class="sm-name">{{ isAr?'أحمد محمد':'Ahmad Mohammad' }}</h3>
        <p class="sm-email">ahmad@email.com</p>
        <div class="sm-badge">✓ {{ isAr?'موثّق':'Verified' }}</div>
      </div>
      <div class="sp-card-group">
        <div v-for="(m,i) in moreItems" :key="i" class="sp-pay-item">
          <div class="sp-pay-ic">{{ m.ic }}</div>
          <div class="sp-pay-body"><div class="sp-pay-t">{{ m.t }}</div><div class="sp-pay-s">{{ m.sub }}</div></div>
          <span class="sp-arr">›</span>
        </div>
      </div>
      <div class="sm-logout">🚪 {{ isAr?'تسجيل الخروج':'Sign Out' }}</div>
    </div>

    <!-- ═══ SEND ═══ -->
    <div v-if="screen==='send'" class="sim-body sim-fade">
      <div class="sim-nav"><span class="sim-back" @click="back">‹</span><span class="sim-nav-t">{{ isAr?'إرسال تحويل':'Send Money' }}</span><span></span></div>
      <template v-if="sendStep===0">
        <div class="ss-recipient"><div class="ss-avatar">م</div><div><div class="ss-rname">{{ isAr?'محمد علي':'Mohammad Ali' }}</div><div class="ss-rbank">Damascus - Syria</div></div></div>
        <div class="ss-amt-wrap"><p class="ss-amt-lbl">{{ isAr?'المبلغ':'Amount' }}</p><div class="ss-amt-box"><span class="ss-cur">€</span><input type="text" v-model="sendAmt" class="ss-amt-inp"/></div><p class="ss-rate">≈ {{ (parseFloat(sendAmt.replace(/,/g,''))*14250 || 0).toLocaleString() }} SYP</p></div>
        <div class="ss-det"><div class="ss-det-r"><span>{{ isAr?'الرسوم':'Fee' }}</span><span class="ss-det-v">€2.99</span></div><div class="ss-det-r"><span>{{ isAr?'يصل خلال':'Arrives in' }}</span><span class="ss-det-v">⚡ {{ isAr?'ثوانٍ':'Seconds' }}</span></div></div>
        <button class="ss-btn" @click="doSend">{{ isAr?'متابعة':'Continue' }} →</button>
      </template>
      <template v-if="sendStep===1">
        <div class="ss-confirm">
          <div class="ss-conf-row"><span>{{ isAr?'إلى':'To' }}</span><span>{{ isAr?'محمد علي':'Mohammad Ali' }}</span></div>
          <div class="ss-conf-row"><span>{{ isAr?'المبلغ':'Amount' }}</span><span class="ss-conf-val">€{{ sendAmt }}</span></div>
          <div class="ss-conf-row"><span>{{ isAr?'يحصل على':'Gets' }}</span><span class="ss-conf-val">{{ (parseFloat(sendAmt.replace(/,/g,''))*14250 || 0).toLocaleString() }} SYP</span></div>
          <div class="ss-conf-row ss-conf-total"><span>{{ isAr?'الإجمالي':'Total' }}</span><span>€{{ (parseFloat(sendAmt.replace(/,/g,''))+2.99).toFixed(2) }}</span></div>
        </div>
        <button class="ss-btn ss-btn-confirm" @click="doSend">{{ isAr?'تأكيد التحويل':'Confirm' }} ✓</button>
      </template>
      <template v-if="sendStep===2">
        <div class="ss-success"><div class="ss-check">✓</div><h3>{{ isAr?'تم التحويل بنجاح!':'Transfer Successful!' }}</h3><p class="ss-succ-amt">€{{ sendAmt }}</p><p class="ss-succ-to">→ {{ isAr?'محمد علي':'Mohammad Ali' }}</p><p class="ss-succ-time">{{ isAr?'وصل خلال 8 ثوانٍ':'Arrived in 8 seconds' }}</p></div>
        <button class="ss-btn" @click="doSend">{{ isAr?'العودة':'Back to Home' }}</button>
      </template>
    </div>

    <!-- Tab Bar -->
    <div class="sim-tab">
      <div v-for="tab in tabItems" :key="tab.id" class="sim-tab-item" :class="{'sim-tab-active':screen===tab.id||(tab.id==='home'&&['send','rates'].includes(screen))}" @click="go(tab.id)">
        <span class="sim-tab-ic">{{ tab.ic }}</span><span class="sim-tab-l">{{ tab.l }}</span>
      </div>
    </div>
  </div>
</div>
</template>
<style scoped>
/* ── Phone Shell (iPhone 17 Pro: 280px wide, 5px bezels, 19.5:9 screen) ── */
.sim-phone{position:relative;width:280px;background:linear-gradient(145deg,#2a2a2e,#1a1a1e);border-radius:48px;padding:5px;box-shadow:0 24px 80px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.08) inset,inset 0 1px 0 rgba(255,255,255,.1)}
.sim-island{position:absolute;top:5px;left:50%;transform:translateX(-50%);width:90px;height:26px;background:#000;border-radius:999px;z-index:30}
.sim-screen{background:#F8FAFC;border-radius:43px;overflow:hidden;display:flex;flex-direction:column;height:606px}
.sim-status{display:flex;justify-content:space-between;align-items:center;padding:16px 28px 6px;font-size:10px;color:#0F172A;font-weight:600;flex-shrink:0;z-index:20}
.sim-status-r{display:flex;align-items:center;gap:3px;font-size:8px}.sim-sig{letter-spacing:-1px}.sim-wifi{font-size:10px}
.sim-batt{width:20px;height:9px;border:1.5px solid #0F172A;border-radius:2px;position:relative;margin-left:2px}.sim-batt::after{content:'';position:absolute;inset:1.5px;width:65%;background:#0F172A;border-radius:1px}.sim-batt::before{content:'';position:absolute;right:-3px;top:50%;transform:translateY(-50%);width:1.5px;height:4px;background:rgba(15,23,42,.6);border-radius:0 1px 1px 0}
.sim-body{flex:1;overflow-y:auto;overflow-x:hidden;padding:8px 16px 12px;-ms-overflow-style:none;scrollbar-width:none}.sim-body::-webkit-scrollbar{display:none}
.sim-fade{animation:simFadeIn .3s ease}
@keyframes simFadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
/* ── Nav ── */
.sim-nav{display:flex;justify-content:space-between;align-items:center;padding:4px 0 12px}.sim-back{font-size:24px;color:#10B981;cursor:pointer;font-weight:300;padding:0 8px}.sim-nav-t{font-size:15px;font-weight:800;color:#0F172A}
/* ── Tab Bar ── */
.sim-tab{display:flex;justify-content:space-around;padding:6px 8px 22px;background:#fff;border-top:1px solid #E2E8F0;flex-shrink:0}
.sim-tab-item{display:flex;flex-direction:column;align-items:center;gap:2px;cursor:pointer;padding:4px 10px;border-radius:10px;transition:all .2s}
.sim-tab-ic{font-size:16px;transition:transform .2s}.sim-tab-item:hover .sim-tab-ic{transform:scale(1.1)}
.sim-tab-l{font-size:9px;color:#64748B;font-weight:500;transition:color .2s}
.sim-tab-active{background:rgba(16,185,129,.1)}.sim-tab-active .sim-tab-l{color:#10B981;font-weight:600}.sim-tab-active .sim-tab-ic{transform:scale(1.05)}
/* ── HOME ── */
.sh-header{display:flex;justify-content:space-between;align-items:center;padding:0 0 8px}
.sh-logo{font-size:22px;font-weight:900;color:#10B981;letter-spacing:-0.5px}
.sh-bell{position:relative;font-size:18px;width:36px;height:36px;background:#F1F5F9;border-radius:18px;display:flex;align-items:center;justify-content:center;cursor:pointer}
.sh-bell-dot{position:absolute;top:8px;right:8px;width:6px;height:6px;background:#EF4444;border-radius:3px}
.sh-greet{font-size:18px;font-weight:800;color:#0F172A;margin-bottom:14px}
.sh-bal-card{background:linear-gradient(135deg,#0F172A,#1E293B);border-radius:18px;padding:18px;text-align:center;margin-bottom:14px}
.sh-bal-lbl{font-size:11px;color:rgba(255,255,255,.6);margin-bottom:4px}.sh-bal{font-size:30px;font-weight:900;color:#fff;margin:0}.sh-bal span{font-size:18px;color:rgba(255,255,255,.4)}
.sh-wallets{display:flex;gap:6px;overflow-x:auto;padding-bottom:12px;scrollbar-width:none}.sh-wallets::-webkit-scrollbar{display:none}
.sh-wallet-pill{display:flex;align-items:center;gap:5px;padding:8px 10px;background:#fff;border:1px solid #E2E8F0;border-radius:12px;white-space:nowrap;flex-shrink:0}
.sh-wf{font-size:14px}.sh-wc{font-size:10px;font-weight:700;color:#475569}.sh-wb{font-size:11px;font-weight:700;color:#0F172A}
.sh-actions{display:flex;gap:6px;margin-bottom:14px;overflow-x:auto;scrollbar-width:none}.sh-actions::-webkit-scrollbar{display:none}
.sh-pill{padding:8px 12px;background:#F1F5F9;border-radius:20px;font-size:11px;font-weight:600;color:#0F172A;white-space:nowrap;cursor:pointer;flex-shrink:0;transition:background .2s}.sh-pill:hover{background:#E2E8F0}
.sh-sec-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.sh-sec-title{font-size:13px;color:#475569;font-weight:700}
.sh-see-all{font-size:11px;color:#475569;font-weight:600;padding:4px 10px;background:#F1F5F9;border-radius:14px;cursor:pointer}
.sh-tx{display:flex;justify-content:space-between;align-items:center;padding:10px;background:#fff;border:1px solid #E2E8F0;border-radius:14px;margin-bottom:5px;cursor:pointer;transition:background .2s}.sh-tx:hover{background:#F8FAFC}
.sh-tx-l{display:flex;align-items:center;gap:10px}.sh-tx-ic{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:11px;flex-shrink:0}
.sh-tx-n{font-size:12px;font-weight:600;color:#0F172A}.sh-tx-d{font-size:10px;color:#64748B}
.sh-tx-a{font-size:12px;font-weight:800;color:#0F172A;white-space:nowrap}.sh-tx-pos{color:#10B981}
/* AI Card */
.sh-ai{display:flex;align-items:center;gap:10px;padding:12px;background:#F5F3FF;border-radius:14px;margin-top:10px;cursor:pointer}
.sh-ai-ic{width:36px;height:36px;background:rgba(139,92,246,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px}
.sh-ai-body{flex:1}.sh-ai-t{font-size:12px;font-weight:700;color:#0F172A}.sh-ai-s{font-size:10px;color:#64748B}
.sh-ai-arr{color:#64748B;font-size:16px}
/* ── CARDS ── */
.sc-card{position:relative;border-radius:18px;padding:20px;margin-bottom:8px;overflow:hidden;transition:all .3s;min-height:140px}
.sc-card::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.1) 0%,transparent 60%)}
.sc-frozen{filter:grayscale(.5);opacity:.7}.sc-frozen-overlay{position:absolute;inset:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;border-radius:18px}
.sc-card-top{display:flex;justify-content:space-between;margin-bottom:20px}.sc-brand{font-size:18px;font-weight:900;color:rgba(255,255,255,.9)}.sc-type{font-size:14px;font-weight:700;color:rgba(255,255,255,.5);letter-spacing:3px}
.sc-num{font-size:15px;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:3px;margin-bottom:14px;font-family:monospace}
.sc-card-bot{display:flex;justify-content:space-between}.sc-lbl{font-size:8px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px}.sc-name{font-size:12px;color:#fff;font-weight:700}
.sc-dots{display:flex;justify-content:center;gap:6px;margin-bottom:10px}.sc-dot{width:6px;height:6px;background:#E2E8F0;border-radius:3px;cursor:pointer;transition:all .2s}.sc-dot-active{width:18px;background:#10B981}
.sc-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:10px}
.sc-act{display:flex;flex-direction:column;align-items:center;gap:3px;padding:10px 6px;background:#F1F5F9;border-radius:12px;cursor:pointer;transition:background .2s;font-size:9px;color:#475569;font-weight:600}.sc-act:hover{background:#E2E8F0}.sc-act span:first-child{font-size:16px}
/* Wallet Rows */
.sw-row{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border:1px solid #E2E8F0;border-radius:14px;margin-bottom:5px;cursor:pointer;transition:all .2s}
.sw-row:hover,.sw-row-active{border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.03)}
.sw-flag{font-size:20px}.sw-info{flex:1}.sw-code{font-size:13px;font-weight:700;color:#0F172A}.sw-sub{font-size:9px;color:#64748B;font-family:monospace}
.sw-bal{font-size:14px;font-weight:800}
/* ── PAYMENTS ── */
.sp-hdr-ic{width:30px;height:30px;background:#F1F5F9;border-radius:15px;display:flex;align-items:center;justify-content:center;font-size:14px}
.sp-hdr-add{width:32px;height:32px;background:#F1F5F9;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#475569;cursor:pointer}
.sp-card-group{background:#fff;border:1px solid #E2E8F0;border-radius:14px;overflow:hidden}
.sp-pay-item{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #F1F5F9;cursor:pointer;transition:background .15s}.sp-pay-item:last-child{border-bottom:none}.sp-pay-item:hover{background:#F8FAFC}
.sp-pay-ic{width:34px;height:34px;background:#F1F5F9;border-radius:17px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.sp-pay-body{flex:1}.sp-pay-t{font-size:13px;font-weight:600;color:#0F172A}.sp-pay-s{font-size:10px;color:#64748B}
.sp-arr{color:#CBD5E1;font-size:16px;font-weight:300}
/* ── INSIGHTS ── */
.si-tabs{display:flex;padding:3px;background:#F1F5F9;border-radius:12px;margin-bottom:16px}
.si-tab{flex:1;text-align:center;padding:8px;font-size:11px;font-weight:500;color:#64748B;border-radius:9px;cursor:pointer;transition:all .2s}
.si-tab-on{background:#0F172A;color:#fff;font-weight:700}
.si-total{font-size:28px;font-weight:800;color:#0F172A;letter-spacing:-0.5px}.si-period{font-size:11px;color:#64748B;margin-bottom:16px}
.si-bars{display:flex;justify-content:space-evenly;align-items:flex-end;height:90px;margin-bottom:8px}
.si-bar-col{display:flex;flex-direction:column;align-items:center;gap:6px}
.si-bar-wrap{height:70px;display:flex;align-items:flex-end}.si-bar{width:28px;border-radius:6px;transition:height .6s cubic-bezier(.16,1,.3,1)}
.si-bar-lbl{font-size:10px;color:#64748B;font-weight:500}.si-bar-cur{color:#0F172A;font-weight:700}
.si-cat{display:flex;justify-content:space-between;align-items:center;padding:10px;background:#fff;border:1px solid #E2E8F0;border-radius:12px;margin-bottom:5px}
.si-cat-l{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#0F172A}
.si-cat-ic{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px}
.si-cat-v{font-size:12px;font-weight:700;color:#0F172A}
/* ── MORE/PROFILE ── */
.sm-header{text-align:center;padding:12px 0 16px;border-bottom:1px solid #E2E8F0;margin-bottom:12px}
.sm-avatar{width:52px;height:52px;border-radius:26px;background:linear-gradient(135deg,#10B981,#059669);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;color:#fff;margin:0 auto 8px}
.sm-name{font-size:15px;font-weight:800;color:#0F172A;margin:0}.sm-email{font-size:11px;color:#64748B;margin-top:2px}
.sm-badge{display:inline-block;margin-top:6px;padding:3px 10px;background:rgba(16,185,129,.1);color:#10B981;font-size:10px;font-weight:700;border-radius:999px}
.sm-logout{text-align:center;margin-top:14px;padding:10px;color:#EF4444;font-size:13px;font-weight:700;cursor:pointer}
/* ── SEND ── */
.ss-recipient{display:flex;align-items:center;gap:12px;padding:14px;background:#fff;border:1px solid #E2E8F0;border-radius:14px;margin-bottom:16px}
.ss-avatar{width:38px;height:38px;border-radius:19px;background:linear-gradient(135deg,#10B981,#059669);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:15px}
.ss-rname{font-size:13px;font-weight:700;color:#0F172A}.ss-rbank{font-size:10px;color:#64748B}
.ss-amt-wrap{text-align:center;margin:16px 0}.ss-amt-lbl{font-size:11px;color:#64748B;margin-bottom:6px}
.ss-amt-box{display:flex;align-items:center;justify-content:center;gap:4px}.ss-cur{font-size:26px;font-weight:300;color:#64748B}
.ss-amt-inp{font-size:32px;font-weight:900;color:#0F172A;background:none;border:none;outline:none;width:110px;text-align:center;font-family:inherit}
.ss-rate{font-size:11px;color:#10B981;margin-top:4px;font-weight:600}
.ss-det{background:#F1F5F9;border-radius:12px;padding:10px;margin-bottom:14px}.ss-det-r{display:flex;justify-content:space-between;padding:5px 0;font-size:11px;color:#64748B}.ss-det-v{color:#0F172A;font-weight:600}
.ss-btn{width:100%;padding:13px;background:#10B981;color:#fff;font-size:14px;font-weight:700;border:none;border-radius:14px;cursor:pointer;font-family:inherit;transition:all .2s}.ss-btn:hover{background:#059669;transform:scale(1.02)}
.ss-btn-confirm{background:#10B981}
.ss-confirm{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:14px;margin-bottom:16px}
.ss-conf-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F1F5F9;font-size:12px;color:#64748B}.ss-conf-row:last-child{border:none}
.ss-conf-val{color:#0F172A;font-weight:700}.ss-conf-total span:last-child{color:#10B981;font-size:15px;font-weight:900}
.ss-success{text-align:center;padding:28px 0}.ss-check{width:56px;height:56px;border-radius:28px;background:linear-gradient(135deg,#10B981,#059669);color:#fff;font-size:24px;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;animation:popIn .5s cubic-bezier(.16,1,.3,1)}
@keyframes popIn{from{transform:scale(0)}to{transform:scale(1)}}
.ss-success h3{color:#0F172A;font-size:16px;margin-bottom:6px}.ss-succ-amt{font-size:26px;font-weight:900;color:#10B981;margin:6px 0}.ss-succ-to{color:#64748B;font-size:12px}.ss-succ-time{color:#94A3B8;font-size:10px;margin-top:10px}
</style>
