<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineOptions({ layout: EmployeeLayout });
const p = defineProps({ stats: Object, recentTickets: Array, recentChats: Array });
</script>
<template>
<Head title="لوحة تحكم الموظف" />
<div class="ed">
  <!-- Welcome Banner -->
  <div class="ed-banner">
    <div class="ed-banner-text">مرحباً 👋</div>
  </div>

  <!-- Stats Row (Bank-style sunken panels) -->
  <div class="ed-stats-row">
    <div class="ed-stat-box">
      <div class="ed-stat-icon">🎫</div>
      <div class="ed-stat-num ed-red">{{ stats.openTickets }}</div>
      <div class="ed-stat-label">تذاكر مفتوحة</div>
    </div>
    <div class="ed-stat-box">
      <div class="ed-stat-icon">⏳</div>
      <div class="ed-stat-num ed-amber">{{ stats.inProgressTickets }}</div>
      <div class="ed-stat-label">قيد المعالجة</div>
    </div>
    <div class="ed-stat-box">
      <div class="ed-stat-icon">💬</div>
      <div class="ed-stat-num ed-blue">{{ stats.unreadChats }}</div>
      <div class="ed-stat-label">رسائل غير مقروءة</div>
    </div>
    <div class="ed-stat-box">
      <div class="ed-stat-icon">💸</div>
      <div class="ed-stat-num ed-green">{{ stats.pendingRemittances }}</div>
      <div class="ed-stat-label">حوالات جاهزة</div>
    </div>
  </div>

  <!-- Two-panel content area -->
  <div class="ed-panels">
    <!-- Recent Tickets Panel (GroupBox style) -->
    <fieldset class="ed-fieldset">
      <legend>🎫 آخر التذاكر</legend>
      <div class="ed-fieldset-actions">
        <Link :href="route('employee.tickets')" class="ed-btn">الكل ←</Link>
      </div>
      <table class="ed-table">
        <thead>
          <tr>
            <th>الحالة</th>
            <th>العميل</th>
            <th>الموضوع</th>
            <th>التاريخ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in recentTickets" :key="t.id" class="ed-table-row" @click="$inertia.visit(route('employee.tickets.show', t.id))">
            <td><span class="ed-st" :class="'ed-st-' + t.status">{{ {open:'مفتوحة',in_progress:'قيد المعالجة',resolved:'محلولة',closed:'مغلقة'}[t.status] || t.status }}</span></td>
            <td class="ed-td-name">{{ t.user_name || 'زائر' }}</td>
            <td class="ed-td-subj">{{ t.subject }}</td>
            <td class="ed-td-date">{{ new Date(t.created_at).toLocaleDateString('ar') }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="!recentTickets?.length" class="ed-empty">لا توجد تذاكر حالياً</div>
    </fieldset>

    <!-- Recent Chats Panel (GroupBox style) -->
    <fieldset class="ed-fieldset">
      <legend>💬 آخر المحادثات</legend>
      <div class="ed-fieldset-actions">
        <Link :href="route('employee.chat')" class="ed-btn">الكل ←</Link>
      </div>
      <div class="ed-chat-list">
        <Link v-for="c in recentChats" :key="c.id" :href="route('employee.chat') + '?user=' + c.id" class="ed-chat-item">
          <div class="ed-chat-avatar">{{ c.full_name?.charAt(0) }}</div>
          <div class="ed-chat-info">
            <div class="ed-chat-name">{{ c.full_name }}</div>
            <div class="ed-chat-email">{{ c.email }}</div>
          </div>
          <span v-if="c.unread" class="ed-chat-badge">{{ c.unread }}</span>
        </Link>
      </div>
      <div v-if="!recentChats?.length" class="ed-empty">لا توجد محادثات حالياً</div>
    </fieldset>
  </div>
</div>
</template>
<style scoped>
/* ═══════════════════════════════════
   BANKING SYSTEM DASHBOARD DESIGN
   ═══════════════════════════════════ */
.ed {
  direction: rtl;
  padding: 8px;
  font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
  font-size: 12px;
}

/* Welcome Banner */
.ed-banner {
  background: linear-gradient(180deg, #f0f4f8, #e0e8f0);
  border: 2px groove #c0c0c0;
  padding: 10px 14px;
  margin-bottom: 8px;
  text-align: center;
}
.ed-banner-text {
  font-size: 18px;
  font-weight: 900;
  color: #1a3a5c;
}

/* Stats Row */
.ed-stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin-bottom: 10px;
}
.ed-stat-box {
  background: linear-gradient(180deg, #fff, #f0f0f0);
  border: 2px groove #c0c0c0;
  padding: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: background 0.15s;
}
.ed-stat-box:hover {
  background: linear-gradient(180deg, #fffff0, #f8f8e8);
}
.ed-stat-icon { font-size: 22px; }
.ed-stat-num { font-size: 26px; font-weight: 900; }
.ed-stat-label { font-size: 11px; color: #555; font-weight: 600; }
.ed-red { color: #cc0000; }
.ed-amber { color: #b45309; }
.ed-blue { color: #1d4ed8; }
.ed-green { color: #15803d; }

/* Panels Layout */
.ed-panels {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

/* GroupBox / Fieldset */
.ed-fieldset {
  border: 2px groove #c0c0c0;
  padding: 10px;
  background: #f8f8f8;
  position: relative;
}
.ed-fieldset legend {
  font-size: 13px;
  font-weight: 800;
  color: #1a3a5c;
  padding: 0 8px;
  background: #f8f8f8;
}
.ed-fieldset-actions {
  position: absolute;
  top: -2px;
  left: 10px;
}
.ed-btn {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 10px;
  background: linear-gradient(180deg, #f8f8f8, #e0e0e0);
  border: 1px solid #999;
  border-radius: 2px;
  color: #333;
  text-decoration: none;
  cursor: pointer;
  box-shadow: inset 0 1px 0 #fff;
}
.ed-btn:hover {
  background: linear-gradient(180deg, #fff, #e8e8e8);
}

/* Table */
.ed-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  margin-top: 4px;
}
.ed-table thead {
  background: linear-gradient(180deg, #e0e4e8, #c8ccd0);
}
.ed-table th {
  padding: 5px 8px;
  border: 1px solid #a8acb0;
  font-weight: 800;
  color: #1a2a3a;
  text-align: right;
  font-size: 10px;
}
.ed-table td {
  padding: 5px 8px;
  border: 1px solid #d0d4d8;
  background: #fff;
}
.ed-table-row {
  cursor: pointer;
}
.ed-table-row:hover td {
  background: #e8f0ff;
}
.ed-td-name { font-weight: 700; color: #1a2a3a; }
.ed-td-subj { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #555; }
.ed-td-date { font-size: 10px; color: #888; direction: ltr; text-align: center; }

/* Status badges */
.ed-st { font-size: 9px; font-weight: 800; padding: 1px 6px; border-radius: 2px; white-space: nowrap; }
.ed-st-open { background: #fecdd3; color: #9f1239; }
.ed-st-in_progress { background: #fef3c7; color: #92400e; }
.ed-st-resolved { background: #d1fae5; color: #065f46; }
.ed-st-closed { background: #e2e8f0; color: #475569; }

/* Chat List */
.ed-chat-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-top: 4px;
}
.ed-chat-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  text-decoration: none;
  border: 1px solid transparent;
  transition: all 0.1s;
}
.ed-chat-item:hover {
  background: linear-gradient(180deg, #fff, #e8ecf0);
  border-color: #8898a8;
}
.ed-chat-avatar {
  width: 28px;
  height: 28px;
  border-radius: 3px;
  background: linear-gradient(180deg, #1a5c2a, #145020);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 800;
  flex-shrink: 0;
}
.ed-chat-info { flex: 1; min-width: 0; }
.ed-chat-name { font-size: 12px; font-weight: 700; color: #1a2a3a; }
.ed-chat-email { font-size: 10px; color: #888; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ed-chat-badge {
  background: #cc0000;
  color: #fff;
  font-size: 9px;
  font-weight: 900;
  padding: 0 5px;
  border-radius: 2px;
  min-width: 14px;
  text-align: center;
  line-height: 16px;
}

.ed-empty {
  text-align: center;
  color: #999;
  font-size: 11px;
  padding: 20px;
  font-style: italic;
}

@media (max-width: 900px) {
  .ed-stats-row { grid-template-columns: repeat(2, 1fr); }
  .ed-panels { grid-template-columns: 1fr; }
}
</style>
