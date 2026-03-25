<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { inject, ref, computed, onMounted, onUnmounted } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
defineOptions({ layout: SiteLayout });
const isAr = inject('isAr', computed(() => true));
let obs;
onMounted(()=>{obs=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('vi');if(x.target.hasAttribute('data-stagger')){x.target.querySelectorAll(':scope > *').forEach((ch,i)=>{setTimeout(()=>ch.classList.add('vi'),i*100)})}}}),{threshold:.08,rootMargin:'0px 0px -40px 0px'});document.querySelectorAll('.an,.an-l,.an-r').forEach(el=>obs.observe(el));document.querySelectorAll('[data-stagger]').forEach(p=>{p.querySelectorAll(':scope > *').forEach(ch=>ch.classList.add('an'));obs.observe(p)})});
onUnmounted(()=>{obs?.disconnect()});

const t = computed(() => isAr.value ? {
  title:'الباقات والأسعار — SDB Wallet',
  heroTag:'الباقات',heroH:'اختر الباقة',heroEm:'المناسبة لك.',
  heroP:'حسابات شخصية وتجارية بمميزات استثنائية — عمولات منافسة، 5 بطاقات Mastercard، مساعد ذكي بالذكاء الاصطناعي، وتأمين ضد الاحتيال.',
  persTag:'حسابات شخصية',persTitle:'للأفراد',persSub:'حساب رقمي شامل مع بطاقات Mastercard وتحويلات عالمية.',
  bizTag:'حسابات تجارية',bizTitle:'للشركات',bizSub:'حلول مصرفية متكاملة لإدارة أعمالك.',
  personal:[
    {n:'Personal',p:'€9.99',p2:'/شهر',c:'#9FE870',cDark:'#2D6A00',pop:false,ic:'⭐',target:'للأفراد',link:'/preregister?type=personal&plan=personal',feats:[
      {t:'5 بطاقات Mastercard',h:true},{t:'5 حسابات',h:true},{t:'عمولة تحويل 2.5%',h:false},{t:'عمولة إيداع 2%',h:false},{t:'صرف عملات +3%',h:false},{t:'تداول كريبتو 2%',h:false},{t:'AI مساعد ذكي',h:true},{t:'تأمين ضد الاحتيال',h:true}
    ]},
    {n:'Plus',p:'€19.99',p2:'/شهر',c:'#7C3AED',cDark:'#4C1D95',pop:true,ic:'💎',target:'للعملاء المتقدمين',link:'/preregister?type=personal&plan=plus',feats:[
      {t:'5 بطاقات Mastercard',h:true},{t:'5 حسابات',h:true},{t:'عمولة تحويل 1.5%',h:true},{t:'عمولة إيداع 1%',h:true},{t:'صرف عملات +1.5%',h:true},{t:'تداول كريبتو 1%',h:true},{t:'كاشباك 1%',h:true},{t:'AI مساعد ذكي',h:true},{t:'تأمين ضد الاحتيال',h:true},{t:'دعم أولوية',h:true}
    ]},
  ],
  business:[
    {n:'Business',p:'€49',p2:'/شهر',c:'#3B82F6',cDark:'#1E3A5F',pop:false,ic:'🏢',target:'للشركات والمشاريع',link:'/preregister?type=business&plan=business',feats:[
      {t:'5 بطاقات Mastercard',h:true},{t:'5 حسابات',h:true},{t:'عمولة تحويل 1%',h:true},{t:'عمولة إيداع 0.8%',h:true},{t:'صرف عملات +1%',h:true},{t:'Payment Gateway',h:true},{t:'Net Bank Business',h:true},{t:'نظام محاسبة',h:true},{t:'فواتير',h:true},{t:'API Access',h:true},{t:'AI مساعد ذكي',h:true},{t:'تأمين ضد الاحتيال',h:true},{t:'دعم أولوية',h:true}
    ]},
    {n:'Enterprise',p:'مخصص',p2:'',c:'#F59E0B',cDark:'#78350F',pop:false,ic:'🚀',target:'للمؤسسات الكبيرة',link:'/contact',feats:[
      {t:'كل ميزات Business',h:true},{t:'عمولات مخصصة',h:true},{t:'Web Shop متكامل',h:true},{t:'ربط ERP / SAP',h:true},{t:'تطوير حسب الطلب',h:true},{t:'مدير حساب مخصص',h:true},{t:'SLA مخصص',h:true}
    ]},
  ],
  allTag:'كل الباقات',allTitle:'مقارنة شاملة',allSub:'قارن كل المميزات والعمولات بين الباقات.',
  compH:['','Personal','Plus','Business','Enterprise'],
  compCat:[
    {cat:'العمولات',rows:[
      {f:'السعر الشهري',v:['€9.99','€19.99','€49','مخصص']},
      {f:'عمولة تحويل',v:['2.5%','1.5%','1%','مخصص']},
      {f:'عمولة إيداع',v:['2%','1%','0.8%','مخصص']},
      {f:'صرف عملات',v:['+3%','+1.5%','+1%','مخصص']},
      {f:'تداول كريبتو',v:['2%','1%','1%','مخصص']},
    ]},
    {cat:'البطاقات والحسابات',rows:[
      {f:'بطاقات Mastercard',v:['5','5','5','5']},
      {f:'حسابات',v:['5','5','5','5']},
    ]},
    {cat:'الميزات الذكية',rows:[
      {f:'AI مساعد ذكي',v:['✓','✓','✓','✓']},
      {f:'تأمين ضد الاحتيال',v:['✓','✓','✓','✓']},
      {f:'كاشباك',v:['—','1%','—','✓']},
      {f:'دعم أولوية',v:['—','✓','✓','✓']},
    ]},
    {cat:'ميزات الأعمال',rows:[
      {f:'Payment Gateway',v:['—','—','✓','✓']},
      {f:'Net Bank Business',v:['—','—','✓','✓']},
      {f:'نظام محاسبة',v:['—','—','✓','✓']},
      {f:'فواتير',v:['—','—','✓','✓']},
      {f:'API Access',v:['—','—','✓','✓']},
      {f:'Web Shop',v:['—','—','—','✓']},
      {f:'ربط ERP',v:['—','—','—','✓']},
    ]},
  ],
  faqTag:'الأسئلة الشائعة',faqTitle:'كل ما تريد معرفته',
  faqs:[
    {q:'هل يمكنني تغيير الباقة لاحقاً؟',a:'نعم — يمكنك الترقية أو التخفيض بأي وقت من التطبيق فوراً. لا يوجد عقود أو التزامات.'},
    {q:'ما الفرق بين Personal و Plus؟',a:'Plus يقدم عمولات أقل بكثير (1.5% بدل 2.5% للتحويل)، كاشباك 1% على كل المشتريات، ودعم أولوية.'},
    {q:'ماذا يتضمن حساب Business؟',a:'كل ما تحتاجه شركتك: Payment Gateway للموقع، نظام محاسبة، فواتير، صلاحيات موظفين، Net Bank Business، وAPI.'},
    {q:'هل AI المساعد الذكي مجاني؟',a:'نعم — متوفر مع كل الباقات بدون رسوم إضافية.'},
    {q:'هل التأمين ضد الاحتيال متوفر بكل الباقات؟',a:'نعم — تأمين ضد الاحتيال مضمّن مع كل الباقات.'},
  ],
  ctaTitle:'ابدأ اليوم',ctaSub:'اختر الباقة المناسبة وابدأ خلال دقائق.',ctaBtn:'افتح حسابك ←',ctaBtn2:'تواصل معنا',
} : {
  title:'Plans & Pricing — SDB Wallet',
  heroTag:'PLANS',heroH:'Choose the plan',heroEm:'that fits you.',
  heroP:'Personal and business accounts with exceptional features — competitive fees, 5 Mastercard cards, AI assistant, and fraud protection.',
  persTag:'PERSONAL ACCOUNTS',persTitle:'For individuals',persSub:'Complete digital account with Mastercard cards and global transfers.',
  bizTag:'BUSINESS ACCOUNTS',bizTitle:'For companies',bizSub:'Complete banking solutions for your business.',
  personal:[
    {n:'Personal',p:'€9.99',p2:'/month',c:'#9FE870',cDark:'#2D6A00',pop:false,ic:'⭐',target:'For individuals',link:'/preregister?type=personal&plan=personal',feats:[
      {t:'5 Mastercard cards',h:true},{t:'5 accounts',h:true},{t:'2.5% transfer fee',h:false},{t:'2% deposit fee',h:false},{t:'+3% currency exchange',h:false},{t:'2% crypto trading',h:false},{t:'AI assistant',h:true},{t:'Fraud protection',h:true}
    ]},
    {n:'Plus',p:'€19.99',p2:'/month',c:'#7C3AED',cDark:'#4C1D95',pop:true,ic:'💎',target:'For advanced users',link:'/preregister?type=personal&plan=plus',feats:[
      {t:'5 Mastercard cards',h:true},{t:'5 accounts',h:true},{t:'1.5% transfer fee',h:true},{t:'1% deposit fee',h:true},{t:'+1.5% currency exchange',h:true},{t:'1% crypto trading',h:true},{t:'1% Cashback',h:true},{t:'AI assistant',h:true},{t:'Fraud protection',h:true},{t:'Priority support',h:true}
    ]},
  ],
  business:[
    {n:'Business',p:'€49',p2:'/month',c:'#3B82F6',cDark:'#1E3A5F',pop:false,ic:'🏢',target:'Companies & startups',link:'/preregister?type=business&plan=business',feats:[
      {t:'5 Mastercard cards',h:true},{t:'5 accounts',h:true},{t:'1% transfer fee',h:true},{t:'0.8% deposit fee',h:true},{t:'+1% currency exchange',h:true},{t:'Payment Gateway',h:true},{t:'Net Bank Business',h:true},{t:'Accounting system',h:true},{t:'Invoicing',h:true},{t:'API Access',h:true},{t:'AI assistant',h:true},{t:'Fraud protection',h:true},{t:'Priority support',h:true}
    ]},
    {n:'Enterprise',p:'Custom',p2:'',c:'#F59E0B',cDark:'#78350F',pop:false,ic:'🚀',target:'Large enterprises',link:'/contact',feats:[
      {t:'All Business features',h:true},{t:'Custom fees',h:true},{t:'Full Web Shop',h:true},{t:'ERP / SAP integration',h:true},{t:'Custom development',h:true},{t:'Dedicated account manager',h:true},{t:'Custom SLA',h:true}
    ]},
  ],
  allTag:'ALL PLANS',allTitle:'Full Comparison',allSub:'Compare all features and fees across plans.',
  compH:['','Personal','Plus','Business','Enterprise'],
  compCat:[
    {cat:'Fees',rows:[
      {f:'Monthly price',v:['€9.99','€19.99','€49','Custom']},
      {f:'Transfer fee',v:['2.5%','1.5%','1%','Custom']},
      {f:'Deposit fee',v:['2%','1%','0.8%','Custom']},
      {f:'Currency exchange',v:['+3%','+1.5%','+1%','Custom']},
      {f:'Crypto trading',v:['2%','1%','1%','Custom']},
    ]},
    {cat:'Cards & Accounts',rows:[
      {f:'Mastercard cards',v:['5','5','5','5']},
      {f:'Accounts',v:['5','5','5','5']},
    ]},
    {cat:'Smart Features',rows:[
      {f:'AI assistant',v:['✓','✓','✓','✓']},
      {f:'Fraud protection',v:['✓','✓','✓','✓']},
      {f:'Cashback',v:['—','1%','—','✓']},
      {f:'Priority support',v:['—','✓','✓','✓']},
    ]},
    {cat:'Business Features',rows:[
      {f:'Payment Gateway',v:['—','—','✓','✓']},
      {f:'Net Bank Business',v:['—','—','✓','✓']},
      {f:'Accounting system',v:['—','—','✓','✓']},
      {f:'Invoicing',v:['—','—','✓','✓']},
      {f:'API Access',v:['—','—','✓','✓']},
      {f:'Web Shop',v:['—','—','—','✓']},
      {f:'ERP integration',v:['—','—','—','✓']},
    ]},
  ],
  faqTag:'FAQ',faqTitle:'Everything you need to know',
  faqs:[
    {q:'Can I change plans later?',a:'Yes — upgrade or downgrade anytime from the app instantly. No contracts or commitments.'},
    {q:'What\'s the difference between Personal and Plus?',a:'Plus offers much lower fees (1.5% vs 2.5% for transfers), 1% cashback on all purchases, and priority support.'},
    {q:'What does Business include?',a:'Everything your business needs: Payment Gateway, accounting system, invoicing, employee permissions, Net Bank Business, and API.'},
    {q:'Is the AI assistant free?',a:'Yes — included with all plans at no extra cost.'},
    {q:'Is fraud protection included in all plans?',a:'Yes — fraud protection is included with every plan.'},
  ],
  ctaTitle:'Start today',ctaSub:'Choose the right plan and get started in minutes.',ctaBtn:'Open your account →',ctaBtn2:'Contact us',
});

const faqOpen = ref(-1);
function toggleFaq(i){faqOpen.value=faqOpen.value===i?-1:i}
</script>
<template>
<Head :title="t.title" />

<!-- ═══════════ HERO ═══════════ -->
<section class="hero"><div class="hero-bg"></div>
<div class="hero-circles"><div class="hc hc1"></div><div class="hc hc2"></div><div class="hc hc3"></div></div>
<div class="sw tc" style="position:relative;z-index:1">
  <span class="pill an">{{ t.heroTag }}</span>
  <h1 class="hero-h1 an" style="transition-delay:.1s">{{ t.heroH }}<br><span class="hero-em">{{ t.heroEm }}</span></h1>
  <p class="hero-p an" style="transition-delay:.2s">{{ t.heroP }}</p>
</div></section>

<!-- ═══════════ PERSONAL PLANS ═══════════ -->
<section class="sec-white"><div class="sw">
  <div class="sec-hdr an"><span class="pill-green-border">{{ t.persTag }}</span><h2 class="t2">{{ t.persTitle }}</h2><p class="t2-sub">{{ t.persSub }}</p></div>
  <div class="plans-g" data-stagger>
    <div v-for="p in t.personal" :key="p.n" class="plan-card" :class="{'plan-pop':p.pop}">
      <div v-if="p.pop" class="plan-badge">{{ isAr?'الأكثر طلباً':'Most popular' }}</div>
      <div class="plan-top" :style="{background:`linear-gradient(135deg,${p.cDark},${p.c})`}">
        <span class="plan-ic">{{ p.ic }}</span>
        <h3 class="plan-name">{{ p.n }}</h3>
        <div class="plan-price"><span class="plan-p">{{ p.p }}</span><span class="plan-p2">{{ p.p2 }}</span></div>
        <span class="plan-target">{{ p.target }}</span>
      </div>
      <div class="plan-body">
        <ul class="plan-feats"><li v-for="f in p.feats" :key="f.t" :class="{'feat-hl':f.h}"><span class="feat-ck">✓</span>{{ f.t }}</li></ul>
        <a :href="p.link" class="plan-cta" :style="{background:`linear-gradient(135deg,${p.cDark},${p.c})`}">{{ isAr?'احصل على '+p.n:'Get '+p.n }} →</a>
      </div>
    </div>
  </div>
</div></section>

<!-- ═══════════ BUSINESS PLANS ═══════════ -->
<section class="sec-dark"><div class="sec-glow1"></div><div class="sec-glow2"></div>
<div class="sw" style="position:relative;z-index:1">
  <div class="sec-hdr an"><span class="pill-green-border">{{ t.bizTag }}</span><h2 class="t2" style="color:#fff">{{ t.bizTitle }}</h2><p class="t2-sub" style="color:rgba(255,255,255,.6)">{{ t.bizSub }}</p></div>
  <div class="plans-g" data-stagger>
    <div v-for="p in t.business" :key="p.n" class="plan-card plan-dark">
      <div class="plan-top" :style="{background:`linear-gradient(135deg,${p.cDark},${p.c})`}">
        <span class="plan-ic">{{ p.ic }}</span>
        <h3 class="plan-name">{{ p.n }}</h3>
        <div class="plan-price"><span class="plan-p">{{ p.p }}</span><span class="plan-p2">{{ p.p2 }}</span></div>
        <span class="plan-target">{{ p.target }}</span>
      </div>
      <div class="plan-body plan-body-dark">
        <ul class="plan-feats plan-feats-dark"><li v-for="f in p.feats" :key="f.t" :class="{'feat-hl-dark':f.h}"><span class="feat-ck feat-ck-dark">✓</span>{{ f.t }}</li></ul>
        <a :href="p.link" class="plan-cta" :style="{background:`linear-gradient(135deg,${p.cDark},${p.c})`}">{{ p.n==='Enterprise'?(isAr?'تواصل معنا':'Contact us'):(isAr?'احصل على '+p.n:'Get '+p.n) }} →</a>
      </div>
    </div>
  </div>
</div></section>

<!-- ═══════════ COMPARISON TABLE ═══════════ -->
<section class="sec-white"><div class="sw">
  <div class="sec-hdr an"><span class="pill">{{ t.allTag }}</span><h2 class="t2">{{ t.allTitle }}</h2><p class="t2-sub">{{ t.allSub }}</p></div>
  <div class="tbl-wrap an">
    <table class="tbl">
      <thead><tr><th v-for="h in t.compH" :key="h" :class="{'th-plan':h}">{{ h }}</th></tr></thead>
      <tbody>
        <template v-for="cat in t.compCat" :key="cat.cat">
          <tr class="tbl-cat-row"><td :colspan="5" class="tbl-cat">{{ cat.cat }}</td></tr>
          <tr v-for="r in cat.rows" :key="r.f">
            <td class="tbl-feat">{{ r.f }}</td>
            <td v-for="(v,i) in r.v" :key="i" :class="{'tbl-yes':v==='✓','tbl-no':v==='—'}">{{ v }}</td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div></section>

<!-- ═══════════ FAQ ═══════════ -->
<section class="sec-light"><div class="sw" style="max-width:800px">
  <div class="sec-hdr an"><span class="pill">{{ t.faqTag }}</span><h2 class="t2">{{ t.faqTitle }}</h2></div>
  <div class="faqs an"><div v-for="(f,i) in t.faqs" :key="i" class="faq" :class="{faqOpen:faqOpen===i}" @click="toggleFaq(i)">
    <div class="faq-q"><span>{{ f.q }}</span><span class="faq-arrow">{{ faqOpen===i?'−':'+' }}</span></div>
    <div class="faq-a" v-if="faqOpen===i">{{ f.a }}</div>
  </div></div>
</div></section>

<!-- ═══════════ CTA ═══════════ -->
<section class="sec-cta"><div class="sw tc">
  <h2 class="t2 an">{{ t.ctaTitle }}</h2>
  <p class="t2-sub an tc" style="margin:0 auto 32px">{{ t.ctaSub }}</p>
  <div class="cta-row an"><a href="/preregister" class="cta-btn">{{ t.ctaBtn }}</a><a href="/support" class="cta-btn2">{{ t.ctaBtn2 }}</a></div>
</div></section>
</template>
<style scoped>
.sw{max-width:1200px;margin:0 auto;padding:0 24px}.tc{text-align:center}
.sec-white{padding:100px 0;background:#fff;position:relative;overflow:hidden}
.sec-light{padding:100px 0;background:#F5F9F3;position:relative;overflow:hidden}
.sec-dark{padding:100px 0;background:linear-gradient(135deg,#163300,#1a3d00,#0f2600);position:relative;overflow:hidden;color:#fff}
.sec-cta{padding:120px 0;background:linear-gradient(135deg,#F0FBE8,#E8F5E0)}
.sec-glow1{position:absolute;top:0;right:0;width:600px;height:600px;background:rgba(159,232,112,.1);border-radius:50%;filter:blur(150px)}
.sec-glow2{position:absolute;bottom:0;left:0;width:500px;height:500px;background:rgba(159,232,112,.05);border-radius:50%;filter:blur(120px)}
.sec-hdr{text-align:center;margin-bottom:48px}
.pill{display:inline-block;padding:6px 16px;background:#E8F5E0;color:#2D6A00;font-size:12px;font-weight:700;border-radius:999px;margin-bottom:12px;letter-spacing:1px}
.pill-green-border{display:inline-block;padding:6px 16px;background:rgba(159,232,112,.2);color:#9FE870;font-size:12px;font-weight:700;border-radius:999px;margin-bottom:16px;border:1px solid rgba(159,232,112,.3)}
.t2{font-size:clamp(1.8rem,4vw,3rem);font-weight:900;line-height:1.1;letter-spacing:-.02em;margin-bottom:16px;color:#163300}.rtl .t2{letter-spacing:0}
.t2-sub{font-size:16px;color:#666;line-height:1.85;max-width:550px;margin:0 auto}
.hero{padding:160px 0 80px;position:relative;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,#E8F5E0,#F0FBE8,#fff)}
.hero-circles{position:absolute;inset:0;overflow:hidden}
.hc{position:absolute;border-radius:50%;filter:blur(80px)}.hc1{top:-20%;right:-10%;width:600px;height:600px;background:rgba(159,232,112,.3);animation:pulse 4s ease-in-out infinite}.hc2{bottom:-20%;left:-10%;width:500px;height:500px;background:rgba(159,232,112,.2);animation:pulse 6s ease-in-out infinite}.hc3{top:40%;left:30%;width:300px;height:300px;background:rgba(22,51,0,.05)}
.hero-h1{font-size:clamp(2.5rem,5.5vw,4.5rem);font-weight:900;color:#163300;line-height:1.05;letter-spacing:-.04em;margin-bottom:20px}.hero-em{color:#2D6A00}
.hero-p{font-size:18px;color:#555;line-height:1.85;margin:0 auto;max-width:650px}
.plans-g{display:grid;grid-template-columns:repeat(2,1fr);gap:24px;max-width:860px;margin:0 auto}
.plan-card{background:#fff;border-radius:28px;overflow:hidden;border:2px solid #f0f0f0;transition:all .4s;position:relative}.plan-card:hover{transform:translateY(-8px);box-shadow:0 24px 60px rgba(0,0,0,.1)}
.plan-dark{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1);backdrop-filter:blur(8px)}.plan-dark:hover{border-color:rgba(159,232,112,.3);box-shadow:0 24px 60px rgba(0,0,0,.3)}
.plan-pop{border-color:#7C3AED;box-shadow:0 12px 40px rgba(124,58,237,.15)}
.plan-badge{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.2);backdrop-filter:blur(8px);color:#fff;font-size:10px;font-weight:800;padding:6px 14px;border-radius:999px;z-index:2;border:1px solid rgba(255,255,255,.3)}
.rtl .plan-badge{right:auto;left:16px}
.plan-top{padding:36px 28px;text-align:center;color:#fff;position:relative}
.plan-ic{font-size:40px;display:block;margin-bottom:8px;filter:drop-shadow(0 4px 8px rgba(0,0,0,.2))}
.plan-name{font-size:26px;font-weight:900;margin-bottom:8px}
.plan-price{margin-bottom:8px}.plan-p{font-size:42px;font-weight:900}.plan-p2{font-size:14px;opacity:.7;margin-inline-start:4px}
.plan-target{font-size:11px;opacity:.6;font-weight:600;letter-spacing:1px;text-transform:uppercase}
.plan-body{padding:28px}
.plan-body-dark{background:rgba(255,255,255,.03)}
.plan-feats{list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:8px}
.plan-feats li{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;color:rgba(10,10,10,.5);padding:8px 0;border-bottom:1px solid rgba(10,10,10,.04);transition:color .2s}
.plan-feats-dark li{color:rgba(255,255,255,.4);border-color:rgba(255,255,255,.05)}
.feat-hl{color:#163300!important;font-weight:600}
.feat-hl-dark{color:rgba(255,255,255,.85)!important;font-weight:600}
.feat-ck{width:22px;height:22px;border-radius:50%;background:#E8F5E0;display:flex;align-items:center;justify-content:center;color:#2D6A00;font-size:11px;font-weight:900;flex-shrink:0}
.feat-ck-dark{background:rgba(159,232,112,.15);color:#9FE870}
.plan-cta{display:block;padding:16px;color:#fff;font-size:15px;font-weight:800;border-radius:16px;text-decoration:none;text-align:center;transition:all .2s;box-shadow:0 4px 16px rgba(0,0,0,.15)}.plan-cta:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2)}
/* Table */
.tbl-wrap{overflow-x:auto;border-radius:24px;border:2px solid #f0f0f0;box-shadow:0 8px 32px rgba(0,0,0,.04)}
.tbl{width:100%;border-collapse:collapse;font-size:13px;background:#fff}
.tbl thead{background:linear-gradient(135deg,#163300,#1a3d00)}
.tbl th{padding:16px 20px;color:#fff;font-weight:800;font-size:13px;text-align:center;white-space:nowrap}.tbl th:first-child{text-align:start;min-width:180px}
.th-plan{position:relative}
.tbl-cat-row{background:#F5F9F3}
.tbl-cat{padding:12px 20px;font-weight:800;color:#9FE870;font-size:12px;letter-spacing:1px;text-transform:uppercase;border-bottom:2px solid rgba(159,232,112,.1)}
.tbl-feat{padding:12px 20px;font-weight:600;color:#163300;text-align:start;border-bottom:1px solid rgba(0,0,0,.03)}
.tbl td{padding:12px 20px;text-align:center;border-bottom:1px solid rgba(0,0,0,.03);color:rgba(10,10,10,.5)}
.tbl-yes{color:#2D6A00!important;font-weight:700}.tbl-no{color:rgba(10,10,10,.12)!important}
.tbl tbody tr:hover{background:rgba(159,232,112,.03)}
/* FAQ */
.faqs{display:flex;flex-direction:column;gap:8px}
.faq{background:#fff;border:1px solid #e8e8e8;border-radius:16px;padding:16px 20px;cursor:pointer;transition:all .3s}.faq:hover,.faqOpen{border-color:#9FE870;box-shadow:0 4px 16px rgba(159,232,112,.1)}
.faq-q{display:flex;justify-content:space-between;align-items:center;font-size:15px;font-weight:700;color:#163300}
.faq-arrow{font-size:20px;color:#9FE870;font-weight:300;flex-shrink:0;margin-inline-start:16px}
.faq-a{margin-top:12px;font-size:14px;color:#666;line-height:1.8;padding-top:12px;border-top:1px solid #f0f0f0}
/* CTA */
.cta-row{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
.cta-btn{display:inline-block;padding:18px 48px;background:#163300;color:#fff;font-size:16px;font-weight:800;border-radius:999px;text-decoration:none;transition:all .3s}.cta-btn:hover{background:#1e4400;transform:translateY(-2px);box-shadow:0 12px 32px rgba(22,51,0,.15)}
.cta-btn2{display:inline-block;padding:18px 48px;background:transparent;color:#163300;font-size:16px;font-weight:800;border-radius:999px;text-decoration:none;border:2px solid rgba(22,51,0,.15);transition:all .2s}.cta-btn2:hover{border-color:#163300}
/* Animations */
.an{opacity:0;transform:translateY(28px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}.an.vi{opacity:1;transform:none}
.an-l{opacity:0;transform:translateX(-40px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}.an-l.vi{opacity:1;transform:none}
.an-r{opacity:0;transform:translateX(40px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}.an-r.vi{opacity:1;transform:none}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
@media(max-width:768px){.plans-g{grid-template-columns:1fr}.tbl th,.tbl td,.tbl-feat,.tbl-cat{padding:8px 12px;font-size:11px}.hero{padding:120px 0 40px}.sec-white,.sec-light,.sec-dark{padding:60px 0}.cta-btn,.cta-btn2{width:100%;text-align:center;padding:16px}.cta-row{flex-direction:column;padding:0 16px}}
</style>
