<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
defineOptions({ layout: AdminLayout });
const p = defineProps({ employees: Array });
const showAdd = ref(false);
const showReset = ref(null);

const form = useForm({ full_name: '', email: '', phone: '' });
const resetForm = useForm({ password: '' });

const addEmployee = () => {
  form.post(route('admin.employees.store'), {
    onSuccess: () => { showAdd.value = false; form.reset(); }
  });
};

const toggleStatus = (id) => router.patch(route('admin.employees.toggle', id));
const reset2FA = (id) => { if(confirm('إعادة تعيين 2FA لهذا الموظف؟')) router.post(route('admin.employees.reset-2fa', id)); };
const deleteEmp = (id) => { if(confirm('حذف هذا الموظف نهائياً؟')) router.delete(route('admin.employees.delete', id)); };

const resetPw = (id) => {
  resetForm.post(route('admin.employees.reset-password', id), {
    onSuccess: () => { showReset.value = null; resetForm.reset(); }
  });
};
</script>

<template>
<Head title="إدارة الموظفين" />
<div class="em">
  <div class="em-header">
    <h1 class="em-title">👥 إدارة الموظفين</h1>
    <button @click="showAdd = true" class="em-add-btn">➕ إضافة موظف</button>
  </div>

  <div class="em-count">إجمالي الموظفين: <b>{{ employees.length }}</b></div>

  <div class="em-table-wrap">
    <table class="em-tbl">
      <thead>
        <tr><th>#</th><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>الحالة</th><th>2FA</th><th>التسجيل</th><th>إجراءات</th></tr>
      </thead>
      <tbody>
        <tr v-for="e in employees" :key="e.id">
          <td>{{ e.id }}</td>
          <td class="em-bold">{{ e.full_name }}</td>
          <td>{{ e.email }}</td>
          <td>{{ e.phone || '—' }}</td>
          <td><span class="em-badge" :class="e.status === 'active' ? 'em-b-ok' : 'em-b-err'">{{ e.status === 'active' ? 'نشط' : 'معلّق' }}</span></td>
          <td><span class="em-badge" :class="e.totp_enabled ? 'em-b-ok' : 'em-b-warn'">{{ e.totp_enabled ? 'مفعّل' : 'غير مفعّل' }}</span></td>
          <td class="em-date">{{ e.created_at ? new Date(e.created_at).toLocaleDateString('ar') : '—' }}</td>
          <td>
            <div class="em-actions">
              <button @click="toggleStatus(e.id)" class="em-btn" :class="e.status === 'active' ? 'em-btn-warn' : 'em-btn-ok'" :title="e.status === 'active' ? 'تعليق' : 'تفعيل'">
                {{ e.status === 'active' ? '⏸️' : '▶️' }}
              </button>
              <button @click="showReset = e.id" class="em-btn em-btn-blue" title="إعادة كلمة المرور">🔑</button>
              <button @click="reset2FA(e.id)" class="em-btn em-btn-blue" title="إعادة 2FA" v-if="e.totp_enabled">🔄</button>
              <button @click="deleteEmp(e.id)" class="em-btn em-btn-err" title="حذف">🗑️</button>
            </div>
          </td>
        </tr>
        <tr v-if="!employees.length"><td colspan="8" class="em-empty">لا يوجد موظفين</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Add Modal -->
  <div v-if="showAdd" class="em-overlay" @click.self="showAdd = false">
    <div class="em-modal">
      <h3 class="em-modal-title">➕ إضافة موظف جديد</h3>
      <p class="em-modal-sub">سيتم إنشاء كلمة مرور تلقائياً وإرسالها مع بيانات الدخول للموظف عبر البريد الإلكتروني</p>
      <form @submit.prevent="addEmployee" class="em-form">
        <div class="em-field"><label>الاسم الكامل</label><input v-model="form.full_name" required placeholder="محمد أحمد" /></div>
        <div class="em-field"><label>البريد الإلكتروني</label><input v-model="form.email" type="email" required placeholder="employee@sdbwallet.com" /><span v-if="form.errors.email" class="em-err">{{ form.errors.email }}</span></div>
        <div class="em-field"><label>رقم الهاتف (اختياري)</label><input v-model="form.phone" placeholder="+45..." /></div>
        <div class="em-modal-actions">
          <button type="submit" class="em-modal-ok" :disabled="form.processing">{{ form.processing ? 'جاري الإنشاء...' : 'إنشاء الموظف ✅' }}</button>
          <button type="button" @click="showAdd = false" class="em-modal-cancel">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Reset Password Modal -->
  <div v-if="showReset" class="em-overlay" @click.self="showReset = null">
    <div class="em-modal">
      <h3 class="em-modal-title">🔑 إعادة تعيين كلمة المرور</h3>
      <form @submit.prevent="resetPw(showReset)" class="em-form">
        <div class="em-field"><label>كلمة المرور الجديدة</label><input v-model="resetForm.password" type="text" required placeholder="كلمة مرور جديدة" /></div>
        <div class="em-modal-actions">
          <button type="submit" class="em-modal-ok" :disabled="resetForm.processing">تغيير</button>
          <button type="button" @click="showReset = null" class="em-modal-cancel">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>
</template>

<style scoped>
.em{direction:rtl;max-width:1100px;margin:0 auto}
.em-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.em-title{font-size:22px;font-weight:900;color:#0f172a}
.em-add-btn{padding:10px 20px;background:linear-gradient(135deg,#2D6A00,#3d8a00);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;transition:all .15s;font-family:inherit}
.em-add-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(45,106,0,.2)}
.em-count{font-size:13px;color:#64748b;margin-bottom:14px;font-weight:600}
.em-table-wrap{background:#fff;border:1px solid #e8ebf0;border-radius:16px;padding:14px;overflow-x:auto}
.em-tbl{width:100%;border-collapse:collapse;min-width:800px}
.em-tbl th{font-size:11px;font-weight:700;color:#94a3b8;text-align:right;padding:10px 12px;border-bottom:2px solid #e8ebf0;background:#fafbfc}
.em-tbl td{font-size:13px;color:#334155;padding:10px 12px;border-bottom:1px solid #f1f5f9}
.em-tbl tr:hover td{background:#fafbfc}
.em-bold{font-weight:700;color:#0f172a}
.em-date{font-size:11px;color:#94a3b8}
.em-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:6px}
.em-b-ok{background:#ecfdf5;color:#059669}.em-b-err{background:#fef2f2;color:#dc2626}.em-b-warn{background:#fef3c7;color:#d97706}
.em-actions{display:flex;gap:4px}
.em-btn{width:28px;height:28px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.em-btn:hover{transform:scale(1.1)}
.em-btn-warn{border-color:#f59e0b}.em-btn-ok{border-color:#059669}.em-btn-blue{border-color:#2563eb}.em-btn-err{border-color:#ef4444}
.em-empty{text-align:center;color:#cbd5e1;padding:30px}
/* Modal */
.em-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;z-index:999}
.em-modal{background:#fff;border-radius:20px;padding:28px;min-width:420px;max-width:500px}
.em-modal-title{font-size:18px;font-weight:900;color:#0f172a;margin:0 0 4px}
.em-modal-sub{font-size:12px;color:#64748b;margin-bottom:16px}
.em-form{display:flex;flex-direction:column;gap:12px}
.em-field{display:flex;flex-direction:column;gap:4px}
.em-field label{font-size:12px;font-weight:700;color:#64748b}
.em-field input{padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;transition:border .2s;font-family:inherit}
.em-field input:focus{border-color:#2563eb}
.em-err{font-size:11px;color:#ef4444;font-weight:600}
.em-modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
.em-modal-ok{padding:10px 24px;background:#2D6A00;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer}
.em-modal-ok:disabled{opacity:.5;cursor:not-allowed}
.em-modal-cancel{padding:10px 24px;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer}
</style>
