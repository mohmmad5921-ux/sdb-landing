<script setup>
import { Head } from '@inertiajs/vue3';
import { inject, computed } from 'vue';
import SiteLayout from '@/Layouts/SiteLayout.vue';
defineOptions({ layout: SiteLayout });
const isAr = inject('isAr', computed(() => true));
const t = computed(() => isAr.value ? {
  title:'الصحافة — SDB Wallet',tag:'صحافة',
  heroH:'غرفة الأخبار.',heroEm:'SDB Wallet.',
  heroP:'بيانات صحفية، موارد إعلامية، وتواصل مع فريق العلاقات العامة.',
  releases:[
    {date:'مارس 2026',t:'SDB Wallet يفتح التسجيل المسبق لتحكّم بأموالك، من أي مكان',d:'SDB Wallet — الشركة المسجّلة في أوروبا — تعلن فتح التسجيل المسبق لخدماتها المالية الرقمية الموجّهة للسوريين.'},
    {date:'فبراير 2026',t:'SDB Wallet يحصل على عضوية Mastercard',d:'SDB Wallet ينضم لشبكة ماستركارد العالمية — بطاقات مقبولة في 90+ مليون نقطة بيع حول العالم.'},
    {date:'يناير 2026',t:'إطلاق حاسبة صرف العملات بـ 30+ عملة',d:'SDB Wallet يطلق حاسبة صرف حية تدعم 30 عملة بأسعار السوق الحقيقية بدون هوامش.'},
    {date:'ديسمبر 2025',t:'SDB Wallet يحصل على ترخيص خدمات مالية أوروبي',d:'الشركة تحصل على ترخيص رسمي من الجهات الرقابية المالية الأوروبية.'},
  ],
  mediaTitle:'موارد إعلامية',
  mediaDesc:'حمّل شعارات SDB Wallet بالصيغ المختلفة، ألوان العلامة، ومعلومات الشركة.',
  media:['شعار SDB (SVG)','شعار SDB (PNG)','دليل العلامة التجارية','صور المنتج','معلومات الشركة','بيانات صحفية سابقة'],
  factsTitle:'حقائق سريعة',
  facts:[
    {l:'الاسم القانوني',v:'SDB Wallet'},{l:'التأسيس',v:'2025'},{l:'المقر',v:'أوروبا'},{l:'الترخيص',v:'EU Regulated'},{l:'الهدف',v:'تحكّم بأموالك، من أي مكان'},{l:'العملات',v:'30+'}
  ],
  contactTitle:'تواصل معنا',contactEmail:'press@sdbwallet.com',contactNote:'للاستفسارات الإعلامية فقط. نرد خلال 24 ساعة.',
} : {
  title:'Press — SDB Wallet',tag:'Press',
  heroH:'Newsroom.',heroEm:'SDB Wallet.',
  heroP:'Press releases, media resources, and public relations contact.',
  releases:[
    {date:'March 2026',t:'SDB Wallet Opens Pre-registration for Digital Financial Services',d:'SDB Wallet — registered in Europe — announces pre-registration for its digital financial services targeting users worldwide.'},
    {date:'February 2026',t:'SDB Wallet Receives Mastercard Membership',d:'SDB Wallet joins the global Mastercard network — cards accepted at 90+ million points of sale worldwide.'},
    {date:'January 2026',t:'Live Currency Calculator Launch with 30+ Currencies',d:'SDB Wallet launches a live exchange calculator supporting 30 currencies at real market rates with no markup.'},
    {date:'December 2025',t:'SDB Wallet Receives European Financial Services License',d:'The company receives official licensing from the European financial regulatory authorities.'},
  ],
  mediaTitle:'Media Resources',
  mediaDesc:'Download SDB Wallet logos, brand colors, and company information.',
  media:['SDB Logo (SVG)','SDB Logo (PNG)','Brand Guidelines','Product Screenshots','Company Information','Past Press Releases'],
  factsTitle:'Quick Facts',
  facts:[
    {l:'Legal name',v:'SDB Wallet'},{l:'Founded',v:'2025'},{l:'HQ',v:'Europe'},{l:'License',v:'EU Regulated'},{l:'Mission',v:'Digital Financial Solutions'},{l:'Currencies',v:'30+'}
  ],
  contactTitle:'Contact Us',contactEmail:'press@sdbwallet.com',contactNote:'Media inquiries only. We respond within 24 hours.',
});
</script>
<template>
<Head :title="t.title" />
<section class="p-hero"><div class="sw tc"><div class="p-hero-tag">{{ t.tag }}</div><h1 class="p-hero-h">{{ t.heroH }}<br><span class="p-hero-em">{{ t.heroEm }}</span></h1><p class="p-hero-p">{{ t.heroP }}</p></div></section>
<section class="sec"><div class="sw"><div class="releases"><div v-for="r in t.releases" :key="r.t" class="rel-c"><span class="rel-date">{{ r.date }}</span><h3 class="rel-t">{{ r.t }}</h3><p class="rel-d">{{ r.d }}</p></div></div></div></section>
<section class="sec sec-alt"><div class="sw"><h2 class="t2 tc">{{ t.mediaTitle }}</h2><p class="tc" style="color:rgba(10,10,10,.4);margin-bottom:28px;font-size:14px">{{ t.mediaDesc }}</p><div class="media-g"><span v-for="m in t.media" :key="m" class="media-i">📁 {{ m }}</span></div></div></section>
<section class="sec"><div class="sw"><h2 class="t2 tc">{{ t.factsTitle }}</h2><div class="g3"><div v-for="f in t.facts" :key="f.l" class="fact-c"><div class="fact-l">{{ f.l }}</div><div class="fact-v">{{ f.v }}</div></div></div></div></section>
<section class="sec sec-sky tc"><div class="sw"><h2 class="t2 t2-w">{{ t.contactTitle }}</h2><a :href="'mailto:'+t.contactEmail" class="cta-btn">{{ t.contactEmail }}</a><p class="t2-sub t2-sub-w tc" style="margin:16px auto 0">{{ t.contactNote }}</p></div></section>
</template>
<style scoped>
.sw{max-width:1200px;margin:0 auto;padding:0 24px}.tc{text-align:center}
.sec{padding:80px 0}.sec-alt{background:#F5F9F3}.sec-sky{background:linear-gradient(135deg,#10481A,#1a3d00,#9FE870);color:#fff;padding:100px 0}
.t2{font-size:clamp(1.8rem,4vw,2.8rem);font-weight:900;margin-bottom:48px}.t2-w{color:#fff}
.t2-sub{font-size:14px;line-height:1.8;max-width:540px}.t2-sub-w{color:rgba(255,255,255,.4)}
.p-hero{padding:160px 0 60px;background:linear-gradient(135deg,#10481A,#1a3d00,#9FE870);color:#fff}
.p-hero-tag{font-size:12px;font-weight:800;letter-spacing:3px;color:rgba(255,255,255,.5);text-transform:uppercase;margin-bottom:20px}
.p-hero-h{font-size:clamp(2.2rem,5vw,3.8rem);font-weight:900;line-height:1.1;margin-bottom:16px}.p-hero-em{color:#9FE870}
.p-hero-p{font-size:17px;color:rgba(255,255,255,.5);max-width:560px;margin:0 auto;line-height:1.8}
.releases{display:flex;flex-direction:column;gap:16px}
.rel-c{padding:28px;background:#fff;border:1px solid rgba(159,232,112,.08);border-radius:18px;border-right:4px solid #9FE870;transition:all .3s}.rel-c:hover{box-shadow:0 8px 24px rgba(159,232,112,.06)}
.rtl .rel-c{border-right:none;border-left:4px solid #9FE870}
.rel-date{font-size:11px;font-weight:700;color:#9FE870;margin-bottom:8px;display:block}
.rel-t{font-size:16px;font-weight:800;color:#10481A;margin-bottom:8px;line-height:1.4}.rel-d{font-size:13px;color:rgba(10,10,10,.4);line-height:1.7}
.media-g{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;max-width:700px;margin:0 auto}
.media-i{font-size:13px;font-weight:600;color:#10481A;padding:14px;background:#fff;border:1px solid rgba(159,232,112,.08);border-radius:12px;cursor:pointer;transition:all .2s}.media-i:hover{box-shadow:0 4px 12px rgba(159,232,112,.06)}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;max-width:700px;margin:0 auto}
.fact-c{padding:20px;background:#fff;border:1px solid rgba(159,232,112,.08);border-radius:14px;text-align:center}
.fact-l{font-size:11px;font-weight:700;color:#9FE870;margin-bottom:4px;text-transform:uppercase}.fact-v{font-size:18px;font-weight:900;color:#10481A}
.cta-btn{display:inline-block;padding:16px 44px;background:#fff;color:#10481A;font-size:15px;font-weight:800;border-radius:12px;text-decoration:none;transition:all .2s}.cta-btn:hover{transform:translateY(-2px)}
@media(max-width:768px){.media-g,.g3{grid-template-columns:1fr}}
</style>
