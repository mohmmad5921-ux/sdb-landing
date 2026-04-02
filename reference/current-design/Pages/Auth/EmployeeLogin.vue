<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '', password: '', remember: false, portal: 'employee' });
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
<Head title="بوابة الموظفين — SDB Wallet" />
<div class="el-page">
  <div class="el-bg">
    <div class="el-circle el-c1"></div>
    <div class="el-circle el-c2"></div>
    <div class="el-circle el-c3"></div>
  </div>

  <div class="el-center">
    <div class="el-logo-area">
      <img src="/images/sdb-logo.png" alt="SDB Wallet" class="el-logo-img" />
    </div>

    <div class="el-card">
      <div class="el-lock">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <h1 class="el-title">بوابة الموظفين</h1>
      <p class="el-sub">الوصول مقيّد لموظفي الدعم الفني</p>

      <div v-if="status" class="el-alert">{{ status }}</div>

      <form @submit.prevent="submit" class="el-form">
        <div class="el-field">
          <label class="el-label">البريد الإلكتروني</label>
          <input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" placeholder="employee@sfrbank.dk" class="el-input" />
          <InputError :message="form.errors.email" class="el-error" />
        </div>

        <div class="el-field">
          <label class="el-label">كلمة المرور</label>
          <input id="password" type="password" v-model="form.password" required autocomplete="current-password" placeholder="••••••••" class="el-input" />
          <InputError :message="form.errors.password" class="el-error" />
        </div>

        <label class="el-check"><input type="checkbox" v-model="form.remember" /><span>تذكرني</span></label>

        <button type="submit" class="el-btn" :disabled="form.processing">
          <span v-if="form.processing" class="el-spinner"></span>
          {{ form.processing ? 'جاري الدخول...' : 'تسجيل الدخول' }}
        </button>
      </form>

      <div class="el-footer">
        <div class="el-sec">🔒 اتصال مشفّر — 256-bit SSL</div>
      </div>
    </div>

    <p class="el-copy">© 2026 SDB Wallet — Syria Digital Wallet</p>
  </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
*{box-sizing:border-box;margin:0;padding:0}

.el-page{min-height:100vh;background:#eef3f9;position:relative;overflow:hidden;font-family:'Inter',system-ui,sans-serif;direction:rtl}

.el-bg{position:absolute;inset:0;pointer-events:none;overflow:hidden}
.el-circle{position:absolute;border-radius:50%;opacity:.12}
.el-c1{width:500px;height:500px;background:radial-gradient(circle,#60a5fa,transparent 70%);top:-15%;right:-10%}
.el-c2{width:350px;height:350px;background:radial-gradient(circle,#1e3a5f,transparent 70%);bottom:-10%;left:-5%}
.el-c3{width:200px;height:200px;background:radial-gradient(circle,#38bdf8,transparent 70%);top:50%;left:30%}

.el-center{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}

.el-logo-area{margin-bottom:24px;text-align:center}
.el-logo-img{width:180px;height:auto}

.el-card{width:100%;max-width:400px;background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:24px;padding:40px 36px;box-shadow:0 16px 48px rgba(0,0,0,.06)}

.el-lock{width:48px;height:48px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1d4ed8,#60a5fa);color:#fff;border-radius:14px;margin:0 auto 16px}
.el-title{font-size:22px;font-weight:900;color:#0f172a;text-align:center;letter-spacing:-.03em}
.el-sub{font-size:13px;color:rgba(15,23,42,.35);text-align:center;margin-top:4px;margin-bottom:28px}

.el-alert{padding:12px;background:rgba(30,78,216,.06);border:1px solid rgba(30,78,216,.1);color:#1d4ed8;font-size:13px;font-weight:600;border-radius:12px;margin-bottom:16px;text-align:center}

.el-form{display:flex;flex-direction:column;gap:16px}
.el-field{display:flex;flex-direction:column;gap:5px}
.el-label{font-size:12px;font-weight:700;color:rgba(15,23,42,.5);letter-spacing:.3px}
.el-input{width:100%;padding:14px 16px;background:#f8fafc;border:1.5px solid rgba(15,23,42,.08);border-radius:12px;font-size:14px;color:#0f172a;outline:none;transition:all .2s;font-family:inherit}
.el-input:focus{border-color:#60a5fa;background:#fff;box-shadow:0 0 0 3px rgba(96,165,250,.12)}
.el-input::placeholder{color:rgba(15,23,42,.2)}
.el-error{font-size:12px;color:#ef4444;margin-top:2px;font-weight:600}

.el-check{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(15,23,42,.4);cursor:pointer}
.el-check input{width:16px;height:16px;accent-color:#1d4ed8;border-radius:4px}
.el-terms-check{display:flex;align-items:center;gap:8px;font-size:12px;color:#334155;cursor:pointer;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px}
.el-terms-check input{width:16px;height:16px;accent-color:#1d4ed8;flex-shrink:0}
.el-terms-link{color:#1d4ed8;text-decoration:underline;font-weight:700}

.el-btn{width:100%;padding:15px;background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;border:none;border-radius:14px;font-size:15px;font-weight:800;cursor:pointer;transition:all .2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:4px}
.el-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,78,216,.2)}
.el-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}

.el-spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

.el-footer{text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid rgba(15,23,42,.04);display:flex;flex-direction:column;gap:8px;align-items:center}
.el-sec{display:inline-block;padding:4px 12px;background:rgba(30,78,216,.04);border:1px solid rgba(15,23,42,.06);border-radius:8px;font-size:11px;color:rgba(15,23,42,.3);font-weight:600}
.el-terms{font-size:12px;color:#1d4ed8;text-decoration:none;font-weight:600;transition:color .15s}
.el-terms:hover{color:#1e40af;text-decoration:underline}

.el-copy{margin-top:24px;font-size:11px;color:rgba(15,23,42,.2);text-align:center}

@media(max-width:480px){
  .el-card{padding:28px 20px;border-radius:20px}
  .el-mark{font-size:28px}
}
</style>
