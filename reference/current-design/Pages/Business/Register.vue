<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const step = ref(1);
const totalSteps = 3;

const form = useForm({
    business_name: '',
    business_name_ar: '',
    category: '',
    size: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: 'Syria',
    website: '',
    commercial_register: '',
    tax_number: '',
});

const categories = [
    { value: 'retail', label: 'تجارة تجزئة', icon: '🏪' },
    { value: 'ecommerce', label: 'تجارة إلكترونية', icon: '🛒' },
    { value: 'services', label: 'خدمات', icon: '🔧' },
    { value: 'food', label: 'مطاعم وكافيهات', icon: '🍕' },
    { value: 'education', label: 'تعليم', icon: '📚' },
    { value: 'healthcare', label: 'صحة', icon: '🏥' },
    { value: 'tech', label: 'تكنولوجيا', icon: '💻' },
    { value: 'real_estate', label: 'عقارات', icon: '🏗️' },
    { value: 'other', label: 'أخرى', icon: '📦' },
];

const sizes = [
    { value: 'solo', label: 'فردي', desc: 'شخص واحد' },
    { value: 'small', label: 'صغير', desc: '2-10 موظفين' },
    { value: 'medium', label: 'متوسط', desc: '11-50 موظف' },
    { value: 'large', label: 'كبير', desc: '50+ موظف' },
];

const nextStep = () => { if (step.value < totalSteps) step.value++; };
const prevStep = () => { if (step.value > 1) step.value--; };
const submit = () => form.post(route('business.register.submit'));
</script>

<template>
<Head title="تسجيل حساب تجاري — SDB" />
<div class="reg-root">
    <div class="reg-container">
        <!-- Header -->
        <div class="reg-header">
            <div class="reg-logo">SDB</div>
            <div class="reg-logo-sub">Business</div>
            <h1 class="reg-title">سجّل شركتك</h1>
            <p class="reg-desc">افتح حساب تجاري واستفد من الفواتير، الأقساط، POS، وأكثر</p>
        </div>

        <!-- Progress -->
        <div class="reg-progress">
            <div v-for="s in totalSteps" :key="s"
                 :class="['reg-step', s <= step ? 'reg-step-active' : '', s < step ? 'reg-step-done' : '']">
                <div class="reg-step-num">{{ s < step ? '✓' : s }}</div>
                <div class="reg-step-label">{{ ['معلومات الشركة', 'التواصل', 'التسجيل'][s-1] }}</div>
            </div>
        </div>

        <!-- Step 1: Business Info -->
        <div v-if="step === 1" class="reg-form">
            <div class="reg-field">
                <label>اسم الشركة (English) *</label>
                <input v-model="form.business_name" type="text" placeholder="My Company" />
            </div>
            <div class="reg-field">
                <label>اسم الشركة (عربي)</label>
                <input v-model="form.business_name_ar" type="text" placeholder="شركتي" />
            </div>

            <div class="reg-field">
                <label>نوع النشاط *</label>
                <div class="reg-categories">
                    <div v-for="c in categories" :key="c.value"
                         :class="['reg-cat', form.category === c.value && 'reg-cat-active']"
                         @click="form.category = c.value">
                        <span class="reg-cat-icon">{{ c.icon }}</span>
                        <span>{{ c.label }}</span>
                    </div>
                </div>
            </div>

            <div class="reg-field">
                <label>حجم الشركة *</label>
                <div class="reg-sizes">
                    <div v-for="s in sizes" :key="s.value"
                         :class="['reg-size', form.size === s.value && 'reg-size-active']"
                         @click="form.size = s.value">
                        <div class="reg-size-label">{{ s.label }}</div>
                        <div class="reg-size-desc">{{ s.desc }}</div>
                    </div>
                </div>
            </div>

            <button class="reg-btn" @click="nextStep" :disabled="!form.business_name || !form.category || !form.size">
                التالي →
            </button>
        </div>

        <!-- Step 2: Contact -->
        <div v-if="step === 2" class="reg-form">
            <div class="reg-field">
                <label>البريد الإلكتروني *</label>
                <input v-model="form.email" type="email" placeholder="info@company.com" />
            </div>
            <div class="reg-row">
                <div class="reg-field">
                    <label>الهاتف</label>
                    <input v-model="form.phone" type="text" placeholder="+963 xxx xxx xxx" />
                </div>
                <div class="reg-field">
                    <label>الموقع الإلكتروني</label>
                    <input v-model="form.website" type="url" placeholder="https://..." />
                </div>
            </div>
            <div class="reg-row">
                <div class="reg-field">
                    <label>المدينة</label>
                    <input v-model="form.city" type="text" placeholder="دمشق" />
                </div>
                <div class="reg-field">
                    <label>البلد</label>
                    <input v-model="form.country" type="text" />
                </div>
            </div>
            <div class="reg-field">
                <label>العنوان</label>
                <input v-model="form.address" type="text" placeholder="الشارع، الحي..." />
            </div>
            <div class="reg-btns">
                <button class="reg-btn reg-btn-sec" @click="prevStep">← السابق</button>
                <button class="reg-btn" @click="nextStep" :disabled="!form.email">التالي →</button>
            </div>
        </div>

        <!-- Step 3: Legal -->
        <div v-if="step === 3" class="reg-form">
            <div class="reg-field">
                <label>السجل التجاري (اختياري)</label>
                <input v-model="form.commercial_register" type="text" placeholder="رقم السجل التجاري" />
            </div>
            <div class="reg-field">
                <label>الرقم الضريبي (اختياري)</label>
                <input v-model="form.tax_number" type="text" placeholder="رقم التعريف الضريبي" />
            </div>

            <div class="reg-notice">
                <span>ℹ️</span>
                <span>بعد التسجيل، سيكون حسابك في <strong>الوضع التجريبي</strong> حتى يتم مراجعته والموافقة عليه من الإدارة.</span>
            </div>

            <div class="reg-btns">
                <button class="reg-btn reg-btn-sec" @click="prevStep">← السابق</button>
                <button class="reg-btn reg-btn-submit" @click="submit" :disabled="form.processing">
                    {{ form.processing ? '⏳ جاري...' : '🚀 سجّل الآن' }}
                </button>
            </div>

            <div v-if="form.errors" class="reg-errors">
                <div v-for="(err, key) in form.errors" :key="key" class="reg-error">{{ err }}</div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap');
.reg-root { min-height: 100vh; background: linear-gradient(135deg, #040810 0%, #0a1628 50%, #0f1d35 100%); display: flex; align-items: center; justify-content: center; font-family: 'Cairo', sans-serif; direction: rtl; color: #1a1a2e; padding: 40px 20px; }
.reg-container { width: 100%; max-width: 580px; }

.reg-header { text-align: center; margin-bottom: 32px; }
.reg-logo { font-family: 'Inter', sans-serif; font-size: 36px; font-weight: 900; letter-spacing: -2px; }
.reg-logo-sub { font-size: 11px; color: #6366f1; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-top: -6px; }
.reg-title { font-size: 24px; font-weight: 900; margin-top: 16px; }
.reg-desc { font-size: 13px; color: #6b7280; margin-top: 4px; }

.reg-progress { display: flex; justify-content: center; gap: 24px; margin-bottom: 28px; }
.reg-step { display: flex; align-items: center; gap: 8px; opacity: .3; transition: all .3s; }
.reg-step-active { opacity: 1; }
.reg-step-num { width: 28px; height: 28px; border-radius: 50%; background: #e8ecf1; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
.reg-step-active .reg-step-num { background: rgba(99,102,241,.15); border-color: #6366f1; color: #6366f1; }
.reg-step-done .reg-step-num { background: rgba(16,185,129,.15); border-color: #10b981; color: #10b981; }
.reg-step-label { font-size: 11px; font-weight: 600; }

.reg-form { background: #f8f9fb; border: 1px solid #e8ecf1; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 18px; }
.reg-field { display: flex; flex-direction: column; gap: 6px; }
.reg-field label { font-size: 12px; font-weight: 700; color: #6b7280; }
.reg-field input { background: #f1f5f9; border: 1px solid #d1d5db; border-radius: 12px; padding: 12px 14px; color: #1a1a2e; font-size: 14px; font-family: 'Cairo', sans-serif; outline: none; transition: all .2s; }
.reg-field input:focus { border-color: #6366f1; background: rgba(99,102,241,.05); }
.reg-field input::placeholder { color: #9ca3af; }
.reg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.reg-categories { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.reg-cat { display: flex; align-items: center; gap: 6px; padding: 10px; border-radius: 12px; background: #f8f9fb; border: 1px solid #e8ecf1; cursor: pointer; font-size: 12px; font-weight: 600; transition: all .2s; }
.reg-cat:hover { border-color: #9ca3af; }
.reg-cat-active { background: rgba(99,102,241,.1); border-color: #6366f1; color: #818cf8; }
.reg-cat-icon { font-size: 18px; }

.reg-sizes { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.reg-size { padding: 12px; border-radius: 12px; background: #f8f9fb; border: 1px solid #e8ecf1; cursor: pointer; text-align: center; transition: all .2s; }
.reg-size:hover { border-color: #9ca3af; }
.reg-size-active { background: rgba(99,102,241,.1); border-color: #6366f1; }
.reg-size-label { font-size: 13px; font-weight: 800; }
.reg-size-desc { font-size: 10px; color: #6b7280; }

.reg-btn { width: 100%; padding: 14px; border-radius: 14px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #1a1a2e; font-size: 15px; font-weight: 800; font-family: 'Cairo', sans-serif; border: none; cursor: pointer; transition: all .2s; }
.reg-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(99,102,241,.3); }
.reg-btn:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }
.reg-btn-sec { background: #e8ecf1; }
.reg-btn-sec:hover { background: #d1d5db; box-shadow: none; }
.reg-btn-submit { background: linear-gradient(135deg, #10b981, #059669); }
.reg-btn-submit:hover { box-shadow: 0 8px 24px rgba(16,185,129,.3); }
.reg-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.reg-notice { display: flex; gap: 8px; padding: 14px; border-radius: 12px; background: rgba(245,158,11,.06); border: 1px solid rgba(245,158,11,.15); font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.6; }
.reg-errors { display: flex; flex-direction: column; gap: 4px; }
.reg-error { font-size: 12px; color: #ef4444; }
</style>
