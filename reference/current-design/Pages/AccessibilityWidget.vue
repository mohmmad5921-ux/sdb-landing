<template>
  <!-- Visual filters overlay (moved to root to escape transform containing block) -->
  <div 
    v-if="settings.grayscale || settings.contrast || settings.colorBlindness" 
    class="sdb-access-overlay" 
    :class="{ 'is-grayscale': settings.grayscale, 'is-contrast': settings.contrast, 'is-color-blind': settings.colorBlindness }"
  ></div>

  <!-- Reading Guide -->
  <div v-if="settings.readingGuide" class="sdb-reading-guide" :style="{ top: mouseY + 'px' }"></div>

  <div class="sdb-acc-wrapper" dir="rtl">
    <!-- Floating Toggle Button -->
    <button
      class="sdb-acc-toggle"
      @click="isOpen = !isOpen"

      title="أدوات الوصول - Accessibility"
      :class="{ 'is-open': isOpen }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z"/>
      </svg>
    </button>

    <!-- Sidebar Panel -->
    <Transition name="sdb-acc-slide">
      <div v-if="isOpen" class="sdb-acc-panel">
        <div class="sdb-acc-header">
          <h3>أدوات الوصول</h3>
          <button @click="isOpen = false" class="sdb-acc-close">×</button>
        </div>
        
        <div class="sdb-acc-options">
          <!-- Toggle 1: Font Size -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">A+</span>
            <span class="sdb-acc-label">تكبير الخط</span>
            <input type="checkbox" v-model="settings.largeFont" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 2: Highlight Links -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">🔗</span>
            <span class="sdb-acc-label">إبراز الروابط</span>
            <input type="checkbox" v-model="settings.highlightLinks" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 3: Readable Font -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">T</span>
            <span class="sdb-acc-label">خط مقروء</span>
            <input type="checkbox" v-model="settings.readableFont" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 4: Large Cursor -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">↗</span>
            <span class="sdb-acc-label">مؤشر كبير</span>
            <input type="checkbox" v-model="settings.largeCursor" />
            <div class="sdb-acc-switch"></div>
          </label>
          
          <!-- Toggle 5: Text Spacing -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">⇿</span>
            <span class="sdb-acc-label">تباعد النصوص</span>
            <input type="checkbox" v-model="settings.textSpacing" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 6: Stop Animations -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">⏸</span>
            <span class="sdb-acc-label">إيقاف الحركات</span>
            <input type="checkbox" v-model="settings.stopAnimations" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 7: Hide Images -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">🚫</span>
            <span class="sdb-acc-label">إخفاء الصور</span>
            <input type="checkbox" v-model="settings.hideImages" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 8: Highlight Headers -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">H</span>
            <span class="sdb-acc-label">إبراز العناوين</span>
            <input type="checkbox" v-model="settings.highlightHeaders" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 9: Reading Guide -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">➖</span>
            <span class="sdb-acc-label">دليل القراءة</span>
            <input type="checkbox" v-model="settings.readingGuide" />
            <div class="sdb-acc-switch"></div>
          </label>
          
          <!-- Toggle 10: High Contrast -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">◑</span>
            <span class="sdb-acc-label">تباين عالي</span>
            <input type="checkbox" v-model="settings.contrast" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 11: Grayscale -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">◧</span>
            <span class="sdb-acc-label">تدرج رمادي</span>
            <input type="checkbox" v-model="settings.grayscale" />
            <div class="sdb-acc-switch"></div>
          </label>
          <!-- Toggle 12: Color Blindness -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">👁️</span>
            <span class="sdb-acc-label">عمى الألوان</span>
            <input type="checkbox" v-model="settings.colorBlindness" />
            <div class="sdb-acc-switch"></div>
          </label>

          <!-- Toggle 13: Keyboard Focus -->
          <label class="sdb-acc-item">
            <span class="sdb-acc-icon">⌨️</span>
            <span class="sdb-acc-label">تمييز التركيز</span>
            <input type="checkbox" v-model="settings.keyboardFocus" />
            <div class="sdb-acc-switch"></div>
          </label>
        </div>

        <button @click="resetSettings" class="sdb-acc-reset">
          إعادة تعيين (Reset)
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted } from 'vue';

const isOpen = ref(false);
const mouseY = ref(0);

function updateMouseY(e) {
  if (settings.readingGuide) {
    mouseY.value = e.clientY;
  }
}

const settings = reactive({
  contrast: false,
  grayscale: false,
  largeFont: false,
  highlightLinks: false,
  readableFont: false,
  largeCursor: false,
  stopAnimations: false,
  readingGuide: false,
  textSpacing: false,
  hideImages: false,
  highlightHeaders: false,
  colorBlindness: false,
  keyboardFocus: false
});

// Load from LocalStorage
onMounted(() => {
  window.addEventListener('mousemove', updateMouseY);
  const saved = localStorage.getItem('sdb_accessibility');
  if (saved) {
    Object.assign(settings, JSON.parse(saved));
  }
  const savedOpen = localStorage.getItem('sdb_accessibility_open');
  if (savedOpen === 'true') {
    isOpen.value = true;
  }
  applyClasses();
});

onUnmounted(() => {
  window.removeEventListener('mousemove', updateMouseY);
});

// Watch for changes, save, and apply
watch(() => ({ ...settings }), (newVal) => {
  localStorage.setItem('sdb_accessibility', JSON.stringify(newVal));
  applyClasses();
}, { deep: true });

watch(isOpen, (newVal) => {
  localStorage.setItem('sdb_accessibility_open', newVal.toString());
});

function applyClasses() {
  const cl = document.documentElement.classList; // Use html instead of body
  // We handle contrast/grayscale/readingGuide exclusively via the overlay div which is reactive in the template
  settings.largeFont ? cl.add('sdb-access-large-font') : cl.remove('sdb-access-large-font');
  settings.highlightLinks ? cl.add('sdb-access-links') : cl.remove('sdb-access-links');
  settings.readableFont ? cl.add('sdb-access-readable-font') : cl.remove('sdb-access-readable-font');
  settings.largeCursor ? cl.add('sdb-access-cursor') : cl.remove('sdb-access-cursor');
  settings.stopAnimations ? cl.add('sdb-access-no-anim') : cl.remove('sdb-access-no-anim');
  settings.textSpacing ? cl.add('sdb-access-spacing') : cl.remove('sdb-access-spacing');
  settings.hideImages ? cl.add('sdb-access-no-img') : cl.remove('sdb-access-no-img');
  settings.highlightHeaders ? cl.add('sdb-access-headers') : cl.remove('sdb-access-headers');
  settings.keyboardFocus ? cl.add('sdb-access-focus') : cl.remove('sdb-access-focus');
}

function resetSettings() {
  settings.contrast = false;
  settings.grayscale = false;
  settings.largeFont = false;
  settings.highlightLinks = false;
  settings.readableFont = false;
  settings.largeCursor = false;
  settings.stopAnimations = false;
  settings.readingGuide = false;
  settings.textSpacing = false;
  settings.hideImages = false;
  settings.highlightHeaders = false;
  settings.colorBlindness = false;
  settings.keyboardFocus = false;
}
</script>

<style scoped>
/* Add the visual CSS filter overlay */
.sdb-access-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  z-index: 2000000000; /* Extremely high, but just below the widget */
  pointer-events: none; /* Let all clicks pass through */
}

/* Grayscale Mode */
.sdb-access-overlay.is-grayscale {
  backdrop-filter: grayscale(100%);
  -webkit-backdrop-filter: grayscale(100%);
}

/* Color Blindness Lens (shifts colors for Deuteranopia/Protanopia support safely) */
.sdb-access-overlay.is-color-blind {
  backdrop-filter: sepia(40%) hue-rotate(-15deg) saturate(150%) contrast(110%);
  -webkit-backdrop-filter: sepia(40%) hue-rotate(-15deg) saturate(150%) contrast(110%);
}

/* High Contrast Mode - The mix-blend-mode strategy */
.sdb-access-overlay.is-contrast {
  mix-blend-mode: difference;
  background-color: white; /* Inverts all colors underneath perfectly */
}

/* Reading Guide */
.sdb-reading-guide {
  position: fixed;
  left: 0;
  right: 0;
  height: 8px;
  background: rgba(255, 235, 59, 0.6);
  border-top: 2px solid #000;
  border-bottom: 2px solid #000;
  z-index: 2000000001; /* Above the overlay but below the widget wrapper */
  pointer-events: none;
  transform: translateY(-50%);
  box-shadow: 0 0 20px rgba(255, 235, 59, 1);
}

/* Accessibility Widget Wrapper */
.sdb-acc-wrapper {
  position: fixed;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2147483647; /* Highest possible */
}

/* Floating Toggle Button */
.sdb-acc-toggle {
  background: #1B5E20; /* SDB Green */
  color: white;
  border: none;
  width: 52px;
  height: 52px;
  border-radius: 0 8px 8px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 2px 0 10px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
}
.sdb-acc-toggle svg {
  width: 28px;
  height: 28px;
  fill: currentColor;
}
.sdb-acc-toggle:hover {
  width: 58px;
  background: #2E7D32;
}
.sdb-acc-toggle.is-open {
  box-shadow: none;
}

/* Sidebar Panel */
.sdb-acc-panel {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  background: #ffffff;
  width: 300px;
  border-radius: 0 16px 16px 0;
  box-shadow: 5px 0 25px rgba(0,0,0,0.15);
  border: 1px solid #e0e0e0;
  border-left: none;
  display: flex;
  flex-direction: column;
}

.sdb-acc-header {
  background: #1B5E20;
  color: white;
  padding: 16px 20px;
  border-radius: 0 16px 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.sdb-acc-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}
.sdb-acc-close {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  opacity: 0.8;
}
.sdb-acc-close:hover {
  opacity: 1;
}

.sdb-acc-options {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 60vh;
  overflow-y: auto;
}

.sdb-acc-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: #f8f9fa;
  border: 1px solid #eeeeee;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}
.sdb-acc-item:hover {
  background: #f1f3f5;
}

.sdb-acc-icon {
  font-size: 18px;
  width: 24px;
  text-align: center;
  color: #1B5E20;
  font-weight: bold;
}
.sdb-acc-label {
  flex: 1;
  margin: 0 12px;
  font-size: 14px;
  color: #333;
  font-weight: 500;
}

/* Custom Switch */
.sdb-acc-item input[type="checkbox"] {
  display: none;
}
.sdb-acc-switch {
  width: 40px;
  height: 22px;
  background: #ccc;
  border-radius: 20px;
  position: relative;
  transition: 0.3s;
}
.sdb-acc-switch::after {
  content: '';
  position: absolute;
  top: 2px;
  right: 2px;
  width: 18px;
  height: 18px;
  background: white;
  border-radius: 50%;
  transition: 0.3s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.sdb-acc-item input:checked + .sdb-acc-switch {
  background: #1B5E20;
}
.sdb-acc-item input:checked + .sdb-acc-switch::after {
  transform: translateX(-18px);
}

.sdb-acc-reset {
  margin: 16px;
  padding: 12px;
  background: #f1f3f5;
  border: 1px solid #ddd;
  border-radius: 8px;
  color: #555;
  font-weight: 500;
  cursor: pointer;
  transition: 0.2s;
}
.sdb-acc-reset:hover {
  background: #e9ecef;
  color: #000;
}

/* Vue Transitions */
.sdb-acc-slide-enter-active,
.sdb-acc-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.sdb-acc-slide-enter-from,
.sdb-acc-slide-leave-to {
  transform: translate(-100%, -50%);
  opacity: 0;
}

@media(max-width: 600px) {
  .sdb-acc-wrapper {
    top: 50%; /* Re-center vertically to avoid pushing panel top off-screen */
  }
  .sdb-acc-panel {
    width: 290px;
    height: 85vh; /* Explicit maximum height bounds to prevent overflow */
    max-height: 85vh;
    border-radius: 0 16px 16px 0;
  }
  .sdb-acc-options {
    flex: 1;
    max-height: calc(85vh - 120px);
    overflow-y: auto;
  }
  .sdb-acc-toggle {
    width: 44px;
    height: 44px;
  }
  .sdb-acc-toggle svg {
    width: 22px;
    height: 22px;
  }
  .sdb-acc-slide-enter-active,
  .sdb-acc-slide-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
  }
  .sdb-acc-slide-enter-from,
  .sdb-acc-slide-leave-to {
    transform: translate(-100%, -50%);
    opacity: 0;
  }
}
</style>
