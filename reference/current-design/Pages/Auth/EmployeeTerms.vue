<script setup>
import { Head } from '@inertiajs/vue3';
</script>
<template>
<Head title="شروط استخدام بوابة الموظفين — SDB Wallet" />
<div class="st-page">
  <div class="st-center">
    <img src="/images/sdb-logo.png" alt="SDB Wallet" class="st-logo" />
    <div class="st-card">
      <h1 class="st-title">📋 شروط استخدام بوابة الموظفين</h1>
      <p class="st-date">آخر تحديث: مارس 2026</p>

      <div class="st-section">
        <h2>صلاحيات الموظف</h2>
        <p>يملك الموظف الصلاحيات التالية فقط، ولا يحق له تجاوزها تحت أي ظرف:</p>
        <ul>
          <li>✅ البحث عن العملاء بالاسم أو الهاتف أو الإيميل أو رقم الحساب</li>
          <li>✅ الاطلاع على بيانات العميل الشخصية وأرصدته (قراءة فقط)</li>
          <li>✅ الرد على تذاكر الدعم الفني وتغيير حالتها</li>
          <li>✅ الرد على محادثات الشات مع العملاء</li>
          <li>✅ تغيير وجهة الحوالات (إعادة توجيه لفرع أو وكيل آخر)</li>
        </ul>
      </div>

      <div class="st-danger">
        <h2>🚫 ممنوعات صارمة</h2>
        <ul>
          <li>❌ يُمنع منعاً باتاً تعديل أرصدة العملاء أو إضافة/خصم أي مبالغ</li>
          <li>❌ يُمنع حذف أي حساب عميل أو تعطيله</li>
          <li>❌ يُمنع الوصول إلى لوحة تحكم الإدارة أو إعدادات النظام</li>
          <li>❌ يُمنع تصدير أو نسخ بيانات العملاء خارج النظام</li>
          <li>❌ <b>يُمنع تصوير الشاشة (Screenshot) أو تسجيلها بأي وسيلة</b></li>
          <li>❌ <b>يُمنع نقل أو مشاركة أي بيانات عميل عبر واتساب أو إيميل شخصي أو أي وسيلة أخرى</b></li>
          <li>❌ يُمنع الإفصاح عن أي معلومات مالية أو شخصية لأي شخص خارج نطاق العمل</li>
          <li>❌ يُمنع مشاركة بيانات الدخول الخاصة بالموظف مع أي شخص آخر</li>
        </ul>
      </div>

      <div class="st-section">
        <h2>🔍 المراقبة والتتبع</h2>
        <p>يتم تسجيل ومراقبة <b>جميع</b> أنشطة الموظف داخل البوابة بشكل تلقائي ودائم، بما في ذلك:</p>
        <ul>
          <li>📌 كل عملية بحث عن عميل والعميل الذي تم عرضه</li>
          <li>📌 كل رسالة أو رد تم إرسالها في التذاكر أو الشات</li>
          <li>📌 كل تعديل تم على حالة تذكرة أو وجهة حوالة</li>
          <li>📌 وقت تسجيل الدخول والخروج وعنوان IP</li>
        </ul>
        <p style="margin-top:8px">يحق للإدارة مراجعة هذه السجلات في أي وقت ودون إشعار مسبق.</p>
      </div>

      <div class="st-penalty">
        <h2>⚖️ العقوبات والمسؤولية القانونية</h2>
        <p>أي مخالفة لهذه الشروط تُعرض الموظف للعقوبات التالية حسب شدتها:</p>
        <div class="st-penalty-grid">
          <div class="st-pen st-pen-1">
            <span class="st-pen-level">تنبيه</span>
            <span>مخالفة بسيطة لأول مرة</span>
          </div>
          <div class="st-pen st-pen-2">
            <span class="st-pen-level">تعليق فوري</span>
            <span>تصوير شاشة أو مشاركة بيانات</span>
          </div>
          <div class="st-pen st-pen-3">
            <span class="st-pen-level">فصل + ملاحقة قانونية</span>
            <span>تسريب بيانات عملاء أو استغلال الصلاحيات</span>
          </div>
        </div>
        <p style="margin-top:12px">يتحمل الموظف <b>المسؤولية الكاملة</b> عن أي ضرر ناتج عن مخالفته لهذه الشروط، ويحق للبنك اتخاذ الإجراءات القانونية اللازمة بما في ذلك المطالبة بالتعويض.</p>
      </div>

      <div class="st-warning">
        ⚠️ بتسجيل الدخول إلى بوابة الموظفين، فإنك تُقر وتوافق على جميع الشروط المذكورة أعلاه وتتحمل كامل المسؤولية عن أي مخالفة.
      </div>

      <a href="/staff/portal" class="st-back">← العودة إلى صفحة الدخول</a>
    </div>
    <p class="st-copy">© 2026 SDB Wallet — Syria Digital Wallet</p>
  </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
.st-page{min-height:100vh;background:#eef3f9;font-family:'Inter',system-ui,sans-serif;direction:rtl}
.st-center{max-width:700px;margin:0 auto;padding:40px 24px}
.st-logo{display:block;margin:0 auto 24px;width:140px;height:auto}
.st-card{background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:24px;padding:36px;box-shadow:0 8px 32px rgba(0,0,0,.04)}
.st-title{font-size:20px;font-weight:900;color:#0f172a;margin-bottom:4px}
.st-date{font-size:12px;color:#94a3b8;margin-bottom:24px}
.st-section{margin-bottom:20px}
.st-section h2{font-size:15px;font-weight:800;color:#1e293b;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #f1f5f9}
.st-section p{font-size:14px;color:#475569;line-height:1.7}
.st-section ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:6px}
.st-section li{font-size:13px;color:#334155;padding:6px 10px;background:#f8fafc;border-radius:8px;line-height:1.5}
.st-warning{margin-top:24px;padding:14px 18px;background:#fef3c7;border:1px solid #fde68a;border-radius:12px;font-size:13px;font-weight:700;color:#92400e;text-align:center}
.st-danger{margin-bottom:20px;padding:16px;background:#fef2f2;border:2px solid #fecaca;border-radius:14px}
.st-danger h2{font-size:16px;font-weight:900;color:#dc2626;margin-bottom:10px}
.st-danger ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:6px}
.st-danger li{font-size:13px;color:#7f1d1d;padding:8px 12px;background:#fff;border:1px solid #fecaca;border-radius:8px;line-height:1.5}
.st-penalty{margin-bottom:20px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px}
.st-penalty h2{font-size:15px;font-weight:900;color:#1e293b;margin-bottom:8px}
.st-penalty p{font-size:13px;color:#475569;line-height:1.6}
.st-penalty-grid{display:flex;flex-direction:column;gap:8px;margin-top:10px}
.st-pen{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;font-size:13px;color:#334155}
.st-pen-level{font-weight:800;font-size:12px;padding:4px 12px;border-radius:6px;flex-shrink:0}
.st-pen-1{background:#fefce8;border:1px solid #fde68a}.st-pen-1 .st-pen-level{background:#fbbf24;color:#78350f}
.st-pen-2{background:#fff7ed;border:1px solid #fed7aa}.st-pen-2 .st-pen-level{background:#f97316;color:#fff}
.st-pen-3{background:#fef2f2;border:1px solid #fecaca}.st-pen-3 .st-pen-level{background:#dc2626;color:#fff}
.st-back{display:block;text-align:center;margin-top:20px;font-size:14px;color:#1d4ed8;text-decoration:none;font-weight:600}
.st-back:hover{text-decoration:underline}
.st-copy{text-align:center;margin-top:20px;font-size:11px;color:rgba(15,23,42,.2)}
</style>
