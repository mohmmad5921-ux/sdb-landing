<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
const props = defineProps({ loginHistory: Array, twoFaEnabled: Boolean });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
</script>

<template>
<BusinessLayout title="الأمان">
    <div class="sc-grid">
        <div class="sc-section">
            <h2>🛡️ المصادقة الثنائية (2FA)</h2>
            <div class="sc-2fa">
                <div class="sc-2fa-status">
                    <span :class="twoFaEnabled ? 'sc-on' : 'sc-off'">{{ twoFaEnabled ? '✅ مفعّلة' : '❌ غير مفعّلة' }}</span>
                </div>
                <p class="sc-2fa-desc">المصادقة الثنائية تضيف طبقة حماية إضافية لحسابك التجاري</p>
            </div>
        </div>

        <div class="sc-section">
            <h2>📋 سجل الدخول</h2>
            <div class="sc-logins">
                <div v-for="l in loginHistory" :key="l.id" class="sc-login">
                    <div class="sc-login-icon">{{ l.success ? '✅' : '❌' }}</div>
                    <div class="sc-login-info">
                        <div class="sc-login-ip">{{ l.ip_address || 'Unknown IP' }}</div>
                        <div class="sc-login-agent">{{ (l.user_agent || '').substring(0, 60) }}</div>
                    </div>
                    <div class="sc-login-date">{{ fmtDate(l.created_at) }}</div>
                </div>
                <div v-if="!loginHistory?.length" class="sc-empty">لا يوجد سجل دخول</div>
            </div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.sc-grid { display: flex; flex-direction: column; gap: 20px; }
.sc-section { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; }
.sc-section h2 { font-size: 15px; font-weight: 800; margin-bottom: 14px; }
.sc-2fa { padding: 16px; background: #fff; border-radius: 12px; }
.sc-2fa-status { font-size: 16px; font-weight: 800; margin-bottom: 6px; }
.sc-on { color: #10b981; }
.sc-off { color: #ef4444; }
.sc-2fa-desc { font-size: 12px; color: #6b7280; }
.sc-logins { display: flex; flex-direction: column; }
.sc-login { display: flex; align-items: center; gap: 12px; padding: 10px 4px; border-bottom: 1px solid #f8f9fb; }
.sc-login:last-child { border-bottom: none; }
.sc-login-icon { font-size: 16px; }
.sc-login-info { flex: 1; }
.sc-login-ip { font-size: 13px; font-weight: 700; font-family: monospace; }
.sc-login-agent { font-size: 10px; color: #9ca3af; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sc-login-date { font-size: 11px; color: #9ca3af; }
.sc-empty { text-align: center; padding: 20px; color: #9ca3af; }
</style>
