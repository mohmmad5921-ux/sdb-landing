<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
const p = defineProps({ email: String, name: String });
const form = useForm({ code: '' });

const submit = () => {
  form.post('/employee/2fa-verify');
};
</script>
<template>
<Head title="تحقق المصادقة الثنائية" />
<div class="v2">
  <div class="v2-card">
    <div class="v2-icon">🔐</div>
    <h1 class="v2-title">المصادقة الثنائية</h1>
    <p class="v2-desc">مرحباً {{ name }}، أدخل الرمز من تطبيق <b>Google Authenticator</b> للمتابعة</p>

    <form @submit.prevent="submit" class="v2-form">
      <div class="v2-field">
        <input type="text" v-model="form.code" required maxlength="6" pattern="[0-9]{6}"
          placeholder="000000" class="v2-input" inputmode="numeric" autocomplete="one-time-code" autofocus />
        <div class="v2-err" v-if="form.errors.code">{{ form.errors.code }}</div>
      </div>
      <button type="submit" class="v2-btn" :disabled="form.processing">
        {{ form.processing ? 'جاري التحقق...' : 'تأكيد الدخول ✅' }}
      </button>
    </form>

    <Link href="/logout" method="post" as="button" class="v2-logout">تسجيل الخروج</Link>
  </div>
</div>
</template>
<style scoped>
.v2{display:flex;align-items:center;justify-content:center;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);direction:rtl;padding:20px}
.v2-card{background:#fff;border-radius:24px;padding:48px 40px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center}
.v2-icon{font-size:48px;margin-bottom:12px}
.v2-title{font-size:24px;font-weight:900;color:#0f172a;margin:0 0 8px}
.v2-desc{font-size:14px;color:#64748b;line-height:1.6;margin-bottom:28px}
.v2-desc b{color:#0f172a}
.v2-form{display:flex;flex-direction:column;gap:16px}
.v2-field{display:flex;flex-direction:column;gap:6px}
.v2-input{text-align:center;font-size:32px;font-weight:900;letter-spacing:14px;font-family:monospace;padding:16px;border:2px solid #e2e8f0;border-radius:14px;outline:none;transition:border .2s}
.v2-input:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.1)}
.v2-err{font-size:12px;color:#dc2626;font-weight:700}
.v2-btn{padding:16px;background:#2563eb;color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:800;cursor:pointer;font-family:inherit;transition:all .15s}
.v2-btn:hover{background:#1d4ed8;transform:translateY(-1px)}
.v2-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.v2-logout{display:inline-block;margin-top:20px;font-size:12px;color:#94a3b8;background:none;border:none;cursor:pointer;font-family:inherit}
.v2-logout:hover{color:#dc2626}
</style>
