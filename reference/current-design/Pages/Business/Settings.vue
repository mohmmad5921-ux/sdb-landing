<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ businessDetails: Object });
const b = props.businessDetails || {};

const form = useForm({
    business_name: b.business_name || '',
    business_name_ar: b.business_name_ar || '',
    email: b.email || '',
    phone: b.phone || '',
    address: b.address || '',
    city: b.city || '',
    website: b.website || '',
});

const logoForm = useForm({ logo: null });
const logoPreview = ref(b.logo_url || null);
const fileInput = ref(null);

const submit = () => form.post(route('business.settings.update'));
const catLabel = (c) => ({ retail: 'تجارة تجزئة', ecommerce: 'تجارة إلكترونية', services: 'خدمات', food: 'مطاعم', tech: 'تكنولوجيا' }[c] || c);

const onFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    logoForm.logo = file;
    logoPreview.value = URL.createObjectURL(file);
    // Auto-upload immediately
    logoForm.post(route('business.settings.logo'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { logoForm.reset(); },
    });
};

const uploadLogo = () => {
    if (!logoForm.logo) return;
    logoForm.post(route('business.settings.logo'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { logoForm.reset(); },
    });
};

const triggerFileInput = () => fileInput.value?.click();
</script>

<template>
<BusinessLayout title="الإعدادات">
    <div class="st-grid">
        <!-- Logo Upload -->
        <div class="st-section">
            <h2>🖼️ شعار الشركة</h2>
            <p class="st-hint">الشعار يظهر على الفواتير ونقطة البيع والمستندات</p>
            <div class="st-logo-area">
                <div class="st-logo-preview" @click="triggerFileInput">
                    <img v-if="logoPreview" :src="logoPreview" class="st-logo-img" />
                    <div v-else class="st-logo-placeholder">
                        <span>📷</span>
                        <span>اضغط لرفع الشعار</span>
                    </div>
                </div>
                <div class="st-logo-actions">
                    <input ref="fileInput" type="file" accept="image/*" @change="onFileSelect" style="display:none" />
                    <button class="st-btn st-btn-outline" @click="triggerFileInput">📁 اختر صورة</button>
                    <button v-if="logoForm.logo" class="st-btn st-btn-primary" @click="uploadLogo" :disabled="logoForm.processing">
                        {{ logoForm.processing ? '⏳ جارٍ الرفع...' : '✓ رفع الشعار' }}
                    </button>
                    <span class="st-hint">PNG, JPG, WEBP — حد أقصى 2MB</span>
                </div>
            </div>
        </div>

        <!-- Company Settings -->
        <div class="st-section">
            <h2>🏢 بيانات الشركة</h2>
            <div class="st-form">
                <div class="st-row">
                    <div class="st-f"><label>اسم الشركة</label><input v-model="form.business_name" /></div>
                    <div class="st-f"><label>الاسم بالعربي</label><input v-model="form.business_name_ar" /></div>
                </div>
                <div class="st-row">
                    <div class="st-f"><label>البريد الإلكتروني</label><input v-model="form.email" type="email" /></div>
                    <div class="st-f"><label>الهاتف</label><input v-model="form.phone" /></div>
                </div>
                <div class="st-row">
                    <div class="st-f"><label>المدينة</label><input v-model="form.city" /></div>
                    <div class="st-f"><label>الموقع</label><input v-model="form.website" /></div>
                </div>
                <div class="st-f full"><label>العنوان</label><input v-model="form.address" /></div>
                <button class="st-save" @click="submit" :disabled="form.processing">{{ form.processing ? '⏳' : '✓ حفظ التغييرات' }}</button>
            </div>
        </div>

        <!-- Readonly Info -->
        <div class="st-section st-readonly">
            <h2>📊 معلومات الحساب</h2>
            <div class="st-info-grid">
                <div><span class="st-label">النوع</span><span>{{ catLabel(b.category) }}</span></div>
                <div><span class="st-label">الحجم</span><span>{{ b.size }}</span></div>
                <div><span class="st-label">الحالة</span><span :class="b.status === 'active' ? 'st-active' : 'st-pending'">{{ b.status === 'active' ? '✅ نشط' : '⏳ ' + (b.status || 'pending') }}</span></div>
                <div><span class="st-label">المالك</span><span>{{ b.owner_name }}</span></div>
                <div><span class="st-label">السجل التجاري</span><span>{{ b.commercial_register || '—' }}</span></div>
                <div><span class="st-label">الرقم الضريبي</span><span>{{ b.tax_number || '—' }}</span></div>
                <div><span class="st-label">العمولة</span><span>{{ b.fee_percentage }}% + {{ b.fee_fixed }}</span></div>
                <div><span class="st-label">الحد اليومي</span><span>{{ Number(b.transfer_limit_daily || 0).toLocaleString() }}</span></div>
            </div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.st-grid { display: flex; flex-direction: column; gap: 20px; }
.st-section { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 24px; }
.st-section h2 { font-size: 15px; font-weight: 800; margin-bottom: 6px; color: #1a1a2e; }
.st-hint { font-size: 11px; color: #9ca3af; margin-bottom: 12px; }
.st-form { display: flex; flex-direction: column; gap: 14px; }
.st-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.st-f { display: flex; flex-direction: column; gap: 4px; }
.st-f.full { grid-column: 1 / -1; }
.st-f label { font-size: 11px; color: #6b7280; font-weight: 600; }
.st-f input { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; color: #1a1a2e; font-family: 'Cairo'; font-size: 13px; outline: none; }
.st-f input:focus { border-color: #106F24; }
.st-save { background: #106F24; color: #fff; border: none; padding: 12px; border-radius: 12px; font-weight: 800; font-family: 'Cairo'; cursor: pointer; font-size: 14px; margin-top: 4px; }
.st-save:hover { background: #0d5c1e; }
.st-save:disabled { opacity: .4; }

/* Logo */
.st-logo-area { display: flex; align-items: center; gap: 20px; }
.st-logo-preview { width: 120px; height: 120px; border-radius: 16px; border: 2px dashed #d1d5db; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; transition: all .2s; background: #f8f9fb; }
.st-logo-preview:hover { border-color: #106F24; }
.st-logo-img { width: 100%; height: 100%; object-fit: cover; }
.st-logo-placeholder { display: flex; flex-direction: column; align-items: center; gap: 4px; color: #9ca3af; font-size: 11px; }
.st-logo-placeholder span:first-child { font-size: 28px; }
.st-logo-actions { display: flex; flex-direction: column; gap: 8px; }
.st-btn { padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: 'Cairo'; border: none; }
.st-btn-outline { background: #f1f5f9; color: #1a1a2e; border: 1px solid #e2e8f0; }
.st-btn-outline:hover { border-color: #106F24; color: #106F24; }
.st-btn-primary { background: #106F24; color: #fff; }
.st-btn-primary:hover { background: #0d5c1e; }
.st-btn-primary:disabled { opacity: .4; }

.st-readonly { background: #f8f9fb; }
.st-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.st-info-grid > div { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e8ecf1; font-size: 13px; }
.st-label { color: #6b7280; }
.st-active { color: #059669; font-weight: 700; }
.st-pending { color: #f59e0b; font-weight: 700; }
</style>
