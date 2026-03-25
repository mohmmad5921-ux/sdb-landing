<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head } from '@inertiajs/vue3';
defineOptions({ layout: EmployeeLayout });
</script>
<template>
<Head title="شروط الاستخدام" />
<div class="et">
  <h1 class="et-title">📋 شروط استخدام بوابة الموظفين</h1>
  <p class="et-date">آخر تحديث: مارس 2026</p>

  <div class="et-section et-ok">
    <h2>✅ صلاحيات الموظف</h2>
    <p>يملك الموظف الصلاحيات التالية فقط، ولا يحق له تجاوزها تحت أي ظرف:</p>
    <ul>
      <li>البحث عن العملاء بالاسم أو الهاتف أو الإيميل أو رقم الحساب</li>
      <li>الاطلاع على بيانات العميل الشخصية وأرصدته (قراءة فقط)</li>
      <li>الرد على تذاكر الدعم الفني وتغيير حالتها</li>
      <li>الرد على محادثات الشات مع العملاء</li>
      <li>تغيير وجهة الحوالات (إعادة توجيه لفرع أو وكيل آخر)</li>
    </ul>
  </div>

  <div class="et-section et-danger">
    <h2>🚫 ممنوعات صارمة</h2>
    <ul>
      <li>❌ يُمنع منعاً باتاً تعديل أرصدة العملاء أو إضافة/خصم أي مبالغ</li>
      <li>❌ يُمنع حذف أي حساب عميل أو تعطيله</li>
      <li>❌ يُمنع الوصول إلى لوحة تحكم الإدارة أو إعدادات النظام</li>
      <li>❌ يُمنع تصدير أو نسخ بيانات العملاء خارج النظام</li>
      <li>❌ <b>يُمنع تصوير الشاشة (Screenshot) أو تسجيلها بأي وسيلة كانت</b></li>
      <li>❌ <b>يُمنع نقل أو مشاركة أي بيانات عميل عبر واتساب أو إيميل شخصي أو أي وسيلة أخرى</b></li>
      <li>❌ يُمنع الإفصاح عن أي معلومات مالية أو شخصية لأي شخص خارج نطاق العمل</li>
      <li>❌ يُمنع مشاركة بيانات الدخول الخاصة بالموظف مع أي شخص آخر</li>
    </ul>
  </div>

  <div class="et-section et-monitor">
    <h2>🔍 المراقبة والتتبع</h2>
    <p>يتم تسجيل ومراقبة <b>جميع</b> أنشطة الموظف داخل البوابة بشكل تلقائي ودائم، بما في ذلك:</p>
    <ul>
      <li>📌 كل عملية بحث عن عميل والعميل الذي تم عرضه</li>
      <li>📌 كل رسالة أو رد تم إرسالها في التذاكر أو الشات</li>
      <li>📌 كل تعديل تم على حالة تذكرة أو وجهة حوالة</li>
      <li>📌 وقت تسجيل الدخول والخروج وعنوان IP والجهاز المستخدم</li>
    </ul>
    <p class="et-note">يحق للإدارة مراجعة هذه السجلات في أي وقت ودون إشعار مسبق.</p>
  </div>

  <div class="et-section et-penalty">
    <h2>⚖️ العقوبات والمسؤولية القانونية</h2>
    <p>أي مخالفة لهذه الشروط تُعرض الموظف للعقوبات التالية حسب شدتها:</p>
    <div class="et-levels">
      <div class="et-lv et-lv1"><span class="et-lv-tag">تنبيه</span><span>مخالفة بسيطة لأول مرة</span></div>
      <div class="et-lv et-lv2"><span class="et-lv-tag">تعليق فوري</span><span>تصوير شاشة أو مشاركة بيانات عميل</span></div>
      <div class="et-lv et-lv3"><span class="et-lv-tag">فصل + ملاحقة قانونية</span><span>تسريب بيانات عملاء أو استغلال الصلاحيات لمنفعة شخصية</span></div>
    </div>
    <p class="et-note">يتحمل الموظف <b>المسؤولية الكاملة</b> عن أي ضرر ناتج عن مخالفته لهذه الشروط، ويحق للبنك اتخاذ الإجراءات القانونية اللازمة بما في ذلك المطالبة بالتعويض المادي والمعنوي.</p>
  </div>

  <div class="et-warn">
    ⚠️ بدخولك إلى بوابة الموظفين، فإنك تُقر وتوافق على جميع الشروط المذكورة أعلاه وتتحمل كامل المسؤولية القانونية عن أي مخالفة.
  </div>
</div>
</template>

<style scoped>
.et{direction:rtl;max-width:800px;margin:0 auto}
.et-title{font-size:22px;font-weight:900;color:#0f172a;margin-bottom:4px}
.et-date{font-size:12px;color:#94a3b8;margin-bottom:24px}
.et-section{background:#fff;border-radius:16px;padding:20px 24px;margin-bottom:16px;border:1px solid #e8ebf0}
.et-section h2{font-size:16px;font-weight:900;margin-bottom:10px}
.et-section p{font-size:14px;color:#475569;line-height:1.7;margin-bottom:8px}
.et-section ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:6px}
.et-section li{font-size:13px;color:#334155;padding:8px 14px;border-radius:10px;line-height:1.6}
.et-ok h2{color:#059669}
.et-ok li{background:#ecfdf5;border:1px solid #a7f3d0}
.et-danger{border-color:#fecaca;background:#fff5f5}
.et-danger h2{color:#dc2626}
.et-danger li{background:#fff;border:1px solid #fecaca;color:#7f1d1d}
.et-monitor h2{color:#1d4ed8}
.et-monitor li{background:#eff6ff;border:1px solid #bfdbfe}
.et-note{margin-top:8px;font-size:13px;color:#64748b;font-style:italic}
.et-penalty h2{color:#92400e}
.et-penalty{border-color:#fde68a;background:#fffbeb}
.et-levels{display:flex;flex-direction:column;gap:8px;margin:12px 0}
.et-lv{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;font-size:13px;color:#334155}
.et-lv-tag{font-weight:800;font-size:12px;padding:5px 14px;border-radius:8px;flex-shrink:0;white-space:nowrap}
.et-lv1{background:#fefce8;border:1px solid #fde68a}.et-lv1 .et-lv-tag{background:#fbbf24;color:#78350f}
.et-lv2{background:#fff7ed;border:1px solid #fed7aa}.et-lv2 .et-lv-tag{background:#f97316;color:#fff}
.et-lv3{background:#fef2f2;border:1px solid #fecaca}.et-lv3 .et-lv-tag{background:#dc2626;color:#fff}
.et-warn{padding:16px 20px;background:#fef3c7;border:2px solid #f59e0b;border-radius:14px;font-size:14px;font-weight:700;color:#92400e;text-align:center;margin-top:8px}
</style>
