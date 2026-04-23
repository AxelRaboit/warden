<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { Plus, Folder, Pencil, Trash2, Check, X } from "lucide-vue-next";
import AppModal from "@/components/AppModal.vue";
import AppButton from "@/components/AppButton.vue";
import AppInput from "@/components/AppInput.vue";
import AppIconButton from "@/components/AppIconButton.vue";

const props = defineProps({
    show: { type: Boolean, required: true },
    folders: { type: Array, required: true },
    onCreate: { type: Function, required: true },
    onUpdate: { type: Function, required: true },
    onDelete: { type: Function, required: true },
});

const emit = defineEmits(["close"]);

const { t } = useI18n();

const newName = ref("");
const createError = ref("");
const editingId = ref(null);
const editingName = ref("");
const confirmDeleteId = ref(null);

const folderToDelete = computed(() =>
    props.folders.find((f) => f.id === confirmDeleteId.value) ?? null,
);

watch(
    () => props.show,
    (show) => {
        if (show) {
            newName.value = "";
            createError.value = "";
            editingId.value = null;
            editingName.value = "";
            confirmDeleteId.value = null;
        }
    },
);

async function submitCreate() {
    if (!newName.value.trim()) {
        createError.value = t("vault.errors.folder_name_required");
        return;
    }
    const result = await props.onCreate(newName.value.trim());
    if (result && typeof result === "object" && !result.id) {
        createError.value = result.name ?? t("common.error");
        return;
    }
    newName.value = "";
    createError.value = "";
}

function startEdit(folder) {
    editingId.value = folder.id;
    editingName.value = folder.name;
}

async function submitEdit() {
    if (!editingId.value || !editingName.value.trim()) return;
    await props.onUpdate(editingId.value, editingName.value.trim());
    editingId.value = null;
    editingName.value = "";
}

async function submitDelete() {
    if (!confirmDeleteId.value) return;
    await props.onDelete(confirmDeleteId.value);
    confirmDeleteId.value = null;
}
</script>

<template>
    <AppModal :show="show" max-width="md" v-on:close="emit('close')">
        <h2 class="text-base font-semibold text-primary">{{ t("vault.manage_folders") }}</h2>

        <form class="flex flex-col sm:flex-row sm:items-start gap-2" v-on:submit.prevent="submitCreate">
            <div class="flex-1">
                <AppInput
                    v-model="newName"
                    :placeholder="t('vault.form.folder_name_placeholder')"
                    :error="createError"
                />
            </div>
            <AppButton type="submit" variant="secondary" class="w-full sm:w-auto">
                <Plus class="w-4 h-4 mr-1.5" :stroke-width="2" />
                {{ t("vault.add_folder") }}
            </AppButton>
        </form>

        <div v-if="folders.length" class="space-y-2 max-h-80 overflow-y-auto">
            <div
                v-for="folder in folders"
                :key="folder.id"
                class="flex items-center gap-2 bg-surface-2/50 border border-line rounded-lg p-2"
            >
                <Folder class="w-4 h-4 text-muted shrink-0" :stroke-width="2" />

                <template v-if="editingId === folder.id">
                    <input
                        v-model="editingName"
                        type="text"
                        class="flex-1 min-w-0 rounded-md bg-surface-2 border border-line px-2 py-1.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        v-on:keyup.enter="submitEdit"
                        v-on:keyup.esc="editingId = null"
                    >
                    <AppIconButton color="default" v-on:click="editingId = null">
                        <X class="w-4 h-4" :stroke-width="2" />
                    </AppIconButton>
                    <AppIconButton color="emerald" v-on:click="submitEdit">
                        <Check class="w-4 h-4" :stroke-width="2" />
                    </AppIconButton>
                </template>
                <template v-else>
                    <span class="flex-1 min-w-0 text-sm text-primary truncate">{{ folder.name }}</span>
                    <AppIconButton color="indigo" v-on:click="startEdit(folder)">
                        <Pencil class="w-4 h-4" :stroke-width="2" />
                    </AppIconButton>
                    <AppIconButton color="rose" v-on:click="confirmDeleteId = folder.id">
                        <Trash2 class="w-4 h-4" :stroke-width="2" />
                    </AppIconButton>
                </template>
            </div>
        </div>
        <p v-else class="text-sm text-muted text-center py-4">{{ t("vault.no_folders") }}</p>

        <div class="flex justify-end">
            <AppButton variant="secondary" class="w-full sm:w-auto" v-on:click="emit('close')">{{ t("common.close") }}</AppButton>
        </div>
    </AppModal>

    <AppModal :show="confirmDeleteId !== null" max-width="sm" v-on:close="confirmDeleteId = null">
        <p class="text-sm text-primary">
            {{ t("vault.folder_delete_confirm_named", { name: folderToDelete?.name ?? "" }) }}
        </p>
        <div class="flex flex-col sm:flex-row sm:justify-end gap-2">
            <AppButton variant="ghost" class="w-full sm:w-auto" v-on:click="confirmDeleteId = null">
                {{ t("common.cancel") }}
            </AppButton>
            <AppButton variant="danger" class="w-full sm:w-auto" v-on:click="submitDelete">
                {{ t("common.delete") }}
            </AppButton>
        </div>
    </AppModal>
</template>
