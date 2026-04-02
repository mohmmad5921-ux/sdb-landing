<script setup>
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
const props = defineProps({ merchant: Object, apiKeys: Array, apiLogs: Array });
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('ar-u-nu-latn', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
const mask = (k) => k ? k.substring(0, 8) + '•'.repeat(20) : '';
const statusCode = (c) => c >= 200 && c < 300 ? 'green' : c >= 400 ? 'red' : 'yellow';
</script>

<template>
<BusinessLayout title="API & المطورين">
    <div class="ap-grid">
        <!-- API Keys -->
        <div class="ap-section">
            <h2>🔑 مفاتيح API</h2>
            <div v-if="apiKeys?.length" class="ap-keys">
                <div v-for="key in apiKeys" :key="key.id" class="ap-key">
                    <div class="ap-key-header">
                        <span class="ap-key-name">{{ key.name }}</span>
                        <span :class="['ap-env', key.environment === 'production' ? 'ap-prod' : 'ap-sand']">{{ key.environment }}</span>
                    </div>
                    <div class="ap-key-row"><span class="ap-label">Public Key</span><code>{{ key.public_key }}</code></div>
                    <div class="ap-key-row"><span class="ap-label">Secret Key</span><code>{{ key.secret_key_prefix }}•••••••••••</code></div>
                    <div class="ap-key-meta">
                        <span>{{ key.is_active ? '✅ نشط' : '❌ معطل' }}</span>
                        <span v-if="key.last_used_at">آخر استخدام: {{ fmtDate(key.last_used_at) }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="ap-empty">
                <div class="ap-empty-icon">🔌</div>
                <div>لم يتم إنشاء مفاتيح API بعد</div>
                <div class="ap-empty-sub">تواصل مع الإدارة لتفعيل API</div>
            </div>
        </div>

        <!-- Docs -->
        <div class="ap-section">
            <h2>📖 التوثيق</h2>
            <div class="ap-docs">
                <div class="ap-doc"><span>🔗</span><div><strong>Base URL</strong><code>https://sdbwallet.com/api/v1</code></div></div>
                <div class="ap-doc"><span>🔐</span><div><strong>Authentication</strong><span>Bearer Token (API Key)</span></div></div>
                <div class="ap-doc"><span>📦</span><div><strong>Endpoints</strong>
                    <div class="ap-endpoints">
                        <div><code>POST /payments/create</code> — إنشاء عملية دفع</div>
                        <div><code>GET /payments/{id}</code> — حالة الدفع</div>
                        <div><code>POST /invoices/create</code> — إنشاء فاتورة</div>
                        <div><code>GET /balance</code> — رصيد الحساب</div>
                        <div><code>GET /transactions</code> — سجل العمليات</div>
                    </div>
                </div></div>
            </div>
        </div>

        <!-- Logs -->
        <div class="ap-section">
            <h2>📋 سجل الطلبات</h2>
            <div class="ap-logs">
                <div v-for="log in apiLogs" :key="log.id" class="ap-log">
                    <span :class="['ap-method', log.method === 'POST' ? 'ap-post' : 'ap-get']">{{ log.method }}</span>
                    <span class="ap-endpoint">{{ log.endpoint }}</span>
                    <span :class="['ap-code', statusCode(log.status_code)]">{{ log.status_code }}</span>
                    <span class="ap-time">{{ log.response_time_ms }}ms</span>
                    <span class="ap-date">{{ fmtDate(log.created_at) }}</span>
                </div>
                <div v-if="!apiLogs?.length" class="ap-empty-sm">لا توجد طلبات API</div>
            </div>
        </div>
    </div>
</BusinessLayout>
</template>

<style scoped>
.ap-grid { display: flex; flex-direction: column; gap: 20px; }
.ap-section { background: #fff; border: 1px solid #e8ecf1; border-radius: 16px; padding: 20px; }
.ap-section h2 { font-size: 15px; font-weight: 800; margin-bottom: 14px; }
.ap-keys { display: flex; flex-direction: column; gap: 10px; }
.ap-key { background: #f8f9fb; border: 1px solid #e8ecf1; border-radius: 12px; padding: 14px; }
.ap-key-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
.ap-key-name { font-weight: 700; font-size: 13px; }
.ap-env { font-size: 10px; padding: 2px 8px; border-radius: 6px; font-weight: 700; }
.ap-prod { background: rgba(16,185,129,.1); color: #10b981; }
.ap-sand { background: rgba(245,158,11,.1); color: #f59e0b; }
.ap-key-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.ap-label { font-size: 10px; color: #6b7280; min-width: 80px; }
.ap-key-row code { font-size: 11px; color: #818cf8; background: rgba(99,102,241,.08); padding: 3px 8px; border-radius: 6px; }
.ap-key-meta { display: flex; gap: 16px; font-size: 10px; color: #9ca3af; margin-top: 8px; }
.ap-docs { display: flex; flex-direction: column; gap: 12px; }
.ap-doc { display: flex; gap: 12px; padding: 12px; background: #fff; border-radius: 10px; }
.ap-doc strong { display: block; font-size: 12px; font-weight: 700; }
.ap-doc code, .ap-doc span { font-size: 11px; color: #6b7280; }
.ap-endpoints { margin-top: 6px; display: flex; flex-direction: column; gap: 4px; font-size: 11px; }
.ap-endpoints code { color: #10b981; background: rgba(16,185,129,.08); padding: 2px 6px; border-radius: 4px; margin-left: 4px; }
.ap-logs { max-height: 300px; overflow-y: auto; }
.ap-log { display: flex; align-items: center; gap: 12px; padding: 8px 4px; border-bottom: 1px solid #f8f9fb; font-size: 12px; }
.ap-method { font-weight: 800; font-size: 10px; padding: 2px 8px; border-radius: 6px; }
.ap-post { background: rgba(99,102,241,.1); color: #818cf8; }
.ap-get { background: rgba(16,185,129,.1); color: #10b981; }
.ap-endpoint { flex: 1; font-family: monospace; color: #6b7280; font-size: 11px; }
.ap-code { font-weight: 700; font-size: 11px; }
.ap-code.green { color: #10b981; }
.ap-code.red { color: #ef4444; }
.ap-code.yellow { color: #f59e0b; }
.ap-time { font-size: 10px; color: #9ca3af; }
.ap-date { font-size: 10px; color: #9ca3af; }
.ap-empty { text-align: center; padding: 30px; color: #9ca3af; }
.ap-empty-icon { font-size: 36px; margin-bottom: 8px; }
.ap-empty-sub { font-size: 11px; margin-top: 4px; }
.ap-empty-sm { text-align: center; padding: 16px; color: #9ca3af; font-size: 12px; }
</style>
