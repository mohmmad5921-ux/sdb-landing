<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ stats: Object, recentTickets: Array, recentChats: Array });
</script>
<template>
<Head title="لوحة تحكم الموظف" />
<div class="ed">
  <h1 class="ed-title">مرحباً 👋</h1>
  <div class="ed-grid">
    <div class="ed-card" style="border-right:4px solid #ef4444">
      <div class="ed-card-ico">🎫</div>
      <div class="ed-card-val" style="color:#ef4444">{{ stats.openTickets }}</div>
      <div class="ed-card-lbl">تذاكر مفتوحة</div>
    </div>
    <div class="ed-card" style="border-right:4px solid #d97706">
      <div class="ed-card-ico">⏳</div>
      <div class="ed-card-val" style="color:#d97706">{{ stats.inProgressTickets }}</div>
      <div class="ed-card-lbl">قيد المعالجة</div>
    </div>
    <div class="ed-card" style="border-right:4px solid #2563eb">
      <div class="ed-card-ico">💬</div>
      <div class="ed-card-val" style="color:#2563eb">{{ stats.unreadChats }}</div>
      <div class="ed-card-lbl">رسائل غير مقروءة</div>
    </div>
    <div class="ed-card" style="border-right:4px solid #059669">
      <div class="ed-card-ico">💸</div>
      <div class="ed-card-val" style="color:#059669">{{ stats.pendingRemittances }}</div>
      <div class="ed-card-lbl">حوالات جاهزة</div>
    </div>
  </div>

  <div class="ed-row">
    <div class="ed-sec">
      <div class="ed-sec-hdr"><h2>🎫 آخر التذاكر</h2><Link :href="route('employee.tickets')" class="ed-more">الكل ←</Link></div>
      <div class="ed-list">
        <Link v-for="t in recentTickets" :key="t.id" :href="route('employee.tickets.show', t.id)" class="ed-item">
          <div class="ed-item-top"><span class="ed-item-name">{{ t.user_name || 'زائر' }}</span><span class="ed-status" :class="'ed-s-' + t.status">{{ {open:'مفتوحة',in_progress:'قيد المعالجة',resolved:'محلولة',closed:'مغلقة'}[t.status] || t.status }}</span></div>
          <div class="ed-item-sub">{{ t.subject }}</div>
          <div class="ed-item-date">{{ new Date(t.created_at).toLocaleDateString('ar') }}</div>
        </Link>
        <div v-if="!recentTickets?.length" class="ed-empty">لا توجد تذاكر</div>
      </div>
    </div>
    <div class="ed-sec">
      <div class="ed-sec-hdr"><h2>💬 آخر المحادثات</h2><Link :href="route('employee.chat')" class="ed-more">الكل ←</Link></div>
      <div class="ed-list">
        <Link v-for="c in recentChats" :key="c.id" :href="route('employee.chat') + '?user=' + c.id" class="ed-item">
          <div class="ed-item-top">
            <div class="ed-item-avatar" v-if="c.profile_photo"><img :src="c.profile_photo" /></div>
            <div class="ed-item-avatar ed-item-init" v-else>{{ c.full_name?.charAt(0) }}</div>
            <span class="ed-item-name">{{ c.full_name }}</span>
            <span v-if="c.unread" class="ed-badge">{{ c.unread }}</span>
          </div>
          <div class="ed-item-sub">{{ c.email }}</div>
        </Link>
        <div v-if="!recentChats?.length" class="ed-empty">لا توجد محادثات</div>
      </div>
    </div>
  </div>
</div>
</template>
<style scoped>
.ed{direction:rtl;max-width:1200px;margin:0 auto}
.ed-title{font-size:22px;font-weight:900;color:#0f172a;margin-bottom:20px}
.ed-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.ed-card{background:#fff;border:1px solid #e8ebf0;border-radius:14px;padding:18px;display:flex;align-items:center;gap:12px;transition:all .2s}
.ed-card:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.06)}
.ed-card-ico{font-size:28px}
.ed-card-val{font-size:28px;font-weight:900}
.ed-card-lbl{font-size:11px;color:#64748b;font-weight:600}
.ed-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.ed-sec{background:#fff;border:1px solid #e8ebf0;border-radius:14px;padding:16px}
.ed-sec-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #f1f5f9}
.ed-sec-hdr h2{font-size:14px;font-weight:800;color:#1e293b;margin:0}
.ed-more{font-size:12px;color:#2563eb;text-decoration:none;font-weight:600}
.ed-list{display:flex;flex-direction:column;gap:8px}
.ed-item{display:block;padding:10px;border-radius:10px;text-decoration:none;transition:background .15s;border:1px solid transparent}
.ed-item:hover{background:#f8fafc;border-color:#e2e8f0}
.ed-item-top{display:flex;align-items:center;gap:8px}
.ed-item-name{font-size:13px;font-weight:700;color:#0f172a;flex:1}
.ed-item-sub{font-size:11px;color:#64748b;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ed-item-date{font-size:10px;color:#94a3b8;margin-top:2px}
.ed-item-avatar{width:28px;height:28px;border-radius:8px;overflow:hidden;flex-shrink:0}
.ed-item-avatar img{width:100%;height:100%;object-fit:cover}
.ed-item-init{background:#e0f2fe;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#0284c7}
.ed-badge{background:#ef4444;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:10px}
.ed-status{font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px}
.ed-s-open{background:#fef2f2;color:#dc2626}.ed-s-in_progress{background:#fef3c7;color:#d97706}.ed-s-resolved{background:#ecfdf5;color:#059669}.ed-s-closed{background:#f1f5f9;color:#6b7280}
.ed-empty{text-align:center;color:#cbd5e1;font-size:13px;padding:20px}
@media(max-width:900px){.ed-grid{grid-template-columns:repeat(2,1fr)}.ed-row{grid-template-columns:1fr}}
</style>
