<script setup>
import { Head, useForm } from '@inertiajs/vue3';
const p = defineProps({ step: String, qrUrl: String, secret: String, email: String, name: String });

const pwForm = useForm({ password: '', password_confirmation: '' });
const codeForm = useForm({ code: '' });

const submitPw = () => {
  pwForm.post('/employee/setup/password', {
    onSuccess: () => pwForm.reset(),
  });
};
const submitCode = () => {
  codeForm.post('/employee/setup/verify2fa');
};
</script>
<template>
<Head title="إعداد الحساب" />
<div class="su">
  <div class="su-card">
    <img src="/images/sdb-logo.png" alt="SDB Wallet" class="su-logo" />
    <h1 class="su-title">🔐 إعداد حسابك</h1>
    <p class="su-subtitle">مرحباً {{ name }}، يجب إكمال الخطوات التالية قبل البدء</p>

    <!-- Steps indicator -->
    <div class="su-steps">
      <div class="su-step" :class="{ active: step === 'password', done: step === '2fa' }">
        <span class="su-step-num">{{ step === '2fa' ? '✅' : '1' }}</span>
        <span>تغيير كلمة المرور</span>
      </div>
      <div class="su-step-line"></div>
      <div class="su-step" :class="{ active: step === '2fa' }">
        <span class="su-step-num">2</span>
        <span>تفعيل 2FA</span>
      </div>
    </div>

    <!-- Step 1: Password Change -->
    <div v-if="step === 'password'" class="su-section">
      <h2 class="su-sec-title">🔑 تغيير كلمة المرور</h2>
      <p class="su-sec-desc">كلمة المرور الحالية مؤقتة. يجب تغييرها الآن لحماية حسابك.</p>
      <form @submit.prevent="submitPw" class="su-form">
        <div class="su-field">
          <label>كلمة المرور الجديدة</label>
          <input type="password" v-model="pwForm.password" required minlength="8" placeholder="8 أحرف على الأقل..." />
          <div class="su-err" v-if="pwForm.errors.password">{{ pwForm.errors.password }}</div>
        </div>
        <div class="su-field">
          <label>تأكيد كلمة المرور</label>
          <input type="password" v-model="pwForm.password_confirmation" required placeholder="أعد كتابة كلمة المرور..." />
        </div>
        <button type="submit" class="su-btn" :disabled="pwForm.processing">
          {{ pwForm.processing ? 'جاري الحفظ...' : 'حفظ كلمة المرور والمتابعة →' }}
        </button>
      </form>
    </div>

    <!-- Step 2: 2FA Setup -->
    <div v-if="step === '2fa'" class="su-section">
      <h2 class="su-sec-title">📱 تفعيل المصادقة الثنائية (2FA)</h2>
      <p class="su-sec-desc">امسح رمز QR التالي باستخدام تطبيق <b>Google Authenticator</b> ثم أدخل الرمز المكوّن من 6 أرقام</p>
      
      <div class="su-qr-area">
        <img :src="qrUrl" alt="QR Code" class="su-qr" />
        <div class="su-secret">
          <span class="su-secret-label">🔑 المفتاح اليدوي:</span>
          <code class="su-secret-code">{{ secret }}</code>
        </div>
      </div>

      <form @submit.prevent="submitCode" class="su-form">
        <div class="su-field">
          <label>أدخل الرمز من التطبيق</label>
          <input type="text" v-model="codeForm.code" required maxlength="6" pattern="[0-9]{6}" 
            placeholder="000000" class="su-code-input" inputmode="numeric" autocomplete="one-time-code" />
          <div class="su-err" v-if="codeForm.errors.code">{{ codeForm.errors.code }}</div>
        </div>
        <button type="submit" class="su-btn su-btn-green" :disabled="codeForm.processing">
          {{ codeForm.processing ? 'جاري التحقق...' : 'تأكيد وتفعيل 2FA ✅' }}
        </button>
      </form>

      <div class="su-help">
        <p><b>كيف أستخدم Google Authenticator؟</b></p>
        <ol>
          <li>حمّل تطبيق <b>Google Authenticator</b> من App Store أو Play Store</li>
          <li>افتح التطبيق واضغط على <b>+</b> ثم <b>مسح رمز QR</b></li>
          <li>وجه الكاميرا إلى الرمز أعلاه</li>
          <li>أدخل الرمز المكون من 6 أرقام الذي يظهر</li>
        </ol>
      </div>
    </div>
  </div>
</div>
</template>
<style scoped>
.su{display:flex;align-items:center;justify-content:center;min-height:80vh;direction:rtl;padding:20px}
.su-card{background:#fff;border-radius:24px;padding:40px;max-width:520px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.su-logo{display:block;margin:0 auto 16px;width:100px;height:auto}
.su-title{text-align:center;font-size:24px;font-weight:900;color:#0f172a;margin:0 0 4px}
.su-subtitle{text-align:center;font-size:13px;color:#94a3b8;margin-bottom:24px}
/* Steps */
.su-steps{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:28px}
.su-step{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#94a3b8}
.su-step.active{color:#2563eb}
.su-step.done{color:#059669}
.su-step-num{width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#f1f5f9;font-size:11px;font-weight:900}
.su-step.active .su-step-num{background:#2563eb;color:#fff}
.su-step.done .su-step-num{background:#ecfdf5;color:#059669}
.su-step-line{width:40px;height:2px;background:#e2e8f0}
/* Section */
.su-section{margin-top:8px}
.su-sec-title{font-size:18px;font-weight:800;color:#0f172a;margin:0 0 6px}
.su-sec-desc{font-size:13px;color:#64748b;line-height:1.6;margin-bottom:20px}
/* Form */
.su-form{display:flex;flex-direction:column;gap:14px}
.su-field{display:flex;flex-direction:column;gap:4px}
.su-field label{font-size:12px;font-weight:700;color:#64748b}
.su-field input{padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:15px;outline:none;transition:border .2s;font-family:inherit}
.su-field input:focus{border-color:#2563eb}
.su-code-input{text-align:center;font-size:28px;font-weight:900;letter-spacing:12px;font-family:monospace}
.su-err{font-size:12px;color:#dc2626;font-weight:600;margin-top:2px}
.su-btn{padding:14px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;transition:all .15s}
.su-btn:hover{background:#1d4ed8}
.su-btn:disabled{opacity:.5;cursor:not-allowed}
.su-btn-green{background:#10481A}.su-btn-green:hover{background:#245800}
/* QR */
.su-qr-area{text-align:center;margin-bottom:20px;padding:20px;background:#f8fafc;border-radius:16px;border:1px solid #e2e8f0}
.su-qr{width:200px;height:200px;border-radius:12px;margin-bottom:12px}
.su-secret{margin-top:8px}
.su-secret-label{font-size:11px;color:#94a3b8;font-weight:700;display:block;margin-bottom:4px}
.su-secret-code{font-size:14px;font-weight:800;color:#0f172a;letter-spacing:2px;background:#fff;padding:6px 12px;border-radius:8px;border:1px solid #e2e8f0}
/* Help */
.su-help{margin-top:20px;padding:16px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px}
.su-help p{font-size:13px;font-weight:800;color:#0369a1;margin:0 0 8px}
.su-help ol{margin:0;padding:0 20px;font-size:12px;color:#475569;line-height:1.8}
.su-help b{color:#0f172a}
</style>
