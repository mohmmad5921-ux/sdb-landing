<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
defineOptions({ layout: AdminLayout });
const p = defineProps({ backgrounds: Array, categories: Array });

const showAddTo = ref(null);
const showNewSection = ref(false);
const newSectionName = ref('');
const previewUrl = ref(null);
const form = useForm({ title: '', category: '', image: null, sort_order: 0 });

// Image editor state
const zoom = ref(1);
const panX = ref(0);
const panY = ref(0);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });
const imgDimensions = ref({ w: 0, h: 0 });

const editorStyle = computed(() => ({
  backgroundImage: previewUrl.value ? `url(${previewUrl.value})` : 'none',
  backgroundSize: `${zoom.value * 100}%`,
  backgroundPosition: `${50 + panX.value}% ${50 + panY.value}%`,
  backgroundRepeat: 'no-repeat',
}));

const phoneCardStyle = computed(() => ({
  backgroundImage: previewUrl.value ? `url(${previewUrl.value})` : 'none',
  backgroundSize: `${zoom.value * 100}%`,
  backgroundPosition: `${50 + panX.value}% ${50 + panY.value}%`,
  backgroundRepeat: 'no-repeat',
}));

const startDrag = (e) => {
  isDragging.value = true;
  const ev = e.touches ? e.touches[0] : e;
  dragStart.value = { x: ev.clientX - panX.value * 2, y: ev.clientY - panY.value * 2 };
  e.preventDefault();
};
const onDrag = (e) => {
  if (!isDragging.value) return;
  const ev = e.touches ? e.touches[0] : e;
  panX.value = (ev.clientX - dragStart.value.x) / 2;
  panY.value = (ev.clientY - dragStart.value.y) / 2;
  // Clamp
  const maxPan = (zoom.value - 1) * 50;
  panX.value = Math.max(-maxPan, Math.min(maxPan, panX.value));
  panY.value = Math.max(-maxPan, Math.min(maxPan, panY.value));
};
const endDrag = () => { isDragging.value = false; };
const resetEditor = () => { zoom.value = 1; panX.value = 0; panY.value = 0; };

// Group backgrounds by category
const grouped = computed(() => {
  const map = {};
  for (const bg of p.backgrounds) {
    if (!map[bg.category]) map[bg.category] = [];
    map[bg.category].push(bg);
  }
  return map;
});

const categoryOrder = computed(() => Object.keys(grouped.value));

const onFile = (e) => {
  const file = e.target.files[0];
  form.image = file;
  resetEditor();
  if (file) {
    const r = new FileReader();
    r.onload = (ev) => {
      previewUrl.value = ev.target.result;
      const img = new Image();
      img.onload = () => { imgDimensions.value = { w: img.width, h: img.height }; };
      img.src = ev.target.result;
    };
    r.readAsDataURL(file);
  }
};

const openAdd = (cat) => {
  showAddTo.value = cat;
  form.category = cat;
  form.title = '';
  form.image = null;
  form.sort_order = 0;
  previewUrl.value = null;
  resetEditor();
  imgDimensions.value = { w: 0, h: 0 };
};

const submit = () => {
  form.post(route('admin.backgrounds.store'), {
    forceFormData: true,
    onSuccess: () => { form.reset(); showAddTo.value = null; previewUrl.value = null; resetEditor(); },
  });
};

const addSection = () => {
  if (!newSectionName.value.trim()) return;
  showAddTo.value = newSectionName.value.trim();
  form.category = newSectionName.value.trim();
  form.title = '';
  form.image = null;
  form.sort_order = 0;
  previewUrl.value = null;
  showNewSection.value = false;
  newSectionName.value = '';
  resetEditor();
};

const toggle = (id) => router.post(route('admin.backgrounds.toggle', id));
const remove = (id) => { if(confirm('حذف هذه الخلفية نهائياً؟')) router.delete(route('admin.backgrounds.destroy', id)); };

const moveUp = (bg, items) => {
  const idx = items.findIndex(i => i.id === bg.id);
  if (idx <= 0) return;
  const reordered = items.map((item, i) => ({
    id: item.id,
    sort_order: i === idx - 1 ? idx + 1 : (i === idx ? idx : i + 1),
  }));
  router.post(route('admin.backgrounds.reorder'), { items: reordered });
};

const moveDown = (bg, items) => {
  const idx = items.findIndex(i => i.id === bg.id);
  if (idx >= items.length - 1) return;
  const reordered = items.map((item, i) => ({
    id: item.id,
    sort_order: i === idx + 1 ? idx + 1 : (i === idx ? idx + 2 : i + 1),
  }));
  router.post(route('admin.backgrounds.reorder'), { items: reordered });
};

const bgPreview = (bg) => {
  if (bg.image_url) return { backgroundImage: `url(${bg.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center' };
  if (bg.meta) return { background: `linear-gradient(135deg, ${bg.meta.split(',').join(', ')})` };
  return { background: '#334155' };
};
</script>
<template>
<Head title="خلفيات التطبيق" />
<div class="bp">
  <div class="bp-head">
    <div>
      <h1>🖼️ خلفيات التطبيق</h1>
      <p class="bp-sub">إدارة كاملة — إضافة، حذف، ترتيب، وأقسام جديدة</p>
    </div>
    <button @click="showNewSection = !showNewSection" class="bp-new-sec-btn">{{ showNewSection ? '✕' : '+ قسم جديد' }}</button>
  </div>

  <!-- New Section -->
  <div v-if="showNewSection" class="bp-new-sec">
    <input v-model="newSectionName" placeholder="اسم القسم الجديد..." class="bp-input" />
    <button @click="addSection" :disabled="!newSectionName.trim()" class="bp-btn-green">إنشاء وإضافة صورة</button>
  </div>

  <!-- Add Form with Image Editor -->
  <div v-if="showAddTo" class="bp-add-form">
    <div class="bp-add-layout">
      <div class="bp-add-fields">
        <h3>إضافة خلفية إلى: <strong>{{ showAddTo }}</strong></h3>
        <div class="bp-field"><label>العنوان</label><input v-model="form.title" placeholder="مثلاً: دمشق ليلاً" class="bp-input" /></div>
        <div class="bp-field">
          <label>الصورة</label>
          <div class="bp-dropzone" @click="$refs.fileInput.click()">
            <input type="file" accept="image/*" @change="onFile" ref="fileInput" style="display:none" />
            <span v-if="!form.image">📁 اسحب أو انقر لاختيار صورة</span>
            <span v-else class="bp-file-ok">✅ {{ form.image.name }}</span>
          </div>
          <div class="bp-size-info">
            <span>📐 المقاس المطلوب: <strong>1200 × 750 بكسل</strong> (نسبة 16:10)</span>
            <span v-if="imgDimensions.w" class="bp-actual-size" :class="{ good: imgDimensions.w >= 1200 && imgDimensions.h >= 750, bad: imgDimensions.w < 800 }">
              صورتك: {{ imgDimensions.w }} × {{ imgDimensions.h }}
              <template v-if="imgDimensions.w >= 1200 && imgDimensions.h >= 750">✅</template>
              <template v-else-if="imgDimensions.w < 800">⚠️ صغيرة جداً</template>
              <template v-else>⚠️ يُفضل أكبر</template>
            </span>
          </div>
        </div>

        <!-- Image Editor -->
        <div v-if="previewUrl" class="bp-editor">
          <label>✂️ ضبط الصورة — اسحب وكبّر/صغّر</label>
          <div class="bp-crop-area"
            :style="editorStyle"
            @mousedown="startDrag" @mousemove="onDrag" @mouseup="endDrag" @mouseleave="endDrag"
            @touchstart="startDrag" @touchmove="onDrag" @touchend="endDrag">
            <div class="bp-crop-overlay">
              <div class="bp-crop-center"></div>
            </div>
          </div>
          <div class="bp-zoom-row">
            <span class="bp-zoom-icon">🔍−</span>
            <input type="range" min="1" max="3" step="0.05" v-model.number="zoom" class="bp-zoom-slider" />
            <span class="bp-zoom-icon">🔍+</span>
            <span class="bp-zoom-val">{{ Math.round(zoom * 100) }}%</span>
            <button @click="resetEditor" class="bp-reset-btn">↺ إعادة</button>
          </div>
        </div>

        <div class="bp-add-acts">
          <button @click="submit" :disabled="form.processing || !form.title || !form.image" class="bp-btn-green">{{ form.processing ? '⏳' : '📤 رفع' }}</button>
          <button @click="showAddTo = null; previewUrl = null" class="bp-btn-gray">إلغاء</button>
        </div>
      </div>
      <!-- Phone Preview -->
      <div class="bp-phone-wrap">
        <div class="bp-phone">
          <div class="bp-phone-notch"></div>
          <div class="bp-phone-screen">
            <div class="bp-phone-card" :style="previewUrl ? phoneCardStyle : {}">
              <div class="bp-phone-ov"></div>
              <div class="bp-phone-txt"><div class="bp-phone-lbl">إجمالي الرصيد</div><div class="bp-phone-amt">€ 1,234.56</div></div>
            </div>
            <div class="bp-phone-rows"><div class="bp-r"></div><div class="bp-r s"></div><div class="bp-r"></div></div>
          </div>
        </div>
        <span class="bp-hint">{{ previewUrl ? '📱 معاينة حية على الهاتف' : 'اختر صورة لرؤية المعاينة' }}</span>
      </div>
    </div>
  </div>

  <!-- Sections -->
  <div v-for="cat in categoryOrder" :key="cat" class="bp-section">
    <div class="bp-sec-head">
      <h2>{{ cat }} <span class="bp-count">({{ grouped[cat].length }})</span></h2>
      <button @click="openAdd(cat)" class="bp-add-to">+ إضافة</button>
    </div>
    <div class="bp-cards">
      <div v-for="(bg, idx) in grouped[cat]" :key="bg.id" class="bp-card" :class="{ off: !bg.is_active }">
        <div class="bp-card-img" :style="bgPreview(bg)">
          <span class="bp-card-label">{{ bg.title }}</span>
          <span v-if="bg.bg_type === 'animated'" class="bp-badge anim">متحركة</span>
          <span v-if="bg.bg_type === 'gradient'" class="bp-badge grad">لون</span>
          <span v-if="!bg.is_active" class="bp-badge off-badge">معطل</span>
        </div>
        <div class="bp-card-bar">
          <div class="bp-card-btns">
            <button @click="moveUp(bg, grouped[cat])" :disabled="idx === 0" title="أعلى">⬆</button>
            <button @click="moveDown(bg, grouped[cat])" :disabled="idx === grouped[cat].length - 1" title="أسفل">⬇</button>
            <button @click="toggle(bg.id)" :title="bg.is_active ? 'إخفاء' : 'إظهار'">{{ bg.is_active ? '👁️' : '🚫' }}</button>
            <button @click="remove(bg.id)" class="bp-del-btn" title="حذف">🗑️</button>
          </div>
          <span class="bp-card-order">#{{ bg.sort_order }}</span>
        </div>
      </div>
    </div>
  </div>

  <div v-if="!p.backgrounds.length" class="bp-empty">لا توجد خلفيات. ابدأ بإضافة قسم جديد أو شغّل السيدر!</div>
</div>
</template>
<style scoped>
.bp{padding:24px;direction:rtl;max-width:1200px;margin:0 auto}
.bp-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px}
.bp-head h1{font-size:22px;font-weight:800;color:#1e293b;margin:0}
.bp-sub{color:#64748b;font-size:12px;margin:4px 0 0}
.bp-new-sec-btn{padding:10px 20px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:12px}
.bp-new-sec{display:flex;gap:10px;margin-bottom:20px;align-items:center}
.bp-input{padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;background:#f8fafc;flex:1}
.bp-input:focus{border-color:#6366f1}
.bp-btn-green{padding:10px 20px;background:#10b981;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:12px}
.bp-btn-green:disabled{opacity:.5;cursor:not-allowed}
.bp-btn-gray{padding:10px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:12px}

/* Add Form */
.bp-add-form{background:#fff;border-radius:16px;padding:24px;border:1px solid #e2e8f0;margin-bottom:24px;box-shadow:0 4px 15px rgba(0,0,0,.05)}
.bp-add-layout{display:flex;gap:32px;align-items:flex-start}
.bp-add-fields{flex:1}
.bp-add-fields h3{font-size:14px;color:#1e293b;margin:0 0 16px}
.bp-field{margin-bottom:12px}.bp-field label{display:block;font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px}
.bp-dropzone{border:2px dashed #cbd5e1;border-radius:12px;padding:24px;text-align:center;cursor:pointer;color:#94a3b8;font-size:13px;transition:.2s}
.bp-dropzone:hover{border-color:#10b981;background:#f0fdf4}
.bp-file-ok{color:#10b981;font-weight:600}
.bp-size-info{margin-top:8px;display:flex;flex-direction:column;gap:4px;font-size:11px;color:#64748b;background:#f8fafc;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0}
.bp-actual-size{font-weight:600}.bp-actual-size.good{color:#10b981}.bp-actual-size.bad{color:#ef4444}
.bp-add-acts{display:flex;gap:8px;margin-top:12px}

/* Image Editor */
.bp-editor{margin:12px 0}
.bp-editor > label{display:block;font-size:11px;font-weight:700;color:#6366f1;margin-bottom:6px}
.bp-crop-area{width:100%;aspect-ratio:16/10;border-radius:12px;border:2px solid #6366f1;cursor:grab;position:relative;overflow:hidden;background-color:#0f172a}
.bp-crop-area:active{cursor:grabbing}
.bp-crop-overlay{position:absolute;inset:0;pointer-events:none;border:2px solid rgba(255,255,255,.3);border-radius:10px}
.bp-crop-center{position:absolute;top:50%;left:50%;width:20px;height:20px;transform:translate(-50%,-50%);border:2px solid rgba(255,255,255,.4);border-radius:50%}
.bp-zoom-row{display:flex;align-items:center;gap:8px;margin-top:8px}
.bp-zoom-icon{font-size:14px;user-select:none}
.bp-zoom-slider{flex:1;height:4px;accent-color:#6366f1;cursor:pointer}
.bp-zoom-val{font-size:11px;color:#6366f1;font-weight:700;min-width:40px;text-align:center}
.bp-reset-btn{padding:4px 10px;font-size:10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;cursor:pointer;color:#64748b;font-weight:600}
.bp-reset-btn:hover{background:#e2e8f0}

/* Phone */
.bp-phone-wrap{display:flex;flex-direction:column;align-items:center;gap:8px;position:sticky;top:24px}
.bp-phone{width:170px;height:340px;background:#0f172a;border-radius:22px;padding:7px;box-shadow:0 15px 40px rgba(0,0,0,.15);overflow:hidden}
.bp-phone-notch{width:50px;height:5px;background:#1e293b;border-radius:3px;margin:5px auto 7px}
.bp-phone-screen{background:#0f172a;border-radius:15px;height:calc(100% - 17px);overflow:hidden;padding:10px}
.bp-phone-card{width:100%;height:65px;border-radius:11px;background:#1e293b;position:relative;overflow:hidden;background-size:cover;background-position:center;margin-bottom:14px}
.bp-phone-ov{position:absolute;inset:0;background:rgba(0,0,0,.35);border-radius:11px}
.bp-phone-txt{position:relative;z-index:1;padding:10px;text-align:right}
.bp-phone-lbl{color:rgba(255,255,255,.7);font-size:8px}.bp-phone-amt{color:#fff;font-size:16px;font-weight:800;margin-top:3px;font-family:monospace}
.bp-phone-rows{display:flex;flex-direction:column;gap:8px;padding-top:6px}
.bp-r{height:8px;background:#1e293b;border-radius:4px}.bp-r.s{width:60%}
.bp-hint{font-size:10px;color:#94a3b8;text-align:center}

/* Sections */
.bp-section{margin-bottom:28px}
.bp-sec-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid #f1f5f9}
.bp-sec-head h2{font-size:16px;font-weight:800;color:#0f172a;margin:0}
.bp-count{color:#94a3b8;font-weight:500;font-size:13px}
.bp-add-to{padding:6px 14px;background:#f0fdf4;color:#10b981;border:1.5px solid #10b981;border-radius:8px;font-weight:700;cursor:pointer;font-size:11px;transition:.2s}
.bp-add-to:hover{background:#10b981;color:#fff}

/* Cards */
.bp-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.bp-card{border-radius:14px;overflow:hidden;border:2px solid #e2e8f0;background:#fff;transition:.2s}
.bp-card:hover{border-color:#6366f1;box-shadow:0 6px 20px rgba(0,0,0,.08)}
.bp-card.off{opacity:.45}
.bp-card-img{height:85px;position:relative;display:flex;align-items:center;justify-content:center;background-size:cover;background-position:center}
.bp-card-label{color:#fff;font-size:12px;font-weight:700;text-shadow:0 1px 4px rgba(0,0,0,.6);z-index:1;text-align:center;padding:0 8px}
.bp-badge{position:absolute;top:4px;left:4px;font-size:8px;padding:2px 6px;border-radius:4px;font-weight:700;color:#fff}
.bp-badge.anim{background:rgba(99,102,241,.8)}
.bp-badge.grad{background:rgba(16,185,129,.8)}
.bp-badge.off-badge{background:rgba(239,68,68,.8);top:auto;bottom:4px}
.bp-card-bar{display:flex;justify-content:space-between;align-items:center;padding:6px 8px;border-top:1px solid #f1f5f9}
.bp-card-btns{display:flex;gap:4px}
.bp-card-btns button{width:26px;height:26px;border-radius:6px;border:1px solid #e2e8f0;background:#f8fafc;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center;transition:.15s}
.bp-card-btns button:hover{background:#e2e8f0}
.bp-card-btns button:disabled{opacity:.3;cursor:not-allowed}
.bp-del-btn{border-color:#fee2e2!important;background:#fef2f2!important}
.bp-del-btn:hover{background:#fee2e2!important}
.bp-card-order{font-size:10px;color:#94a3b8;font-family:monospace}
.bp-empty{text-align:center;padding:60px;color:#94a3b8;font-size:14px}
</style>
