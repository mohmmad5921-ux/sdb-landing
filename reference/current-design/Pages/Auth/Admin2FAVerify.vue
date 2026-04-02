<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';

defineProps({ email: String });

const digits = ref(['','','','','','']);
const inputs = ref([]);
const form = useForm({ code: '' });

function onInput(i) {
  if (digits.value[i] && i < 5) nextTick(() => inputs.value[i+1]?.focus());
  form.code = digits.value.join('');
}
function onKeydown(e, i) {
  if (e.key === 'Backspace' && !digits.value[i] && i > 0) {
    nextTick(() => inputs.value[i-1]?.focus());
  }
}
function onPaste(e) {
  const txt = (e.clipboardData?.getData('text') || '').replace(/\D/g,'').slice(0,6);
  txt.split('').forEach((c,i) => digits.value[i] = c);
  form.code = digits.value.join('');
  if (txt.length >= 6) nextTick(() => inputs.value[5]?.focus());
  e.preventDefault();
}
const submit = () => form.post(route('admin.2fa.verify.submit'), { onFinish: () => { form.reset('code'); digits.value = ['','','','','','']; } });
</script>

<template>
<Head title="التحقق من الهوية — SDB Wallet" />
<div class="tfa">
  <!-- Animated background -->
  <div class="tfa-bg">
    <div class="tfa-orb tfa-orb1"></div>
    <div class="tfa-orb tfa-orb2"></div>
    <div class="tfa-orb tfa-orb3"></div>
    <div class="tfa-grid"></div>
  </div>

  <div class="tfa-wrap">
    <!-- Logo -->
    <div class="tfa-brand">
      <img src="/images/sdb-logo.png" alt="SDB" class="tfa-logo" />
    </div>

    <!-- Card -->
    <div class="tfa-card">
      <!-- Shield icon -->
      <div class="tfa-icon-wrap">
        <div class="tfa-icon-pulse"></div>
        <div class="tfa-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <path d="M9 12l2 2 4-4"/>
          </svg>
        </div>
      </div>

      <h1 class="tfa-title">التحقق من الهوية</h1>
      <p class="tfa-desc">أدخل رمز التحقق المكوّن من 6 أرقام من تطبيق <strong>Google Authenticator</strong></p>

      <!-- User email badge -->
      <div class="tfa-user">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
        <span>{{ email }}</span>
      </div>

      <form @submit.prevent="submit">
        <!-- 6 OTP boxes -->
        <div class="tfa-otp">
          <input v-for="(d,i) in 6" :key="i"
            :ref="el => inputs[i] = el"
            v-model="digits[i]"
            type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
            class="tfa-box" :class="{ filled: digits[i] }"
            :autofocus="i === 0"
            @input="onInput(i)"
            @keydown="onKeydown($event, i)"
            @paste="onPaste"
          />
        </div>
        <InputError :message="form.errors.code" class="tfa-err" />

        <button type="submit" class="tfa-btn" :disabled="form.processing || form.code.length !== 6">
          <span v-if="form.processing" class="tfa-spin"></span>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          {{ form.processing ? 'جاري التحقق...' : 'تأكيد الدخول' }}
        </button>
      </form>

      <!-- Help -->
      <div class="tfa-help">
        <div class="tfa-tip">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          افتح تطبيق المصادقة وانسخ الرمز
        </div>
      </div>
    </div>

    <!-- SSL badge -->
    <div class="tfa-ssl">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      اتصال مشفّر — 256-bit SSL
    </div>

    <p class="tfa-copy">© 2026 SDB Wallet — Syria Digital Bank</p>
  </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
*{box-sizing:border-box;margin:0;padding:0}

.tfa{min-height:100vh;background:#050d1a;position:relative;overflow:hidden;font-family:'Inter',system-ui,sans-serif;direction:rtl}

/* === Animated BG === */
.tfa-bg{position:absolute;inset:0;pointer-events:none;overflow:hidden}
.tfa-orb{position:absolute;border-radius:50%;filter:blur(80px);animation:float 20s ease-in-out infinite}
.tfa-orb1{width:400px;height:400px;background:rgba(16,72,26,.3);top:-10%;right:-5%;animation-delay:0s}
.tfa-orb2{width:300px;height:300px;background:rgba(159,232,112,.12);bottom:-5%;left:-5%;animation-delay:-7s}
.tfa-orb3{width:200px;height:200px;background:rgba(59,130,246,.08);top:40%;left:50%;animation-delay:-14s}
@keyframes float{0%,100%{transform:translate(0,0) scale(1)}25%{transform:translate(30px,-20px) scale(1.05)}50%{transform:translate(-20px,30px) scale(.95)}75%{transform:translate(15px,15px) scale(1.02)}}
.tfa-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(159,232,112,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(159,232,112,.02) 1px,transparent 1px);background-size:60px 60px}

.tfa-wrap{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}

/* === Logo === */
.tfa-brand{margin-bottom:28px;text-align:center}
.tfa-logo{height:56px;object-fit:contain;filter:brightness(0) invert(1)}

/* === Card === */
.tfa-card{width:100%;max-width:420px;background:rgba(255,255,255,.04);backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);border:1px solid rgba(255,255,255,.08);border-radius:28px;padding:44px 36px 36px;animation:cardIn .6s ease-out}
@keyframes cardIn{from{opacity:0;transform:translateY(20px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}

/* Shield icon */
.tfa-icon-wrap{position:relative;width:60px;height:60px;margin:0 auto 20px}
.tfa-icon-pulse{position:absolute;inset:-6px;border-radius:50%;background:rgba(159,232,112,.1);animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.2);opacity:0}}
.tfa-icon{width:60px;height:60px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#10481A,#2d6a00);border-radius:50%;color:#9FE870;position:relative;z-index:1;box-shadow:0 8px 24px rgba(16,72,26,.4)}

.tfa-title{font-size:24px;font-weight:900;color:#fff;text-align:center;letter-spacing:-.03em}
.tfa-desc{font-size:13px;color:rgba(255,255,255,.4);text-align:center;margin:8px 0 20px;line-height:1.6}
.tfa-desc strong{color:rgba(255,255,255,.7)}

/* User badge */
.tfa-user{display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:12px;color:rgba(255,255,255,.5);font-size:13px;margin-bottom:24px;direction:ltr}

/* OTP boxes */
.tfa-otp{display:flex;gap:10px;justify-content:center;margin-bottom:8px;direction:ltr}
.tfa-box{width:50px;height:58px;border:2px solid rgba(255,255,255,.08);border-radius:14px;background:rgba(255,255,255,.03);font-size:24px;font-weight:800;text-align:center;color:#fff;outline:none;transition:all .25s;font-family:'Inter',monospace;caret-color:#9FE870}
.tfa-box:focus{border-color:#9FE870;background:rgba(159,232,112,.06);box-shadow:0 0 0 3px rgba(159,232,112,.1)}
.tfa-box.filled{border-color:rgba(159,232,112,.4);background:rgba(159,232,112,.08)}
.tfa-err{font-size:12px;color:#f87171;font-weight:600;text-align:center;margin-bottom:8px}

/* Button */
.tfa-btn{width:100%;padding:16px;background:linear-gradient(135deg,#10481A,#2d6a00);color:#fff;border:none;border-radius:16px;font-size:16px;font-weight:800;cursor:pointer;transition:all .3s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;box-shadow:0 4px 20px rgba(16,72,26,.3)}
.tfa-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 32px rgba(16,72,26,.5);background:linear-gradient(135deg,#156322,#3d8a00)}
.tfa-btn:disabled{opacity:.4;cursor:not-allowed;transform:none}
.tfa-spin{width:18px;height:18px;border:2.5px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* Help */
.tfa-help{margin-top:20px;text-align:center}
.tfa-tip{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;background:rgba(234,179,8,.06);border:1px solid rgba(234,179,8,.12);border-radius:12px;font-size:12px;color:rgba(234,179,8,.7);font-weight:500}

/* SSL */
.tfa-ssl{display:inline-flex;align-items:center;gap:6px;margin-top:20px;padding:6px 14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05);border-radius:10px;font-size:11px;color:rgba(255,255,255,.2);font-weight:600}
.tfa-copy{margin-top:16px;font-size:11px;color:rgba(255,255,255,.1);text-align:center}

@media(max-width:480px){
  .tfa-card{padding:32px 20px 28px;border-radius:22px}
  .tfa-otp{gap:6px}
  .tfa-box{width:42px;height:50px;font-size:20px;border-radius:10px}
}
</style>
