<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: AdminLayout });
const p = defineProps({ consultations: Object, stats: Object });
const stColor = s => ({open:'#2563eb',in_progress:'#d97706',resolved:'#059669',closed:'#94a3b8'}[s]||'#64748b');
const stLabel = s => ({open:'مفتوحة',in_progress:'قيد المعالجة',resolved:'تم الحل',closed:'مغلقة'}[s]||s);
</script>
<template>
<Head title="الاستشارات" />
<div class="cn">
  <div class="cn-hdr">
    <h1 class="cn-title">💬 إدارة الاستشارات</h1>
    <p class="cn-sub">Consultation Management</p>
  </div>

  <!-- Stats -->
  <div class="cn-stats">
    <div class="cn-st"><div class="cn-st-val" style="color:#2563eb">{{ p.stats.total }}</div><div class="cn-st-lbl">إجمالي</div></div>
    <div class="cn-st"><div class="cn-st-val" style="color:#059669">{{ p.stats.open }}</div><div class="cn-st-lbl">مفتوحة</div></div>
    <div class="cn-st"><div class="cn-st-val" style="color:#d97706">{{ p.stats.in_progress }}</div><div class="cn-st-lbl">قيد المعالجة</div></div>
    <div class="cn-st"><div class="cn-st-val" style="color:#059669">{{ p.stats.resolved }}</div><div class="cn-st-lbl">تم الحل</div></div>
    <div class="cn-st"><div class="cn-st-val" style="color:#94a3b8">{{ p.stats.closed }}</div><div class="cn-st-lbl">مغلقة</div></div>
  </div>

  <!-- Table -->
  <div class="cn-tbl-wrap">
    <table class="cn-tbl" v-if="p.consultations.data?.length">
      <thead>
        <tr>
          <th>#</th>
          <th>العميل</th>
          <th>الموضوع</th>
          <th>الحالة</th>
          <th>الأولوية</th>
          <th>التاريخ</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in p.consultations.data" :key="c.id">
          <td class="cn-id">{{ c.id }}</td>
          <td>
            <div class="cn-user">{{ c.user_name || '—' }}</div>
            <div class="cn-email">{{ c.user_email || '' }}</div>
          </td>
          <td class="cn-subject">{{ c.subject || '—' }}</td>
          <td><span class="cn-badge" :style="{color: stColor(c.status), background: stColor(c.status)+'18'}">{{ stLabel(c.status) }}</span></td>
          <td><span class="cn-priority" :class="'cn-p-'+(c.priority||'normal')">{{ c.priority || 'عادي' }}</span></td>
          <td class="cn-date">{{ new Date(c.created_at).toLocaleString('ar') }}</td>
          <td>
            <Link :href="route('admin.support.show', c.id)" class="cn-btn">عرض</Link>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="cn-empty">
      <div class="cn-empty-icon">💬</div>
      <div class="cn-empty-text">لا توجد استشارات</div>
    </div>
  </div>

  <!-- Pagination -->
  <div class="cn-pag" v-if="p.consultations.last_page > 1">
    <Link v-for="link in p.consultations.links" :key="link.label" :href="link.url||''" class="cn-pag-l" :class="{active:link.active}" v-html="link.label" />
  </div>
</div>
</template>
<style scoped>
.cn { padding: 24px; direction: rtl; max-width: 1400px; margin: 0 auto; }
.cn-hdr { margin-bottom: 20px; }
.cn-title { font-size: 22px; font-weight: 800; color: #1e293b; margin: 0; }
.cn-sub { font-size: 13px; color: #94a3b8; margin-top: 4px; }

.cn-stats { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.cn-st { background: #fff; border: 1px solid #e8ebf0; border-radius: 12px; padding: 16px 24px; text-align: center; min-width: 120px; }
.cn-st-val { font-size: 28px; font-weight: 900; line-height: 1; }
.cn-st-lbl { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 6px; }

.cn-tbl-wrap { overflow-x: auto; background: #fff; border-radius: 16px; border: 1px solid #e8ebf0; }
.cn-tbl { width: 100%; border-collapse: collapse; min-width: 700px; }
.cn-tbl th { font-size: 12px; font-weight: 700; color: #64748b; text-align: right; padding: 12px 14px; background: #fafbfc; border-bottom: 2px solid #e8ebf0; }
.cn-tbl td { font-size: 13px; color: #334155; padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
.cn-tbl tr:hover td { background: #fafbfc; }

.cn-id { font-family: monospace; color: #94a3b8; font-size: 11px; }
.cn-user { font-weight: 700; color: #0f172a; font-size: 13px; }
.cn-email { font-size: 11px; color: #94a3b8; direction: ltr; text-align: right; }
.cn-subject { font-weight: 600; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cn-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
.cn-priority { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; background: #f1f5f9; color: #64748b; }
.cn-p-high { background: #fef2f2; color: #dc2626; }
.cn-p-urgent { background: #fef2f2; color: #dc2626; font-weight: 800; }
.cn-date { font-size: 11px; color: #94a3b8; }
.cn-btn { padding: 6px 14px; background: #2563eb; color: #fff; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; transition: background .2s; }
.cn-btn:hover { background: #1d4ed8; }

.cn-empty { text-align: center; padding: 60px 20px; }
.cn-empty-icon { font-size: 48px; margin-bottom: 12px; }
.cn-empty-text { font-size: 15px; color: #94a3b8; font-weight: 600; }

.cn-pag { display: flex; gap: 4px; margin-top: 16px; justify-content: center; }
.cn-pag-l { padding: 6px 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; color: #64748b; text-decoration: none; }
.cn-pag-l.active { background: #2563eb; color: #fff; border-color: #2563eb; }
</style>
