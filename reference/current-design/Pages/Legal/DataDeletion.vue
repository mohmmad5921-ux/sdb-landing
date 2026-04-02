<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Head } from '@inertiajs/vue3';
import { inject, computed } from 'vue';
defineOptions({ layout: SiteLayout });
const isAr = inject('isAr', computed(() => true));
const t = computed(() => isAr.value ? {
  title: 'حذف البيانات — SDB Wallet',
  badge: '🗑️ حذف البيانات',
  h1: 'حذف بيانات المستخدم',
  sub: 'كيفية طلب حذف بياناتك من خدماتنا',
  date: 'آخر تحديث: مارس ٢٠٢٦',
  s1t: 'حقك في حذف بياناتك',
  s1p1: 'في SDB Wallet، نحترم حقك في حذف بياناتك الشخصية وفقاً للنظام العام لحماية البيانات (GDPR). يمكنك طلب حذف بياناتك في أي وقت.',
  s1p2: 'عند تقديم طلب حذف البيانات، سنقوم بإزالة جميع بياناتك الشخصية من أنظمتنا، مع مراعاة المتطلبات القانونية والتنظيمية التي قد تتطلب الاحتفاظ ببعض البيانات.',
  s2t: 'كيفية طلب حذف البيانات',
  s2items: [
    {ic:'📧',t:'عبر البريد الإلكتروني',d:'أرسل طلبك إلى dpo@sdbwallet.com مع ذكر اسمك الكامل وعنوان بريدك الإلكتروني المسجل'},
    {ic:'💬',t:'عبر التطبيق',d:'من داخل تطبيق SDB Wallet، اذهب إلى الإعدادات > الخصوصية > طلب حذف البيانات'},
    {ic:'📝',t:'عبر نموذج الدعم',d:'تواصل مع فريق الدعم عبر صفحة الدعم وحدد "طلب حذف بيانات"'},
  ],
  s3t: 'ما الذي يتم حذفه؟',
  s3items: [
    {ic:'👤',t:'البيانات الشخصية',d:'الاسم، البريد الإلكتروني، رقم الهاتف، العنوان'},
    {ic:'💳',t:'البيانات المالية',d:'معلومات الحساب والبطاقات (بعد انتهاء فترة الاحتفاظ القانونية)'},
    {ic:'📱',t:'بيانات الجهاز',d:'معرّفات الجهاز وسجلات تسجيل الدخول'},
    {ic:'🔗',t:'حسابات التواصل الاجتماعي',d:'ربط حسابات Apple وGoogle وFacebook'},
  ],
  s4t: 'فترة المعالجة',
  s4p: 'سنقوم بمعالجة طلبك خلال <strong>30 يوماً</strong> من تاريخ الاستلام. ستتلقى تأكيداً بالبريد الإلكتروني عند اكتمال عملية الحذف.',
  s5t: 'ملاحظات مهمة',
  s5items: [
    'قد نحتفظ ببعض البيانات لمدة 5 سنوات وفقاً لمتطلبات مكافحة غسيل الأموال',
    'حذف البيانات لا يمكن التراجع عنه',
    'ستفقد الوصول إلى حسابك ورصيدك بعد الحذف',
    'يجب تسوية أي أرصدة مستحقة قبل طلب الحذف',
  ],
  s6t: 'تواصل معنا',
  s6items: ['📧 dpo@sdbwallet.com','🏢 قسم حماية البيانات — SDB Wallet، أوروبا','💬 الدعم داخل التطبيق: متاح 24/7'],
} : {
  title: 'Data Deletion — SDB Wallet',
  badge: '🗑️ Data Deletion',
  h1: 'User Data Deletion',
  sub: 'How to request deletion of your data from our services',
  date: 'Last updated: March 2026',
  s1t: 'Your Right to Data Deletion',
  s1p1: 'At SDB Wallet, we respect your right to have your personal data deleted in accordance with the General Data Protection Regulation (GDPR). You can request deletion of your data at any time.',
  s1p2: 'Upon receiving a data deletion request, we will remove all your personal data from our systems, subject to legal and regulatory requirements that may require retention of certain data.',
  s2t: 'How to Request Data Deletion',
  s2items: [
    {ic:'📧',t:'Via Email',d:'Send your request to dpo@sdbwallet.com with your full name and registered email address'},
    {ic:'💬',t:'Via the App',d:'In the SDB Wallet app, go to Settings > Privacy > Request Data Deletion'},
    {ic:'📝',t:'Via Support Form',d:'Contact support and select "Data Deletion Request"'},
  ],
  s3t: 'What Gets Deleted?',
  s3items: [
    {ic:'👤',t:'Personal Data',d:'Name, email, phone number, address'},
    {ic:'💳',t:'Financial Data',d:'Account and card information (after legal retention period)'},
    {ic:'📱',t:'Device Data',d:'Device identifiers and login logs'},
    {ic:'🔗',t:'Social Accounts',d:'Linked Apple, Google, and Facebook accounts'},
  ],
  s4t: 'Processing Time',
  s4p: 'We will process your request within <strong>30 days</strong> of receipt. You will receive an email confirmation once the deletion is complete.',
  s5t: 'Important Notes',
  s5items: [
    'Some data may be retained for 5 years per anti-money laundering requirements',
    'Data deletion is irreversible',
    'You will lose access to your account and balance after deletion',
    'Any outstanding balances must be settled before requesting deletion',
  ],
  s6t: 'Contact Us',
  s6items: ['📧 dpo@sdbwallet.com','🏢 Data Protection Dept. — SDB Wallet, Europe','💬 In-app support: Available 24/7'],
});
</script>

<template>
<Head :title="t.title" />
<div class="lp" :style="{direction: isAr ? 'rtl' : 'ltr'}">
  <div class="lp-hero">
    <div class="lp-hero-glow"></div>
    <div class="lp-hero-inner">
      <div class="lp-badge">{{ t.badge }}</div>
      <h1 class="lp-h1">{{ t.h1 }}</h1>
      <p class="lp-sub">{{ t.sub }}</p>
      <p class="lp-date">{{ t.date }}</p>
    </div>
  </div>

  <div class="lp-body">
    <div class="lp-content">

      <section class="lp-s"><div class="lp-s-num">01</div><h2>{{ t.s1t }}</h2><p>{{ t.s1p1 }}</p><p>{{ t.s1p2 }}</p></section>

      <section class="lp-s"><div class="lp-s-num">02</div><h2>{{ t.s2t }}</h2>
        <div class="lp-list">
          <div v-for="it in t.s2items" :key="it.t" class="lp-item"><span class="lp-item-ic">{{ it.ic }}</span><div><strong>{{ it.t }}</strong><br>{{ it.d }}</div></div>
        </div>
      </section>

      <section class="lp-s"><div class="lp-s-num">03</div><h2>{{ t.s3t }}</h2>
        <div class="lp-list">
          <div v-for="it in t.s3items" :key="it.t" class="lp-item"><span class="lp-item-ic">{{ it.ic }}</span><div><strong>{{ it.t }}</strong><br>{{ it.d }}</div></div>
        </div>
      </section>

      <section class="lp-s"><div class="lp-s-num">04</div><h2>{{ t.s4t }}</h2><p v-html="t.s4p"></p></section>

      <section class="lp-s"><div class="lp-s-num">05</div><h2>{{ t.s5t }}</h2>
        <div class="lp-alert-list">
          <div v-for="n in t.s5items" :key="n" class="lp-note">⚠️ {{ n }}</div>
        </div>
      </section>

      <section class="lp-s lp-s-last"><div class="lp-s-num">06</div><h2>{{ t.s6t }}</h2>
        <div class="lp-contact"><div v-for="c in t.s6items" :key="c" class="lp-contact-item">{{ c }}</div></div>
      </section>

    </div>
  </div>
</div>
</template>

<style scoped>
.lp-hero{background:linear-gradient(135deg,#0a1628 0%,#132743 50%,#0d2137 100%);padding:80px 20px 60px;text-align:center;position:relative;overflow:hidden}
.lp-hero-glow{position:absolute;width:400px;height:400px;background:radial-gradient(circle,rgba(239,68,68,.15),transparent 70%);top:-100px;right:20%;pointer-events:none}
.lp-hero-inner{position:relative;z-index:1;max-width:700px;margin:0 auto}
.lp-badge{display:inline-block;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#ef4444;font-size:12px;font-weight:700;padding:6px 16px;border-radius:20px;margin-bottom:20px;letter-spacing:1px}
.lp-h1{font-size:42px;font-weight:900;color:#fff;margin-bottom:12px}
.lp-sub{font-size:16px;color:rgba(255,255,255,.55);margin-bottom:8px}
.lp-date{font-size:13px;color:rgba(255,255,255,.3)}
.lp-body{max-width:800px;margin:0 auto;padding:40px 20px 60px}
.lp-content{display:flex;flex-direction:column;gap:0}
.lp-s{padding:32px 0;border-bottom:1px solid rgba(0,0,0,.06)}
.lp-s-last{border-bottom:none}
.lp-s-num{font-size:12px;font-weight:900;color:#ef4444;letter-spacing:2px;margin-bottom:8px}
.lp-s h2{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:14px}
.lp-s p{font-size:15px;line-height:1.8;color:#475569;margin-bottom:10px}
.lp-s p:deep(strong){color:#0f172a}
.lp-list{display:flex;flex-direction:column;gap:10px}
.lp-item{display:flex;gap:14px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px}
.lp-item-ic{font-size:20px;flex-shrink:0}
.lp-item strong{color:#0f172a;font-size:14px}
.lp-item div{font-size:13px;color:#64748b;line-height:1.6}
.lp-alert-list{display:flex;flex-direction:column;gap:8px}
.lp-note{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 18px;font-size:14px;color:#991b1b;line-height:1.6}
.lp-contact{display:flex;flex-direction:column;gap:8px}
.lp-contact-item{font-size:15px;color:#334155;font-weight:600;padding:10px 0;border-bottom:1px solid #f1f5f9}
.lp-contact-item:last-child{border:none}
@media(max-width:768px){.lp-h1{font-size:28px}}
</style>
