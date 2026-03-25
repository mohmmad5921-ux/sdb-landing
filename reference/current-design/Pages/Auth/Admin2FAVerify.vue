<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ email: String });

const form = useForm({ code: '' });
const submit = () => form.post(route('admin.2fa.verify.submit'), { onFinish: () => form.reset('code') });
</script>

<template>
<Head title="التحقق من الهوية — SDB Wallet" />
<div class="v2-page">
  <div class="v2-bg">
    <div class="v2-circle v2-c1"></div>
    <div class="v2-circle v2-c2"></div>
  </div>

  <div class="v2-center">
    <div class="v2-logo">SDB<span class="sdb-flag"></span></div>

    <div class="v2-card">
      <div class="v2-shield">🛡️</div>
      <h1 class="v2-title">التحقق من الهوية</h1>
      <p class="v2-sub">أدخل الرمز من تطبيق المصادقة (Google Authenticator)</p>

      <div class="v2-info">
        <span>📧 {{ email }}</span>
      </div>

      <form @submit.prevent="submit" class="v2-form">
        <div class="v2-field">
          <input v-model="form.code" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}"
            placeholder="000000" class="v2-code-input" autofocus required />
          <InputError :message="form.errors.code" class="v2-error" />
        </div>

        <button type="submit" class="v2-btn" :disabled="form.processing || form.code.length !== 6">
          <span v-if="form.processing" class="v2-spinner"></span>
          {{ form.processing ? 'جاري التحقق...' : 'تسجيل الدخول 🔓' }}
        </button>
      </form>

      <div class="v2-footer">
        <div class="v2-tip">💡 افتح تطبيق المصادقة وانسخ الرمز المكون من 6 أرقام</div>
        <div class="v2-sec">🔒 اتصال مشفّر — 256-bit SSL</div>
      </div>
    </div>

    <p class="v2-copy">© 2026 SDB Wallet — Syria Digital Bank</p>
  </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
.v2-page{min-height:100vh;background:#0f172a;position:relative;overflow:hidden;font-family:'Inter',system-ui,sans-serif;direction:rtl}
.v2-bg{position:absolute;inset:0;pointer-events:none;overflow:hidden}
.v2-circle{position:absolute;border-radius:50%;opacity:.08}
.v2-c1{width:500px;height:500px;background:radial-gradient(circle,#9FE870,transparent 70%);top:-15%;right:-10%}
.v2-c2{width:300px;height:300px;background:radial-gradient(circle,#f59e0b,transparent 70%);bottom:-10%;left:10%}
.v2-center{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.v2-logo{font-size:32px;font-weight:900;color:#fff;letter-spacing:-2px;margin-bottom:24px;display:inline-flex;align-items:center;gap:4px}
.v2-card{width:100%;max-width:400px;background:#fff;border-radius:24px;padding:40px 36px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.v2-shield{text-align:center;font-size:40px;margin-bottom:12px}
.v2-title{font-size:22px;font-weight:900;color:#0f172a;text-align:center}
.v2-sub{font-size:13px;color:#64748b;text-align:center;margin-top:4px;margin-bottom:20px}
.v2-info{text-align:center;font-size:13px;color:#94a3b8;margin-bottom:20px;padding:10px;background:#f8fafc;border-radius:10px}
.v2-form{display:flex;flex-direction:column;gap:16px}
.v2-field{display:flex;flex-direction:column;gap:6px}
.v2-code-input{width:100%;padding:18px;border:2px solid #e2e8f0;border-radius:16px;font-size:32px;font-weight:900;text-align:center;letter-spacing:10px;outline:none;transition:all .2s;font-family:'Inter',monospace;color:#0f172a}
.v2-code-input:focus{border-color:#2D6A00;box-shadow:0 0 0 3px rgba(45,106,0,.12)}
.v2-error{font-size:12px;color:#ef4444;font-weight:600;text-align:center}
.v2-btn{width:100%;padding:16px;background:linear-gradient(135deg,#2D6A00,#3d8a00);color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:800;cursor:pointer;transition:all .2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px}
.v2-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(45,106,0,.25)}
.v2-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.v2-spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.v2-footer{text-align:center;margin-top:20px;display:flex;flex-direction:column;gap:8px}
.v2-tip{font-size:12px;color:#64748b;padding:8px 12px;background:#fef3c7;border-radius:10px;border:1px solid #fde68a}
.v2-sec{display:inline-block;padding:4px 12px;background:rgba(45,106,0,.04);border:1px solid rgba(15,23,42,.06);border-radius:8px;font-size:11px;color:#94a3b8;font-weight:600}
.v2-copy{margin-top:24px;font-size:11px;color:rgba(255,255,255,.2);text-align:center}
@media(max-width:480px){.v2-card{padding:28px 20px;border-radius:20px}.v2-code-input{font-size:24px;letter-spacing:6px}}
</style>
