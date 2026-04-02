<script setup>
import { computed } from 'vue';
import { callState, endAdminCall, toggleMute, sendMissedCallEmail, dismissCallResult } from '@/Composables/useAdminCall';

const isActive = callState.isActive;
const isConnecting = callState.isConnecting;
const isConnected = callState.isConnected;
const isMuted = callState.isMuted;
const receiver = computed(() => callState.receiverName.value);
const duration = callState.duration;
const callResult = callState.callResult;
const emailSent = callState.emailSent;
const emailSending = callState.emailSending;

const formatDuration = (secs) => {
  const m = Math.floor(secs / 60).toString().padStart(2, '0');
  const s = (secs % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
};

const resultLabel = computed(() => {
  const r = callResult.value;
  if (r === 'declined') return 'رفض العميل المكالمة';
  if (r === 'missed') return 'مكالمة فائتة';
  if (r === 'no_answer') return 'لم يرد العميل';
  if (r === 'ended') return 'انتهت المكالمة';
  return '';
});
</script>

<template>
  <Teleport to="body">
    <!-- Hidden audio element for WebRTC -->
    <audio id="adminRemoteAudio" autoplay></audio>

    <!-- ═══ CALL BAR — Fixed bottom strip ═══ -->
    <Transition name="call-bar">
      <div v-if="isActive"
           class="call-bar"
           :class="{ 'call-bar--result': callResult }"
           style="direction: rtl">

        <!-- ═══ ACTIVE CALL STATE ═══ -->
        <template v-if="!callResult">
          <!-- Status indicator -->
          <div class="call-bar__status">
            <span class="call-bar__pulse" :class="{ connected: isConnected }"></span>
            <span class="call-bar__status-text">
              {{ isConnecting ? '🔔 يرنّ...' : '✅ العميل فتح الخط' }}
            </span>
          </div>

          <!-- Name + Duration -->
          <div class="call-bar__info">
            <span class="call-bar__name">📞 {{ receiver }}</span>
            <span v-if="isConnected" class="call-bar__duration">{{ formatDuration(duration) }}</span>
          </div>

          <!-- Controls -->
          <div class="call-bar__controls">
            <!-- Mute -->
            <button @click="toggleMute()"
                    class="call-bar__btn call-bar__btn--mute"
                    :class="{ active: isMuted }"
                    :title="isMuted ? 'إلغاء الكتم' : 'كتم الصوت'">
              {{ isMuted ? '🔇' : '🎙️' }}
            </button>

            <!-- End Call -->
            <button @click="endAdminCall()"
                    class="call-bar__btn call-bar__btn--end"
                    title="إنهاء المكالمة">
              📕
            </button>
          </div>
        </template>

        <!-- ═══ POST-CALL RESULT STATE ═══ -->
        <template v-else>
          <!-- Result message -->
          <div class="call-bar__result-info">
            <span class="call-bar__result-icon">
              {{ callResult === 'declined' ? '🚫' : callResult === 'ended' ? '✅' : '📵' }}
            </span>
            <span class="call-bar__result-name">{{ receiver }}</span>
            <span class="call-bar__result-label">— {{ resultLabel }}</span>
          </div>

          <!-- Actions -->
          <div class="call-bar__controls">
            <!-- Send Email (only for missed/declined/no_answer) -->
            <button v-if="['missed', 'declined', 'no_answer'].includes(callResult) && !emailSent"
                    @click="sendMissedCallEmail()"
                    class="call-bar__btn call-bar__btn--email"
                    :disabled="emailSending"
                    title="إرسال إيميل للعميل">
              {{ emailSending ? '⏳' : '📧' }}
              <span class="btn-label">{{ emailSending ? 'جاري الإرسال...' : 'إرسال إيميل' }}</span>
            </button>

            <!-- Email sent confirmation -->
            <span v-if="emailSent" class="call-bar__email-sent">
              ✅ تم إرسال الإيميل
            </span>

            <!-- Dismiss -->
            <button @click="dismissCallResult()"
                    class="call-bar__btn call-bar__btn--dismiss"
                    title="إغلاق">
              ✕
            </button>
          </div>
        </template>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ═══ Call Bar — Fixed Bottom Strip ═══ */
.call-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 10px 24px;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  border-top: 2px solid #10b981;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
  color: #fff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.call-bar--result {
  border-top-color: #f59e0b;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

/* Status */
.call-bar__status {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.call-bar__pulse {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #fbbf24;
  animation: pulse-glow 1.5s ease-in-out infinite;
}

.call-bar__pulse.connected {
  background: #10b981;
  animation: none;
}

.call-bar__status-text {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
  white-space: nowrap;
}

/* Info */
.call-bar__info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}

.call-bar__name {
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.call-bar__duration {
  font-size: 14px;
  font-weight: 600;
  color: #10b981;
  font-variant-numeric: tabular-nums;
  background: rgba(16, 185, 129, 0.1);
  padding: 2px 10px;
  border-radius: 12px;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

/* Result info */
.call-bar__result-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.call-bar__result-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.call-bar__result-name {
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
}

.call-bar__result-label {
  font-size: 13px;
  color: #fbbf24;
  font-weight: 500;
  white-space: nowrap;
}

/* Controls */
.call-bar__controls {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  margin-right: auto;
}

.call-bar__btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border: none;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.call-bar__btn--mute {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.call-bar__btn--mute.active {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.3);
}

.call-bar__btn--mute:hover {
  background: rgba(255, 255, 255, 0.2);
}

.call-bar__btn--end {
  background: #ef4444;
  color: #fff;
  padding: 6px 18px;
}

.call-bar__btn--end:hover {
  background: #dc2626;
  transform: scale(1.05);
}

.call-bar__btn--email {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: #fff;
  padding: 6px 16px;
}

.call-bar__btn--email:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: scale(1.03);
}

.call-bar__btn--email:disabled {
  opacity: 0.6;
  cursor: wait;
}

.call-bar__btn--dismiss {
  background: rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  padding: 6px 10px;
  font-size: 14px;
}

.call-bar__btn--dismiss:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
}

.btn-label {
  font-size: 12px;
}

.call-bar__email-sent {
  font-size: 12px;
  color: #10b981;
  font-weight: 600;
  padding: 4px 12px;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 12px;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

/* ═══ Animations ═══ */
@keyframes pulse-glow {
  0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.5); }
  50% { opacity: 0.7; box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
}

.call-bar-enter-active {
  animation: slideUp 0.3s ease-out;
}

.call-bar-leave-active {
  animation: slideDown 0.3s ease-in;
}

@keyframes slideUp {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes slideDown {
  from { transform: translateY(0); opacity: 1; }
  to { transform: translateY(100%); opacity: 0; }
}
</style>
