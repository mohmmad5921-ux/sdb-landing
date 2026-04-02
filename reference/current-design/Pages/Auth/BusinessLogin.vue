<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const step = ref('type'); // type → role → login
const accountType = ref(''); // business | agent
const subRole = ref(''); // admin | employee | branch

const form = useForm({ email: '', password: '', remember: false, portal: 'business' });

const selectType = (type) => {
    accountType.value = type;
    subRole.value = '';
    step.value = 'role';
};

const selectRole = (role) => {
    subRole.value = role;
    step.value = 'login';
};

const goBack = () => {
    if (step.value === 'login') { step.value = 'role'; subRole.value = ''; }
    else if (step.value === 'role') { step.value = 'type'; accountType.value = ''; }
};

const submit = () => form.post('/business/login', { onFinish: () => form.reset('password') });

const typeLabel = { business: 'حساب تجاري', agent: 'حساب وكيل' };
const roleLabels = {
    admin: 'أدمن رئيسي',
    employee: 'موظف',
    branch: 'فرع',
};
</script>

<template>
<Head title="بوابة الأعمال — SDB Wallet" />
<div class="bl-page">
  <div class="bl-container">
    <!-- Left: Visual Panel -->
    <div class="bl-left">
      <div class="bl-left-content">
        <div class="bl-left-top">
          <Link href="/business" class="bl-back-link">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            العودة لصفحة الأعمال
          </Link>
        </div>
        <div class="bl-left-center">
          <div class="bl-brand-logo">
            <img src="/images/sdb-business-logo-white.png" alt="SDB Business" class="bl-brand-logo-img" />
          </div>
          <h2 class="bl-brand-headline">نت واليت متكامل<br/>لأعمالك</h2>
          <div class="bl-brand-features">
            <div class="bl-bf"><span class="bl-bf-dot"></span>إدارة الموظفين والصلاحيات</div>
            <div class="bl-bf"><span class="bl-bf-dot"></span>فواتير وتقارير مالية متقدمة</div>
            <div class="bl-bf"><span class="bl-bf-dot"></span>فروع وكلاء في جميع المحافظات</div>
          </div>
        </div>
        <div class="bl-left-bottom">
          <div class="bl-trust-badges">
            <span>🏢 للشركات والمؤسسات</span>
            <span>🏦 لوكلاء الحوالات</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Login Steps -->
    <div class="bl-right">
      <div class="bl-card">
        <div class="bl-card-header">
          <div class="bl-card-logo">
            <img src="/images/sdb-business-logo.png" alt="SDB Business" class="bl-card-logo-img" />
          </div>
          <h1 class="bl-title">بوابة الأعمال</h1>
          <p class="bl-subtitle">سجّل دخولك إلى حسابك التجاري أو حساب الوكيل</p>
        </div>

        <!-- Progress Steps -->
        <div class="bl-steps">
          <div :class="['bl-step', step === 'type' ? 'active' : (step !== 'type' ? 'done' : '')]">
            <div class="bl-step-dot">{{ step === 'type' ? '1' : '✓' }}</div>
            <span>نوع الحساب</span>
          </div>
          <div class="bl-step-line" :class="{ active: step !== 'type' }"></div>
          <div :class="['bl-step', step === 'role' ? 'active' : (step === 'login' ? 'done' : '')]">
            <div class="bl-step-dot">{{ step === 'login' ? '✓' : '2' }}</div>
            <span>الدور</span>
          </div>
          <div class="bl-step-line" :class="{ active: step === 'login' }"></div>
          <div :class="['bl-step', step === 'login' ? 'active' : '']">
            <div class="bl-step-dot">3</div>
            <span>تسجيل</span>
          </div>
        </div>

        <div v-if="status" class="bl-alert">{{ status }}</div>

        <!-- Step 1: Account Type -->
        <div v-if="step === 'type'" class="bl-step-content">
          <p class="bl-step-label">اختر نوع حسابك</p>
          <div class="bl-type-grid">
            <button @click="selectType('business')" class="bl-type-card">
              <div class="bl-type-icon">🏢</div>
              <div class="bl-type-name">حساب تجاري</div>
              <div class="bl-type-desc">شركة أو مؤسسة تجارية</div>
              <div class="bl-type-feats">
                <span>👤 أدمن رئيسي</span>
                <span>👥 موظفين</span>
                <span>💰 حسابات</span>
              </div>
            </button>
            <button @click="selectType('agent')" class="bl-type-card agent">
              <div class="bl-type-icon">🏦</div>
              <div class="bl-type-name">حساب وكيل</div>
              <div class="bl-type-desc">مكتب حوالات في سوريا</div>
              <div class="bl-type-feats">
                <span>🏪 فروع</span>
                <span>👥 موظفين</span>
                <span>💸 حوالات</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Step 2: Sub-Role -->
        <div v-if="step === 'role'" class="bl-step-content">
          <p class="bl-step-label">
            {{ accountType === 'business' ? 'اختر دورك في الشركة' : 'اختر نوع الدخول' }}
          </p>

          <!-- Business Roles -->
          <div v-if="accountType === 'business'" class="bl-role-grid">
            <button @click="selectRole('admin')" class="bl-role-card">
              <div class="bl-role-icon">👑</div>
              <div class="bl-role-title">أدمن رئيسي</div>
              <div class="bl-role-desc">صلاحيات كاملة — إدارة الموظفين والحسابات</div>
            </button>
            <button @click="selectRole('employee')" class="bl-role-card">
              <div class="bl-role-icon">👤</div>
              <div class="bl-role-title">موظف</div>
              <div class="bl-role-desc">صلاحيات محدودة حسب ما يحدده الأدمن</div>
            </button>
          </div>

          <!-- Agent Roles -->
          <div v-if="accountType === 'agent'" class="bl-role-grid">
            <button @click="selectRole('admin')" class="bl-role-card">
              <div class="bl-role-icon">👑</div>
              <div class="bl-role-title">أدمن الوكيل</div>
              <div class="bl-role-desc">إدارة الفروع والموظفين والحوالات</div>
            </button>
            <button @click="selectRole('branch')" class="bl-role-card">
              <div class="bl-role-icon">🏪</div>
              <div class="bl-role-title">فرع</div>
              <div class="bl-role-desc">موظف فرع — تسليم حوالات وإدارة الفرع</div>
            </button>
          </div>

          <button @click="goBack" class="bl-back-btn">← رجوع</button>
        </div>

        <!-- Step 3: Login Form -->
        <div v-if="step === 'login'" class="bl-step-content">
          <div class="bl-selected-badge">
            <span>{{ accountType === 'business' ? '🏢' : '🏦' }}</span>
            <span>{{ typeLabel[accountType] }} — {{ roleLabels[subRole] }}</span>
          </div>

          <form @submit.prevent="submit" class="bl-form">
            <div class="bl-field">
              <label class="bl-label" for="email">البريد الإلكتروني</label>
              <input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" placeholder="you@company.com" class="bl-input" />
              <InputError :message="form.errors.email" class="bl-error" />
            </div>

            <div class="bl-field">
              <label class="bl-label" for="password">كلمة المرور</label>
              <input id="password" type="password" v-model="form.password" required autocomplete="current-password" placeholder="••••••••" class="bl-input" />
              <InputError :message="form.errors.password" class="bl-error" />
            </div>

            <div class="bl-options">
              <label class="bl-check">
                <input type="checkbox" v-model="form.remember" />
                <span>تذكرني</span>
              </label>
              <Link v-if="canResetPassword" :href="route('password.request')" class="bl-forgot">نسيت كلمة المرور؟</Link>
            </div>

            <button type="submit" class="bl-btn" :disabled="form.processing">
              <span v-if="form.processing" class="bl-spinner"></span>
              {{ form.processing ? 'جاري تسجيل الدخول...' : 'تسجيل الدخول' }}
            </button>
          </form>

          <button @click="goBack" class="bl-back-btn">← رجوع لاختيار الدور</button>
        </div>

        <div class="bl-footer">
          <span>ليس لديك حساب تجاري؟</span>
          <Link href="/preregister" class="bl-footer-link">قدّم طلب ←</Link>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Cairo:wght@400;600;700;800;900&display=swap');
*{box-sizing:border-box;margin:0;padding:0}

.bl-page{min-height:100vh;background:#fff;font-family:'Cairo','Inter',system-ui,sans-serif;direction:rtl}
.bl-container{display:flex;min-height:100vh}

/* ═══ Left Panel ═══ */
.bl-left{flex:1;background:linear-gradient(145deg,#0a2e0a 0%,#0f3d0f 40%,#1a5c1a 100%);display:flex;position:relative;overflow:hidden}
.bl-left::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 70%,rgba(159,232,112,.12) 0%,transparent 50%),radial-gradient(ellipse at 70% 30%,rgba(159,232,112,.06) 0%,transparent 50%)}
.bl-left-content{position:relative;z-index:1;display:flex;flex-direction:column;justify-content:space-between;padding:40px 60px;width:100%}
.bl-back-link{display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,.4);font-size:13px;font-weight:600;text-decoration:none;transition:color .2s;direction:ltr}
.bl-back-link:hover{color:rgba(255,255,255,.7)}
.bl-left-center{flex:1;display:flex;flex-direction:column;justify-content:center}
.bl-brand-logo{display:flex;align-items:center;direction:ltr;margin-bottom:16px}
.bl-brand-logo-img{height:60px;width:auto}
.bl-flag{display:inline-block;width:28px;height:28px;border-radius:50%;flex-shrink:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 120'%3E%3Ccircle cx='60' cy='60' r='60' fill='%23007a3d'/%3E%3Cpath d='M0 40h120v40H0z' fill='%23fff'/%3E%3Cpath d='M0 80h120v40a60 60 0 01-120 0z' fill='%23000'/%3E%3Cpath d='M0 0h120v40a0 0 0 010 0H0z' fill='%23007a3d'/%3E%3Cg fill='%23ce1126'%3E%3Cpolygon points='35,55 36.5,60 41.5,60 37.5,63 39,68 35,65 31,68 32.5,63 28.5,60 33.5,60'/%3E%3Cpolygon points='60,55 61.5,60 66.5,60 62.5,63 64,68 60,65 56,68 57.5,63 53.5,60 58.5,60'/%3E%3Cpolygon points='85,55 86.5,60 91.5,60 87.5,63 89,68 85,65 81,68 82.5,63 78.5,60 83.5,60'/%3E%3C/g%3E%3C/svg%3E");background-size:cover}
.bl-flag.sm{width:22px;height:22px}
.bl-brand-tagline{font-family:'Inter',sans-serif;font-size:10px;font-weight:700;color:rgba(255,255,255,.25);letter-spacing:4px;text-transform:uppercase;margin-bottom:32px;direction:ltr}
.bl-brand-headline{font-size:36px;font-weight:900;color:#fff;line-height:1.3;margin-bottom:40px}
.bl-brand-features{display:flex;flex-direction:column;gap:14px}
.bl-bf{display:flex;align-items:center;gap:12px;font-size:15px;color:rgba(255,255,255,.5);font-weight:600}
.bl-bf-dot{width:6px;height:6px;border-radius:50%;background:#9FE870;flex-shrink:0}
.bl-left-bottom{padding-top:20px}
.bl-trust-badges{display:flex;gap:24px;font-size:12px;color:rgba(255,255,255,.2);font-weight:500}

/* ═══ Right Panel ═══ */
.bl-right{flex:1;display:flex;align-items:center;justify-content:center;padding:40px;background:#fafafa}
.bl-card{width:100%;max-width:480px;background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:40px 36px;box-shadow:0 1px 3px rgba(0,0,0,.04),0 8px 24px rgba(0,0,0,.04)}
.bl-card-header{text-align:center;margin-bottom:24px}
.bl-card-logo{display:flex;align-items:center;justify-content:center;margin-bottom:12px;direction:ltr}
.bl-card-logo-img{height:42px;width:auto}
.bl-title{font-size:22px;font-weight:900;color:#0a0a0a}
.bl-subtitle{font-size:13px;color:#6b7280;margin-top:4px;font-weight:500}

/* Progress Steps */
.bl-steps{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:28px}
.bl-step{display:flex;align-items:center;gap:6px;font-size:11px;color:#9ca3af;font-weight:600}
.bl-step.active{color:#106F24}
.bl-step.done{color:#9FE870}
.bl-step-dot{width:24px;height:24px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#9ca3af;transition:all .3s}
.bl-step.active .bl-step-dot{background:#106F24;color:#fff}
.bl-step.done .bl-step-dot{background:#9FE870;color:#fff}
.bl-step-line{width:40px;height:2px;background:#e5e7eb;margin:0 8px;transition:background .3s}
.bl-step-line.active{background:#9FE870}

.bl-alert{padding:12px 16px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:13px;font-weight:600;border-radius:12px;margin-bottom:16px;text-align:center}

.bl-step-content{animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

.bl-step-label{font-size:15px;font-weight:800;color:#0a0a0a;margin-bottom:16px;text-align:center}

/* Type Cards */
.bl-type-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.bl-type-card{padding:24px 16px;background:#fff;border:2px solid #e5e7eb;border-radius:16px;cursor:pointer;transition:all .3s;text-align:center}
.bl-type-card:hover{border-color:#106F24;background:#f0fdf4;transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,122,61,.08)}
.bl-type-card.agent:hover{border-color:#7c3aed;background:#f5f3ff}
.bl-type-icon{font-size:36px;margin-bottom:8px}
.bl-type-name{font-size:16px;font-weight:900;color:#0a0a0a;margin-bottom:4px}
.bl-type-desc{font-size:11px;color:#6b7280;margin-bottom:12px;font-weight:500}
.bl-type-feats{display:flex;flex-wrap:wrap;gap:6px;justify-content:center}
.bl-type-feats span{font-size:10px;background:#f3f4f6;padding:3px 8px;border-radius:8px;color:#374151;font-weight:600}

/* Role Cards */
.bl-role-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.bl-role-card{padding:24px 16px;background:#fff;border:2px solid #e5e7eb;border-radius:16px;cursor:pointer;transition:all .3s;text-align:center}
.bl-role-card:hover{border-color:#106F24;background:#f0fdf4;transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,122,61,.08)}
.bl-role-icon{font-size:28px;margin-bottom:6px}
.bl-role-title{font-size:15px;font-weight:900;color:#0a0a0a;margin-bottom:4px}
.bl-role-desc{font-size:11px;color:#6b7280;font-weight:500;line-height:1.5}

.bl-back-btn{display:block;margin:16px auto 0;background:none;border:none;color:#106F24;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;padding:8px 16px;border-radius:8px;transition:all .2s}
.bl-back-btn:hover{background:#f0fdf4}

/* Selected Badge */
.bl-selected-badge{display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:700;color:#166534}

/* Form */
.bl-form{display:flex;flex-direction:column;gap:18px}
.bl-field{display:flex;flex-direction:column;gap:6px}
.bl-label{font-size:13px;font-weight:700;color:#374151}
.bl-input{width:100%;padding:13px 16px;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:12px;font-size:15px;color:#0a0a0a;outline:none;transition:all .2s;font-family:inherit}
.bl-input:focus{border-color:#106F24;background:#fff;box-shadow:0 0 0 3px rgba(0,122,61,.08)}
.bl-input::placeholder{color:#9ca3af}
.bl-error{font-size:12px;color:#dc2626;margin-top:4px}
.bl-options{display:flex;justify-content:space-between;align-items:center}
.bl-check{display:flex;align-items:center;gap:8px;font-size:13px;color:#6b7280;cursor:pointer;font-weight:500}
.bl-check input{width:16px;height:16px;accent-color:#106F24;border-radius:4px}
.bl-forgot{font-size:13px;color:#106F24;text-decoration:none;font-weight:700}
.bl-forgot:hover{text-decoration:underline}
.bl-btn{width:100%;padding:14px;background:linear-gradient(135deg,#106F24,#00a04d);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:800;cursor:pointer;transition:all .2s;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px}
.bl-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(0,122,61,.25)}
.bl-btn:active{transform:translateY(0)}
.bl-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.bl-spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

.bl-footer{text-align:center;font-size:14px;color:#6b7280;font-weight:500;margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6}
.bl-footer-link{color:#106F24;font-weight:800;text-decoration:none;margin-right:6px}
.bl-footer-link:hover{text-decoration:underline}

@media(max-width:900px){
  .bl-container{flex-direction:column}
  .bl-left{display:none}
  .bl-right{min-height:100vh;padding:24px;background:#fff}
  .bl-card{padding:32px 20px;border:none;box-shadow:none;max-width:100%}
  .bl-type-grid,.bl-role-grid{grid-template-columns:1fr}
}
</style>
