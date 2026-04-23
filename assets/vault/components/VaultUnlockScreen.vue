<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { KeyRound } from "lucide-vue-next";
import AppButton from "@/components/AppButton.vue";
import AppInput from "@/components/AppInput.vue";
import { deriveKey } from "@/composables/useVaultCrypto.js";

const props = defineProps({
    salt: { type: String, required: true },
});

const emit = defineEmits(["unlocked"]);

const { t } = useI18n();

const password = ref("");
const loading = ref(false);
const error = ref("");

async function handleUnlock() {
    if (!password.value) {
        error.value = t("vault.unlock_password_required");
        return;
    }
    loading.value = true;
    error.value = "";
    try {
        await deriveKey(password.value, props.salt);
        password.value = "";
        emit("unlocked");
    } catch {
        error.value = t("vault.unlock_failed");
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="w-full max-w-sm space-y-6">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600/15 mb-2">
                    <KeyRound class="w-7 h-7 text-indigo-400" :stroke-width="2" />
                </div>
                <h1 class="text-xl font-semibold text-primary">{{ t("vault.unlock_title") }}</h1>
                <p class="text-sm text-secondary">{{ t("vault.unlock_subtitle") }}</p>
            </div>
            <form class="space-y-4" v-on:submit.prevent="handleUnlock">
                <AppInput
                    v-model="password"
                    type="password"
                    :label="t('vault.unlock_password_label')"
                    :placeholder="t('vault.unlock_password_placeholder')"
                    :error="error"
                    :toggleable="true"
                    required
                />
                <AppButton type="submit" class="w-full" :loading="loading">
                    {{ t("vault.unlock_button") }}
                </AppButton>
            </form>
        </div>
    </div>
</template>
