<script setup>
import { useI18n } from "vue-i18n";
import AppModal from "@/components/AppModal.vue";
import { RECORD_TYPES, RECORD_TYPE_KEYS } from "@/vault/recordTypes.js";

defineProps({
    show: { type: Boolean, required: true },
});

const emit = defineEmits(["close", "pick"]);

const { t } = useI18n();
</script>

<template>
    <AppModal :show="show" max-width="3xl" v-on:close="emit('close')">
        <h2 class="text-base font-semibold text-primary">{{ t("vault.pick_type") }}</h2>
        <p class="text-sm text-secondary">{{ t("vault.pick_type_subtitle") }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
            <button
                v-for="typeKey in RECORD_TYPE_KEYS"
                :key="typeKey"
                type="button"
                class="flex flex-col items-center gap-2 p-3 rounded-lg bg-surface-2/50 border border-line hover:border-indigo-500/60 hover:bg-surface-2 transition-colors text-center"
                v-on:click="emit('pick', typeKey)"
            >
                <component :is="RECORD_TYPES[typeKey].icon" class="w-6 h-6 text-indigo-400" :stroke-width="2" />
                <span class="text-xs font-medium text-primary">{{ t(`vault.types.${typeKey}`) }}</span>
            </button>
        </div>
    </AppModal>
</template>
