<template>
  <AdminLayout>
    <div class="am">
      <!-- Header -->
      <div class="am-head">
        <div>
          <h1>إدارة الصور والشعارات</h1>
          <p class="am-sub">{{ assets.length }} ملف — {{ totalSize }}</p>
        </div>
        <button class="am-btn-up" @click="showUpload = !showUpload">+ رفع صورة جديدة</button>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="am-alert am-alert-ok">✅ {{ $page.props.flash.success }}</div>
      <div v-if="$page.props.flash?.error" class="am-alert am-alert-err">❌ {{ $page.props.flash.error }}</div>

      <!-- Upload -->
      <div v-if="showUpload" class="am-upload">
        <h3>رفع صورة جديدة</h3>
        <form @submit.prevent="uploadNew">
          <div class="am-upload-row">
            <div class="am-f"><label>اختر ملف</label><input type="file" @change="newFile = $event.target.files[0]" accept="image/*,.svg,.ico" required /></div>
            <div class="am-f"><label>اسم الملف (اختياري)</label><input v-model="newFilename" type="text" placeholder="logo-new.png" /></div>
          </div>
          <div class="am-upload-row" style="margin-top: 10px; align-items: center;">
            <div class="am-f">
              <label>📐 العرض (px)</label>
              <div class="am-stepper"><button type="button" class="am-step-btn" @click="uploadWidth = Math.max(10, (uploadWidth || 500) - 50)">−</button><input v-model.number="uploadWidth" type="number" min="10" max="5000" placeholder="أصلي" /><button type="button" class="am-step-btn" @click="uploadWidth = Math.min(5000, (uploadWidth || 500) + 50)">+</button></div>
            </div>
            <div class="am-f">
              <label>📐 الارتفاع (px)</label>
              <div class="am-stepper"><button type="button" class="am-step-btn" @click="uploadHeight = Math.max(10, (uploadHeight || 500) - 50)">−</button><input v-model.number="uploadHeight" type="number" min="10" max="5000" placeholder="تناسبي" /><button type="button" class="am-step-btn" @click="uploadHeight = Math.min(5000, (uploadHeight || 500) + 50)">+</button></div>
            </div>
            <button type="submit" class="am-btn-up" :disabled="uploading">{{ uploading ? 'جاري الرفع...' : '⬆ رفع' }}</button>
          </div>
          <p class="am-resize-note">💡 اتركهم فارغين = الحجم الأصلي. حط عرض فقط = الارتفاع تلقائي.</p>
        </form>
      </div>

      <!-- Filters -->
      <div class="am-filters">
        <button v-for="(cat, key) in allCats" :key="key" :class="['am-ftag', act === key && 'on']" @click="act = act === key ? null : key">
          {{ cat.icon }} {{ cat.label }} <span class="am-cnt">{{ cntCat(key) }}</span>
        </button>
      </div>

      <!-- Grid -->
      <div class="am-grid">
        <div v-for="a in filtered" :key="a.filename" class="am-card">
          <div class="am-img" @click="preview = a">
            <img :src="a.url + '?v=' + Date.now()" :alt="a.filename" loading="lazy" />
            <div class="am-hover">👁 معاينة</div>
          </div>

          <div class="am-info">
            <div class="am-name">{{ a.filename }}</div>
            <div class="am-path">📂 {{ a.full_path }}</div>
            <div class="am-meta">
              <span class="am-tag">{{ categories[a.category]?.icon }} {{ categories[a.category]?.label }}</span>
              <span>{{ a.size_human }}</span>
            </div>
            <!-- Dimensions prominently -->
            <div v-if="a.dimensions" class="am-dims">📐 {{ a.dimensions }}</div>
            <div v-if="a.usage_count > 0" class="am-usage">
              <div class="am-usage-title">📍 مستخدمة في {{ a.usage_count }} مكان:</div>
              <div v-for="u in a.usages" :key="u.file" class="am-usage-item">
                <span class="am-usage-type">{{ u.type }}</span>
                <span class="am-usage-page">{{ u.page }}</span>
                <span class="am-usage-file">{{ u.file }}</span>
              </div>
            </div>
            <div v-else class="am-no-usage">⚠ غير مستخدمة في أي صفحة</div>
          </div>

          <div class="am-acts">
            <button class="am-abtn am-abtn-sz" @click.stop.prevent="startResize(a)">📐 حجم</button>
            <label class="am-abtn am-abtn-rep">🔄 استبدال <input type="file" @change="startReplace(a, $event)" accept="image/*,.svg,.ico" hidden /></label>
            <button class="am-abtn am-abtn-del" @click.stop.prevent="pendingDelete = a">🗑 حذف</button>
            <a :href="a.url" download class="am-abtn am-abtn-dl" @click.stop>⬇ تحميل</a>
          </div>
        </div>
      </div>

      <div v-if="filtered.length === 0" class="am-empty">لا توجد صور في هذا التصنيف</div>

      <!-- Preview Modal -->
      <div v-if="preview" class="am-modal" @click.self="preview = null">
        <div class="am-modal-box">
          <button class="am-modal-x" @click="preview = null">✕</button>
          <img :src="preview.url" :alt="preview.filename" />
          <div class="am-modal-info">
            <h3>{{ preview.filename }}</h3>
            <p>{{ preview.size_human }} — {{ preview.dimensions || preview.extension }} — {{ preview.modified }}</p>
            <p class="am-modal-path">📂 {{ preview.full_path }}</p>
            <div v-if="preview.usage_count > 0" class="am-modal-usages">
              <strong>📍 مستخدمة في:</strong>
              <div v-for="u in preview.usages" :key="u.file">{{ u.page }} ({{ u.file }})</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation -->
      <div v-if="pendingDelete" class="am-modal" @click.self="pendingDelete = null">
        <div class="am-confirm-box">
          <div class="am-confirm-icon">🗑</div>
          <h3>حذف {{ pendingDelete.filename }}؟</h3>
          <p>سيتم حذفه من السيرفر و GitHub</p>
          <div v-if="pendingDelete.usage_count > 0" class="am-confirm-warn">
            🚨 تحذير: هذه الصورة مستخدمة في {{ pendingDelete.usage_count }} مكان!<br>الحذف قد يسبب أخطاء في الموقع.
          </div>
          <div class="am-confirm-acts">
            <button class="am-confirm-cancel" @click="pendingDelete = null">إلغاء</button>
            <button class="am-confirm-ok" @click="doDelete" :disabled="deleting">{{ deleting ? 'جاري الحذف...' : '🗑 نعم، احذف' }}</button>
          </div>
        </div>
      </div>

      <!-- Replace Confirmation with Resize -->
      <div v-if="pendingReplace" class="am-modal" @click.self="cancelReplace">
        <div class="am-confirm-box">
          <div class="am-confirm-icon">🔄</div>
          <h3>استبدال {{ pendingReplace.filename }}؟</h3>
          <p>الحجم الحالي: <strong>{{ pendingReplace.dimensions || 'غير معروف' }}</strong></p>
          <p>سيتم حفظ نسخة احتياطية + تحديث الموقع فوراً</p>
          <div class="am-resize-fields">
            <div class="am-rf">
              <label>📐 العرض (px)</label>
              <div class="am-stepper"><button type="button" class="am-step-btn" @click="resizeW = Math.max(10, (resizeW || parseInt(pendingReplace.dimensions?.split('×')[0]) || 500) - 50)">−</button><input v-model.number="resizeW" type="number" min="10" max="5000" :placeholder="pendingReplace.dimensions ? pendingReplace.dimensions.split('×')[0] : 'أصلي'" /><button type="button" class="am-step-btn" @click="resizeW = Math.min(5000, (resizeW || parseInt(pendingReplace.dimensions?.split('×')[0]) || 500) + 50)">+</button></div>
            </div>
            <div class="am-rf">
              <label>📐 الارتفاع (px)</label>
              <div class="am-stepper"><button type="button" class="am-step-btn" @click="resizeH = Math.max(10, (resizeH || parseInt(pendingReplace.dimensions?.split('×')[1]) || 500) - 50)">−</button><input v-model.number="resizeH" type="number" min="10" max="5000" :placeholder="pendingReplace.dimensions ? pendingReplace.dimensions.split('×')[1] : 'تناسبي'" /><button type="button" class="am-step-btn" @click="resizeH = Math.min(5000, (resizeH || parseInt(pendingReplace.dimensions?.split('×')[1]) || 500) + 50)">+</button></div>
            </div>
          </div>
          <p class="am-resize-hint">💡 اتركهم فارغين = الحجم الأصلي. حط عرض فقط = الارتفاع تلقائي.</p>
          <div class="am-confirm-acts">
            <button class="am-confirm-cancel" @click="cancelReplace">إلغاء</button>
            <button class="am-confirm-ok am-confirm-blue" @click="doReplace" :disabled="replacing">{{ replacing ? 'جاري الاستبدال...' : '🔄 نعم، استبدل' }}</button>
          </div>
        </div>
      </div>

      <!-- Resize Live Preview Modal -->
      <div v-if="resizeTarget" class="am-modal" @click.self="resizeTarget = null">
        <div class="am-resize-modal">
          <button class="am-modal-x" @click="resizeTarget = null">✕</button>
          <h3>📐 تغيير حجم {{ resizeTarget.filename }}</h3>
          <p class="am-resize-current">الحجم الحالي: <strong>{{ resizeTarget.dimensions || '?' }}</strong></p>
          
          <!-- Live Preview -->
          <div class="am-live-wrap">
            <div class="am-live-box">
              <img :src="resizeTarget.url + '?v=' + Date.now()" :alt="resizeTarget.filename" :style="{ width: rszW + 'px', height: rszH + 'px' }" />
            </div>
            <div class="am-live-label">{{ rszW || '?' }} × {{ rszH || '?' }} px</div>
          </div>

          <!-- Controls -->
          <div class="am-resize-fields">
            <div class="am-rf">
              <label>العرض</label>
              <div class="am-stepper"><button type="button" class="am-step-btn" @click="rszW = Math.max(10, (rszW || 100) - 50)">−</button><input v-model.number="rszW" type="number" min="10" max="5000" /><button type="button" class="am-step-btn" @click="rszW = Math.min(5000, (rszW || 100) + 50)">+</button></div>
            </div>
            <div class="am-rf">
              <label>الارتفاع</label>
              <div class="am-stepper"><button type="button" class="am-step-btn" @click="rszH = Math.max(10, (rszH || 100) - 50)">−</button><input v-model.number="rszH" type="number" min="10" max="5000" /><button type="button" class="am-step-btn" @click="rszH = Math.min(5000, (rszH || 100) + 50)">+</button></div>
            </div>
          </div>

          <div class="am-confirm-acts">
            <button class="am-confirm-cancel" @click="resizeTarget = null">إلغاء</button>
            <button class="am-confirm-ok am-confirm-blue" @click="doResize" :disabled="resizing">{{ resizing ? 'جاري التغيير...' : '✅ طبّق الحجم' }}</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ assets: Array, categories: Object, totalSize: String })

const showUpload = ref(false)
const uploading = ref(false)
const newFile = ref(null)
const newFilename = ref('')
const uploadWidth = ref(null)
const uploadHeight = ref(null)
const act = ref(null)
const preview = ref(null)

const pendingDelete = ref(null)
const deleting = ref(false)

const pendingReplace = ref(null)
const replacing = ref(false)
const resizeW = ref(null)
const resizeH = ref(null)
let replaceFile = null

// Direct resize state
const resizeTarget = ref(null)
const rszW = ref(null)
const rszH = ref(null)
const resizing = ref(false)

const allCats = computed(() => ({ all: { label: 'الكل', icon: '📁' }, ...props.categories }))
const filtered = computed(() => !act.value || act.value === 'all' ? props.assets : props.assets.filter(a => a.category === act.value))
function cntCat(k) { return k === 'all' ? props.assets.length : props.assets.filter(a => a.category === k).length }

function uploadNew() {
  if (!newFile.value) return
  uploading.value = true
  const f = new FormData()
  f.append('file', newFile.value)
  if (newFilename.value) f.append('filename', newFilename.value)
  if (uploadWidth.value) f.append('resize_width', uploadWidth.value)
  if (uploadHeight.value) f.append('resize_height', uploadHeight.value)
  router.post('/admin/assets/upload', f, { forceFormData: true, onFinish: () => { uploading.value = false; newFile.value = null; newFilename.value = ''; uploadWidth.value = null; uploadHeight.value = null; showUpload.value = false } })
}

function startReplace(asset, e) {
  const file = e.target.files[0]
  if (!file) return
  replaceFile = file
  pendingReplace.value = asset
  // Pre-fill with old image dimensions so new image matches
  if (asset.dimensions && asset.dimensions !== 'SVG') {
    const parts = asset.dimensions.split('×')
    resizeW.value = parseInt(parts[0]) || null
    resizeH.value = parseInt(parts[1]) || null
  } else {
    resizeW.value = null
    resizeH.value = null
  }
  e.target.value = ''
}

function cancelReplace() {
  pendingReplace.value = null
  replaceFile = null
  resizeW.value = null
  resizeH.value = null
}

function doReplace() {
  if (!pendingReplace.value || !replaceFile) return
  replacing.value = true
  const f = new FormData()
  f.append('file', replaceFile)
  f.append('filename', pendingReplace.value.filename)
  if (resizeW.value) f.append('resize_width', resizeW.value)
  if (resizeH.value) f.append('resize_height', resizeH.value)
  router.post('/admin/assets/replace', f, {
    forceFormData: true,
    onFinish: () => { replacing.value = false; pendingReplace.value = null; replaceFile = null; resizeW.value = null; resizeH.value = null }
  })
}

function doDelete() {
  if (!pendingDelete.value) return
  deleting.value = true
  router.post('/admin/assets/delete', { filename: pendingDelete.value.filename }, {
    onFinish: () => { deleting.value = false; pendingDelete.value = null }
  })
}

function startResize(asset) {
  resizeTarget.value = asset
  if (asset.dimensions && asset.dimensions !== 'SVG') {
    const parts = asset.dimensions.split('×')
    rszW.value = parseInt(parts[0]) || 200
    rszH.value = parseInt(parts[1]) || 200
  } else {
    rszW.value = 200
    rszH.value = 200
  }
}

function doResize() {
  if (!resizeTarget.value || !rszW.value || !rszH.value) return
  resizing.value = true
  router.post('/admin/assets/resize', { filename: resizeTarget.value.filename, resize_width: rszW.value, resize_height: rszH.value }, {
    onFinish: () => { resizing.value = false; resizeTarget.value = null }
  })
}
</script>

<style scoped>
.am { padding: 0; }

.am-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.am-head h1 { font-size: 1.6rem; font-weight: 800; margin: 0; color: #1a1a2e; }
.am-sub { color: #6b7280; font-size: 0.95rem; margin-top: 4px; }

.am-btn-up { padding: 10px 20px; border-radius: 10px; border: none; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all .2s; }
.am-btn-up:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(99,102,241,.35); }
.am-btn-up:disabled { opacity: .5; }

.am-alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 16px; font-size: 0.95rem; font-weight: 600; }
.am-alert-ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; }
.am-alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

.am-upload { background: #f8f9fb; border: 2px dashed #d1d5db; border-radius: 14px; padding: 20px; margin-bottom: 20px; }
.am-upload h3 { margin: 0 0 14px; font-size: 1.1rem; color: #1a1a2e; }
.am-upload-row { display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; }
.am-f { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 140px; }
.am-f label { font-size: 0.85rem; color: #6b7280; font-weight: 600; }
.am-f input[type="text"], .am-f input[type="number"] { background: #fff; border: 1px solid #e5e7eb; color: #1a1a2e; padding: 10px 14px; border-radius: 10px; font-size: 0.9rem; }
.am-f input[type="file"] { font-size: 0.85rem; color: #6b7280; }

.am-filters { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.am-ftag { background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 0.9rem; font-weight: 600; transition: all .2s; display: flex; align-items: center; gap: 6px; }
.am-ftag:hover { border-color: #6366f1; color: #4f46e5; }
.am-ftag.on { background: #eef2ff; border-color: #6366f1; color: #4f46e5; font-weight: 700; }
.am-cnt { background: #e5e7eb; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; }
.am-ftag.on .am-cnt { background: #c7d2fe; }

.am-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px; }

.am-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; overflow: hidden; transition: all .25s; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.am-card:hover { border-color: #6366f1; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }

.am-img { position: relative; height: 180px; background: repeating-conic-gradient(#f3f4f6 0% 25%, #fff 0% 50%) 0 0 / 20px 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; }
.am-img img { max-width: 90%; max-height: 160px; object-fit: contain; transition: transform .3s; }
.am-img:hover img { transform: scale(1.05); }
.am-hover { position: absolute; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .2s; font-size: 1.1rem; font-weight: 700; color: #fff; }
.am-img:hover .am-hover { opacity: 1; }

.am-info { padding: 14px 16px 10px; }
.am-name { font-size: 1rem; font-weight: 800; color: #1a1a2e; margin-bottom: 4px; direction: ltr; text-align: left; word-break: break-all; }
.am-path { font-size: 0.8rem; color: #9ca3af; font-family: 'Courier New', monospace; direction: ltr; text-align: left; margin-bottom: 8px; }
.am-meta { display: flex; gap: 10px; font-size: 0.85rem; color: #6b7280; flex-wrap: wrap; margin-bottom: 6px; }
.am-tag { background: #eef2ff; color: #4f46e5; padding: 3px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; }

.am-dims { background: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; padding: 6px 12px; border-radius: 8px; font-size: 0.95rem; font-weight: 700; margin-bottom: 8px; direction: ltr; text-align: center; }

.am-usage { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-top: 6px; }
.am-usage-title { font-size: 0.85rem; font-weight: 700; color: #059669; margin-bottom: 6px; }
.am-usage-item { display: flex; gap: 8px; align-items: center; font-size: 0.8rem; padding: 3px 0; flex-wrap: wrap; }
.am-usage-type { background: #d1fae5; color: #047857; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
.am-usage-page { color: #1a1a2e; font-weight: 700; font-size: 0.85rem; }
.am-usage-file { color: #9ca3af; font-family: monospace; font-size: 0.7rem; direction: ltr; }

.am-no-usage { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 8px 12px; font-size: 0.85rem; color: #d97706; font-weight: 600; margin-top: 6px; }

.am-acts { display: flex; gap: 8px; padding: 10px 16px 16px; flex-wrap: wrap; }
.am-abtn { padding: 8px 14px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: all .2s; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.am-abtn-rep { background: #f3f4f6; color: #374151; cursor: pointer; }
.am-abtn-rep:hover { background: #e5e7eb; color: #1a1a2e; }
.am-abtn-del { background: #fef2f2; color: #dc2626; }
.am-abtn-del:hover { background: #fee2e2; }
.am-abtn-dl { background: #ecfdf5; color: #059669; }
.am-abtn-dl:hover { background: #d1fae5; }
.am-abtn-sz { background: #f0f9ff; color: #0369a1; }
.am-abtn-sz:hover { background: #e0f2fe; color: #0c4a6e; }

.am-empty { text-align: center; padding: 60px; color: #9ca3af; font-size: 1.1rem; }

/* Modals */
.am-modal { position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.7); display: flex; align-items: center; justify-content: center; padding: 40px; }
.am-modal-box { position: relative; max-width: 90vw; max-height: 90vh; text-align: center; background: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.3); overflow: auto; }
.am-modal-box img { max-width: 100%; max-height: 60vh; border-radius: 12px; }
.am-modal-x { position: absolute; top: 10px; right: 10px; width: 36px; height: 36px; border-radius: 50%; background: #f3f4f6; border: none; color: #1a1a2e; font-size: 1.2rem; cursor: pointer; z-index: 1; }
.am-modal-info { margin-top: 14px; text-align: right; }
.am-modal-info h3 { color: #1a1a2e; margin: 0 0 6px; font-size: 1.1rem; direction: ltr; text-align: left; }
.am-modal-info p { margin: 0; font-size: 0.9rem; color: #6b7280; }
.am-modal-path { font-family: monospace; color: #9ca3af; direction: ltr; text-align: left; }
.am-modal-usages { margin-top: 10px; font-size: 0.85rem; color: #374151; text-align: right; }
.am-modal-usages div { color: #6b7280; font-size: 0.8rem; padding: 2px 0; }

/* Confirm Modal */
.am-confirm-box { background: #fff; border-radius: 20px; padding: 30px; text-align: center; max-width: 460px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.3); animation: am-pop .2s ease; }
@keyframes am-pop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.am-confirm-icon { font-size: 3rem; margin-bottom: 12px; }
.am-confirm-box h3 { font-size: 1.2rem; color: #1a1a2e; margin: 0 0 8px; }
.am-confirm-box p { font-size: 0.9rem; color: #6b7280; margin: 0 0 12px; }
.am-confirm-warn { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; margin-bottom: 16px; }
.am-confirm-acts { display: flex; gap: 10px; justify-content: center; margin-top: 16px; }
.am-confirm-cancel { padding: 10px 24px; border-radius: 10px; border: 1px solid #e5e7eb; background: #f3f4f6; color: #374151; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all .2s; }
.am-confirm-cancel:hover { background: #e5e7eb; }
.am-confirm-ok { padding: 10px 24px; border-radius: 10px; border: none; background: #dc2626; color: #fff; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all .2s; }
.am-confirm-ok:hover { background: #b91c1c; }
.am-confirm-ok:disabled { opacity: .5; cursor: not-allowed; }
.am-confirm-blue { background: #6366f1; }
.am-confirm-blue:hover { background: #4f46e5; }

/* Resize fields in replace modal */
.am-resize-fields { display: flex; gap: 12px; justify-content: center; margin: 12px 0; }
.am-rf { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.am-rf label { font-size: 0.8rem; color: #6b7280; font-weight: 600; }
.am-rf input { background: #f9fafb; border: 1px solid #e5e7eb; color: #1a1a2e; padding: 8px 12px; border-radius: 8px; font-size: 0.9rem; text-align: center; }
.am-rf input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 2px rgba(99,102,241,.15); }
.am-resize-hint { font-size: 0.75rem !important; color: #9ca3af !important; margin: 8px 0 0 !important; }

/* Stepper */
.am-stepper { display: flex; align-items: center; gap: 0; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; }
.am-step-btn { width: 40px; height: 40px; border: none; background: #f3f4f6; color: #1a1a2e; font-size: 1.3rem; font-weight: 700; cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.am-step-btn:hover { background: #6366f1; color: #fff; }
.am-step-btn:active { background: #4f46e5; color: #fff; transform: scale(0.95); }
.am-stepper input { flex: 1; border: none; background: transparent; text-align: center; font-size: 1rem; font-weight: 700; color: #1a1a2e; padding: 8px 4px; min-width: 60px; outline: none; -moz-appearance: textfield; }
.am-stepper input::-webkit-outer-spin-button, .am-stepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.am-resize-note { font-size: 0.75rem; color: #9ca3af; margin: 8px 0 0; }

/* Resize Live Preview Modal */
.am-resize-modal { background: #fff; border-radius: 20px; padding: 24px; text-align: center; max-width: 560px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.3); animation: am-pop .2s ease; position: relative; max-height: 90vh; overflow-y: auto; }
.am-resize-modal h3 { font-size: 1.2rem; color: #1a1a2e; margin: 0 0 4px; }
.am-resize-current { font-size: 0.9rem; color: #6b7280; margin: 0 0 16px; }
.am-live-wrap { display: flex; flex-direction: column; align-items: center; margin-bottom: 16px; }
.am-live-box { border: 2px dashed #6366f1; border-radius: 12px; overflow: auto; display: inline-flex; align-items: center; justify-content: center; background: repeating-conic-gradient(#f3f4f6 0% 25%, #fff 0% 50%) 0 0 / 16px 16px; max-width: 100%; max-height: 400px; }
.am-live-box img { display: block; object-fit: contain; transition: width .2s, height .2s; }
.am-live-label { margin-top: 8px; font-size: 1.1rem; font-weight: 800; color: #6366f1; direction: ltr; }
</style>
