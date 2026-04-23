<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { Eye, EyeOff, RefreshCw } from "lucide-vue-next";
import AppButton from "@/components/AppButton.vue";
import AppInput from "@/components/AppInput.vue";
import AppTextarea from "@/components/AppTextarea.vue";
import AppModal from "@/components/AppModal.vue";
import PasswordStrength from "@/components/PasswordStrength.vue";
import VaultCustomFieldsEditor from "@/vault/components/VaultCustomFieldsEditor.vue";
import { getRecordType, buildDefaultFields } from "@/vault/recordTypes.js";
import { useForm } from "@/composables/useForm.js";
import { required } from "@/utils/validators.js";

const props = defineProps({
    show: { type: Boolean, required: true },
    entry: { type: Object, default: null },
    recordType: { type: String, default: "login" },
    folders: { type: Array, required: true },
    initialFolderId: { type: [Number, null], default: null },
    onSubmit: { type: Function, required: true },
});

const emit = defineEmits(["close"]);

const { t } = useI18n();

const formTitle = ref("");
const formUrl = ref("");
const formFolderId = ref(null);
const formUsername = ref("");
const formPassword = ref("");
const formNotes = ref("");
const formFavorite = ref(false);
const formFields = ref([]);
const showPassword = ref(false);
const submitting = ref(false);

const { errors, validate, setErrors, clearErrors } = useForm();

const isEditing = computed(() => props.entry !== null);
const resolvedRecordType = computed(() => props.entry?.recordType ?? props.recordType);

watch(
    () => props.show,
    (show) => {
        if (!show) return;
        if (props.entry) {
            formTitle.value = props.entry.title;
            formUrl.value = props.entry.url ?? "";
            formFolderId.value = props.entry.folderId ?? null;
            formUsername.value = props.entry.username ?? "";
            formPassword.value = props.entry.password ?? "";
            formNotes.value = props.entry.notes ?? "";
            formFavorite.value = props.entry.isFavorite;
            formFields.value = (props.entry.fields ?? []).map((f) => ({
                id: f.id ?? crypto.randomUUID(),
                type: f.type,
                label: f.label ?? "",
                value: f.value ?? "",
                _editing: false,
            }));
        } else {
            formTitle.value = "";
            formUrl.value = "";
            formFolderId.value = props.initialFolderId;
            formUsername.value = "";
            formPassword.value = "";
            formNotes.value = "";
            formFavorite.value = false;
            formFields.value = buildDefaultFields(props.recordType, t);
        }
        showPassword.value = false;
        clearErrors();
    },
);

function generatePassword() {
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
    const array = new Uint8Array(20);
    crypto.getRandomValues(array);
    formPassword.value = Array.from(array, (b) => charset[b % charset.length]).join("");
    showPassword.value = true;
}

async function handleSubmit() {
    const valid = validate({
        title: () => required(t("vault.errors.title_required"))(formTitle.value),
    });
    if (!valid) return;

    submitting.value = true;
    try {
        const form = {
            title: formTitle.value,
            recordType: resolvedRecordType.value,
            url: formUrl.value,
            folderId: formFolderId.value,
            username: formUsername.value,
            password: formPassword.value,
            notes: formNotes.value,
            isFavorite: formFavorite.value,
            fields: formFields.value
                .filter((f) => f.label.trim() !== "" || f.value.trim() !== "")
                .map((f) => ({ id: f.id, type: f.type, label: f.label, value: f.value })),
        };

        const result = await props.onSubmit(form);

        if (result === true) {
            emit("close");
        } else if (result && typeof result === "object") {
            setErrors(result);
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <AppModal :show="show" max-width="md" v-on:close="emit('close')">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-600/15 flex items-center justify-center shrink-0">
                <component :is="getRecordType(resolvedRecordType).icon" class="w-5 h-5 text-indigo-400" :stroke-width="2" />
            </div>
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-primary">
                    {{ isEditing ? t("vault.edit_entry") : t("vault.new_entry") }}
                </h2>
                <p class="text-xs text-muted">{{ t(`vault.types.${resolvedRecordType}`) }}</p>
            </div>
        </div>

        <form class="space-y-4 mt-4" v-on:submit.prevent="handleSubmit">
            <AppInput
                v-model="formTitle"
                :label="t('vault.form.title')"
                :error="errors.title"
                required
            />
            <AppInput
                v-model="formUrl"
                type="url"
                :label="t('vault.form.url')"
                placeholder="https://example.com"
            />
            <div class="flex flex-col gap-1.5">
                <label class="block text-xs text-secondary uppercase tracking-wide">{{ t("vault.form.folder") }}</label>
                <select
                    v-model="formFolderId"
                    class="w-full rounded-lg bg-surface-2 border border-line px-3 py-2 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >
                    <option :value="null">{{ t("vault.folder_none") }}</option>
                    <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
                </select>
            </div>
            <AppInput
                v-model="formUsername"
                :label="t('vault.form.username')"
                :placeholder="t('vault.form.username_placeholder')"
            />

            <div class="space-y-1.5">
                <label class="block text-xs text-secondary uppercase tracking-wide">
                    {{ t("vault.form.password") }}
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input
                            v-model="formPassword"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full rounded-lg bg-surface-2 border border-line px-3 py-2 text-sm text-primary placeholder-muted focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 pr-9"
                            :placeholder="t('vault.form.password_placeholder')"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted hover:text-primary transition-colors"
                            v-on:click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="w-4 h-4" :stroke-width="2" />
                            <EyeOff v-else class="w-4 h-4" :stroke-width="2" />
                        </button>
                    </div>
                    <AppButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        :title="t('vault.form.generate')"
                        v-on:click="generatePassword"
                    >
                        <RefreshCw class="w-4 h-4" :stroke-width="2" />
                    </AppButton>
                </div>
                <PasswordStrength :password="formPassword" />
            </div>

            <AppTextarea
                v-model="formNotes"
                :label="t('vault.form.notes')"
                :placeholder="t('vault.form.notes_placeholder')"
                :rows="3"
            />

            <VaultCustomFieldsEditor v-model="formFields" />

            <label class="flex items-center gap-2.5 cursor-pointer">
                <input
                    v-model="formFavorite"
                    type="checkbox"
                    class="w-4 h-4 rounded border-line bg-surface-2 text-indigo-500 focus:ring-indigo-500/40"
                >
                <span class="text-sm text-secondary">{{ t("vault.form.favorite") }}</span>
            </label>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-2">
                <AppButton type="button" variant="secondary" class="w-full sm:w-auto" v-on:click="emit('close')">
                    {{ t("common.cancel") }}
                </AppButton>
                <AppButton type="submit" class="w-full sm:w-auto" :loading="submitting">
                    {{ t("common.save") }}
                </AppButton>
            </div>
        </form>
    </AppModal>
</template>
