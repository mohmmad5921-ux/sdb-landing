<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const p = defineProps({ secret: String, qrUrl: String, email: String });

const form = useForm({ code: '' });
const submit = () => form.post(route('admin.2fa.setup.verify'), { onFinish: () => form.reset('code') });
</script>

<template>
<Head title="إعداد المصادقة الثنائية — SDB Wallet" />
<div class="s2-page">
  <div class="s2-bg">
    <div class="s2-circle s2-c1"></div>
    <div class="s2-circle s2-c2"></div>
  </div>

  <div class="s2-center">
    <div class="s2-logo">SDB<span class="sdb-flag"></span></div>

    <div class="s2-card">
      <div class="s2-lock">🔐</div>
      <h1 class="s2-title">إعداد المصادقة الثنائية</h1>
      <p class="s2-sub">امسح رمز QR بتطبيق Google Authenticator أو Microsoft Authenticator</p>

      <div class="s2-steps">
        <div class="s2-step">
          <span class="s2-step-n">1</span>
          <span>حمّل تطبيق <b>Google Authenticator</b> أو <b>Microsoft Authenticator</b></span>
        </div>
        <div class="s2-step">
          <span class="s2-step-n">2</span>
          <span>امسح رمز QR التالي من التطبيق</span>
        </div>
        <div class="s2-step">
          <span class="s2-step-n">3</span>
          <span>أدخل الرمز المكون من 6 أرقام للتفعيل</span>
        </div>
      </div>

      <div class="s2-qr">
        <img :src="qrUrl" alt="QR Code" class="s2-qr-img" />
      </div>

      <div class="s2-secret">
        <span class="s2-secret-lbl">أو أدخل المفتاح يدوياً:</span>
        <code class="s2-secret-code">{{ secret }}</code>
      </div>

      <form @submit.prevent="submit" class="s2-form">
        <div class="s2-field">
          <label class="s2-label">رمز التحقق (6 أرقام)</label>
          <input v-model="form.code" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}"
            placeholder="000000" class="s2-code-input" autofocus required />
          <InputError :message="form.errors.code" class="s2-error" />
        </div>

        <button type="submit" class="s2-btn" :disabled="form.processing || form.code.length !== 6">
          <span v-if="form.processing" class="s2-spinner"></span>
          {{ form.processing ? 'جاري التحقق...' : 'تفعيل المصادقة الثنائية ✅' }}
        </button>
      </form>

      <div class="s2-footer">🔒 هذه الخطوة إلزامية لحماية حساب الإدارة</div>
    </div>
  </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
.s2-page{min-height:100vh;background:#0f172a;position:relative;overflow:hidden;font-family:'Inter',system-ui,sans-serif;direction:rtl}
.s2-bg{position:absolute;inset:0;pointer-events:none;overflow:hidden}
.s2-circle{position:absolute;border-radius:50%;opacity:.08}
.s2-c1{width:600px;height:600px;background:radial-gradient(circle,#9FE870,transparent 70%);top:-20%;right:-15%}
.s2-c2{width:400px;height:400px;background:radial-gradient(circle,#2563eb,transparent 70%);bottom:-10%;left:-5%}
.s2-center{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.s2-logo{font-size:32px;font-weight:900;color:#fff;letter-spacing:-2px;margin-bottom:24px;display:inline-flex;align-items:center;gap:4px}
.s2-card{width:100%;max-width:460px;background:#fff;border-radius:24px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.s2-lock{text-align:center;font-size:36px;margin-bottom:12px}
.s2-title{font-size:20px;font-weight:900;color:#0f172a;text-align:center}
.s2-sub{font-size:13px;color:#64748b;text-align:center;margin-top:4px;margin-bottom:20px}
.s2-steps{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
.s2-step{display:flex;align-items:center;gap:10px;font-size:13px;color:#334155;padding:10px 14px;background:#f8fafc;border-radius:10px}
.s2-step-n{width:24px;height:24px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0}
.s2-qr{text-align:center;margin-bottom:16px;padding:20px;background:#f8fafc;border-radius:14px}
.s2-qr-img{width:200px;height:200px;border-radius:8px}
.s2-secret{text-align:center;margin-bottom:20px}
.s2-secret-lbl{font-size:11px;color:#94a3b8;display:block;margin-bottom:6px}
.s2-secret-code{font-size:16px;font-weight:800;color:#0f172a;background:#f1f5f9;padding:8px 16px;border-radius:8px;letter-spacing:2px;font-family:'Courier New',monospace}
.s2-form{display:flex;flex-direction:column;gap:14px}
.s2-field{display:flex;flex-direction:column;gap:4px}
.s2-label{font-size:12px;font-weight:700;color:#64748b}
.s2-code-input{width:100%;padding:16px;border:2px solid #e2e8f0;border-radius:14px;font-size:28px;font-weight:900;text-align:center;letter-spacing:8px;outline:none;transition:border .2s;font-family:'Inter',monospace}
.s2-code-input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.s2-error{font-size:12px;color:#ef4444;font-weight:600;text-align:center}
.s2-btn{width:100%;padding:15px;background:linear-gradient(135deg,#2D6A00,#3d8a00);color:#fff;border:none;border-radius:14px;font-size:15px;font-weight:800;cursor:pointer;transition:all .2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px}
.s2-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(45,106,0,.2)}
.s2-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.s2-spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.s2-footer{text-align:center;margin-top:16px;font-size:11px;color:#94a3b8;font-weight:600}
@media(max-width:480px){.s2-card{padding:24px 18px}}
</style>
