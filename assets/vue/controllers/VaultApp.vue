<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { Plus, Lock } from "lucide-vue-next";
import AppButton from "@/components/AppButton.vue";
import AppModal from "@/components/AppModal.vue";
import AppNoData from "@/components/AppNoData.vue";
import VaultUnlockScreen from "@/vault/components/VaultUnlockScreen.vue";
import VaultFolderBar from "@/vault/components/VaultFolderBar.vue";
import VaultTypePickerModal from "@/vault/components/VaultTypePickerModal.vue";
import VaultFolderManagerModal from "@/vault/components/VaultFolderManagerModal.vue";
import VaultEntryFormModal from "@/vault/components/VaultEntryFormModal.vue";
import VaultEntryList from "@/vault/components/VaultEntryList.vue";
import { useVaultEntries } from "@/vault/composables/useVaultEntries.js";
import { useVaultFolders } from "@/vault/composables/useVaultFolders.js";
import { isUnlocked, lock } from "@/composables/useVaultCrypto.js";

const props = defineProps({
    salt: { type: String, required: true },
    listPath: { type: String, required: true },
    createPath: { type: String, required: true },
    updatePath: { type: String, required: true },
    deletePath: { type: String, required: true },
    foldersListPath: { type: String, required: true },
    foldersCreatePath: { type: String, required: true },
    foldersUpdatePath: { type: String, required: true },
    foldersDeletePath: { type: String, required: true },
});

const { t } = useI18n();

const unlocked = ref(isUnlocked());

const { entries, loading, loadEntries, createEntry, updateEntry, deleteEntry } =
    useVaultEntries(props.listPath, props.createPath, props.updatePath, props.deletePath);

const { folders, loadFolders, createFolder, updateFolder, deleteFolder } =
    useVaultFolders(props.foldersListPath, props.foldersCreatePath, props.foldersUpdatePath, props.foldersDeletePath);

// --- Filtering ---
const selectedFolderId = ref("all");

const filteredEntries = computed(() => {
    if (selectedFolderId.value === "all") return entries.value;
    if (selectedFolderId.value === "none") return entries.value.filter((e) => !e.folderId);
    return entries.value.filter((e) => e.folderId === selectedFolderId.value);
});

async function loadAll() {
    await Promise.all([loadEntries(), loadFolders()]);
}

onMounted(async () => {
    if (unlocked.value) await loadAll();
});

async function handleUnlocked() {
    unlocked.value = true;
    await loadAll();
}

function handleLock() {
    lock();
    unlocked.value = false;
}

// --- Entry form modal ---
const showFormModal = ref(false);
const editingEntry = ref(null);
const pendingRecordType = ref("login");

const initialFolderId = computed(() =>
    typeof selectedFolderId.value === "number" ? selectedFolderId.value : null,
);

async function handleFormSubmit(form) {
    return editingEntry.value ? updateEntry(editingEntry.value.id, form) : createEntry(form);
}

function openEdit(entry) {
    editingEntry.value = entry;
    showFormModal.value = true;
}

function closeForm() {
    showFormModal.value = false;
    editingEntry.value = null;
}

// --- Type picker ---
const showTypePicker = ref(false);

function openTypePicker() {
    showTypePicker.value = true;
}

function pickTypeAndCreate(typeKey) {
    showTypePicker.value = false;
    editingEntry.value = null;
    pendingRecordType.value = typeKey;
    showFormModal.value = true;
}

// --- Favorite toggle ---
function toggleFavorite(entry) {
    updateEntry(entry.id, { ...entry, isFavorite: !entry.isFavorite });
}

// --- Delete confirmation ---
const confirmDeleteId = ref(null);

function askDelete(entry) {
    confirmDeleteId.value = entry.id;
}

function cancelDelete() {
    confirmDeleteId.value = null;
}

async function confirmDelete() {
    if (!confirmDeleteId.value) return;
    await deleteEntry(confirmDeleteId.value);
    confirmDeleteId.value = null;
}

// --- Folder manager ---
const showFolderManager = ref(false);

async function handleFolderDelete(id) {
    const ok = await deleteFolder(id);
    if (!ok) return;
    entries.value = entries.value.map((e) => (e.folderId === id ? { ...e, folderId: null } : e));
    if (selectedFolderId.value === id) selectedFolderId.value = "all";
}
</script>

<template>
    <div>
        <VaultUnlockScreen v-if="!unlocked" :salt="salt" v-on:unlocked="handleUnlocked" />

        <div v-else class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
                <AppButton variant="secondary" class="w-full sm:w-auto" v-on:click="handleLock">
                    <Lock class="w-4 h-4 mr-1.5" :stroke-width="2" />
                    {{ t("vault.lock_button") }}
                </AppButton>
                <AppButton class="w-full sm:w-auto" v-on:click="openTypePicker">
                    <Plus class="w-4 h-4 mr-1.5" :stroke-width="2" />
                    {{ t("vault.new_entry") }}
                </AppButton>
            </div>

            <VaultFolderBar
                v-model="selectedFolderId"
                :folders="folders"
                :entries="entries"
                v-on:manage="showFolderManager = true"
            />

            <div v-if="loading" class="flex items-center justify-center py-16">
                <div class="w-8 h-8 rounded-full border-2 border-indigo-500 border-t-transparent animate-spin" />
            </div>

            <AppNoData v-else-if="filteredEntries.length === 0" :message="t('vault.no_entries')" />

            <VaultEntryList
                v-else
                :entries="filteredEntries"
                v-on:edit="openEdit"
                v-on:delete="askDelete"
                v-on:toggle-favorite="toggleFavorite"
            />

            <VaultTypePickerModal
                :show="showTypePicker"
                v-on:close="showTypePicker = false"
                v-on:pick="pickTypeAndCreate"
            />

            <VaultFolderManagerModal
                :show="showFolderManager"
                :folders="folders"
                :on-create="createFolder"
                :on-update="updateFolder"
                :on-delete="handleFolderDelete"
                v-on:close="showFolderManager = false"
            />

            <AppModal :show="confirmDeleteId !== null" max-width="sm" v-on:close="cancelDelete">
                <p class="text-sm text-primary">{{ t("vault.delete_confirm") }}</p>
                <div class="flex justify-end gap-2">
                    <AppButton variant="ghost" size="md" v-on:click="cancelDelete">{{ t("common.cancel") }}</AppButton>
                    <AppButton variant="danger" size="md" v-on:click="confirmDelete">{{ t("common.delete") }}</AppButton>
                </div>
            </AppModal>

            <VaultEntryFormModal
                :show="showFormModal"
                :entry="editingEntry"
                :record-type="pendingRecordType"
                :folders="folders"
                :initial-folder-id="initialFolderId"
                :on-submit="handleFormSubmit"
                v-on:close="closeForm"
            />
        </div>
    </div>
</template>
