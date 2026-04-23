<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { Folder, FolderOpen, Settings } from "lucide-vue-next";

const props = defineProps({
    modelValue: { type: [String, Number], required: true },
    folders: { type: Array, required: true },
    entries: { type: Array, required: true },
});

const emit = defineEmits(["update:modelValue", "manage"]);

const { t } = useI18n();

const uncategorizedCount = computed(() => props.entries.filter((e) => !e.folderId).length);

function countInFolder(folderId) {
    return props.entries.filter((e) => e.folderId === folderId).length;
}

function select(value) {
    emit("update:modelValue", value);
}

function chipClass(active) {
    return active
        ? "bg-indigo-600/15 text-indigo-400 border-indigo-500/40"
        : "text-secondary border-line hover:bg-surface-2";
}
</script>

<template>
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button
            type="button"
            class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-lg text-muted hover:text-primary hover:bg-surface-2 border border-dashed border-line transition-colors"
            :title="t('vault.manage_folders')"
            :aria-label="t('vault.manage_folders')"
            v-on:click="emit('manage')"
        >
            <Settings class="w-4 h-4" :stroke-width="2" />
        </button>
        <button
            type="button"
            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border"
            :class="chipClass(modelValue === 'all')"
            v-on:click="select('all')"
        >
            {{ t("vault.folder_all") }}
            <span class="text-muted">({{ entries.length }})</span>
        </button>
        <button
            type="button"
            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border"
            :class="chipClass(modelValue === 'none')"
            v-on:click="select('none')"
        >
            {{ t("vault.folder_none") }}
            <span class="text-muted">({{ uncategorizedCount }})</span>
        </button>
        <button
            v-for="folder in folders"
            :key="folder.id"
            type="button"
            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border"
            :class="chipClass(modelValue === folder.id)"
            v-on:click="select(folder.id)"
        >
            <FolderOpen v-if="modelValue === folder.id" class="w-3.5 h-3.5" :stroke-width="2" />
            <Folder v-else class="w-3.5 h-3.5" :stroke-width="2" />
            {{ folder.name }}
            <span class="text-muted">({{ countInFolder(folder.id) }})</span>
        </button>
    </div>
</template>
