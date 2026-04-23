<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { toast } from "vue-sonner";
import { Plus, Eye, EyeOff, Check, X, Pencil, Copy, ExternalLink } from "lucide-vue-next";
import AppButton from "@/components/AppButton.vue";

const FIELD_TYPES = ["text", "password", "email", "url", "note"];

const props = defineProps({
    modelValue: { type: Array, required: true },
});

const emit = defineEmits(["update:modelValue"]);

const { t } = useI18n();

const newType = ref("text");
const revealedIds = ref(new Set());

function updateFields(fields) {
    emit("update:modelValue", fields);
}

function addField() {
    updateFields([
        ...props.modelValue,
        {
            id: crypto.randomUUID(),
            type: newType.value,
            label: "",
            value: "",
            _editing: true,
        },
    ]);
}

function removeField(id) {
    revealedIds.value.delete(id);
    updateFields(props.modelValue.filter((f) => f.id !== id));
}

function patchField(id, patch) {
    updateFields(props.modelValue.map((f) => (f.id === id ? { ...f, ...patch } : f)));
}

function validateField(field) {
    if (!field.label.trim() && !field.value.trim()) return;
    patchField(field.id, { _editing: false });
}

function editField(field) {
    patchField(field.id, { _editing: true });
}

function toggleRevealed(id) {
    if (revealedIds.value.has(id)) {
        revealedIds.value.delete(id);
    } else {
        revealedIds.value.add(id);
    }
    revealedIds.value = new Set(revealedIds.value);
}

function inputTypeFor(field) {
    if (field.type === "password" && !revealedIds.value.has(field.id)) return "password";
    if (field.type === "email") return "email";
    if (field.type === "url") return "url";
    return "text";
}

async function copyValue(value) {
    try {
        await navigator.clipboard.writeText(value);
        toast.success(t("vault.copied"));
    } catch {
        toast.error(t("common.error"));
    }
}
</script>

<template>
    <div class="space-y-2 pt-2 border-t border-line">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <label class="block text-xs text-secondary uppercase tracking-wide">
                {{ t("vault.form.custom_fields") }}
            </label>
            <div class="flex items-center gap-2">
                <select
                    v-model="newType"
                    class="flex-1 sm:flex-none rounded-lg bg-surface-2 border border-line px-2 py-1 text-xs text-primary focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >
                    <option v-for="type in FIELD_TYPES" :key="type" :value="type">
                        {{ t(`vault.field_types.${type}`) }}
                    </option>
                </select>
                <AppButton type="button" variant="secondary" size="sm" v-on:click="addField">
                    <Plus class="w-3.5 h-3.5 mr-1" :stroke-width="2" />
                    {{ t("vault.form.add_field") }}
                </AppButton>
            </div>
        </div>

        <div v-if="modelValue.length" class="space-y-2">
            <template v-for="field in modelValue" :key="field.id">
                <!-- Edit mode -->
                <div
                    v-if="field._editing"
                    class="bg-surface-2/50 border border-line rounded-lg p-2 space-y-2"
                >
                    <div class="flex items-center gap-2">
                        <select
                            :value="field.type"
                            class="flex-1 sm:flex-none rounded-md bg-surface-2 border border-line px-2 py-1.5 text-xs text-primary focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            v-on:change="patchField(field.id, { type: $event.target.value })"
                        >
                            <option v-for="type in FIELD_TYPES" :key="type" :value="type">
                                {{ t(`vault.field_types.${type}`) }}
                            </option>
                        </select>
                        <input
                            :value="field.label"
                            type="text"
                            :placeholder="t('vault.form.field_label')"
                            class="flex-1 rounded-md bg-surface-2 border border-line px-2 py-1.5 text-xs text-primary placeholder-muted focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            v-on:input="patchField(field.id, { label: $event.target.value })"
                        >
                    </div>
                    <textarea
                        v-if="field.type === 'note'"
                        :value="field.value"
                        :rows="2"
                        :placeholder="t('vault.form.field_value')"
                        class="w-full rounded-md bg-surface-2 border border-line px-2 py-1.5 text-sm text-primary placeholder-muted focus:outline-none focus:ring-2 focus:ring-indigo-500/40 resize-y"
                        v-on:input="patchField(field.id, { value: $event.target.value })"
                    />
                    <div v-else class="relative">
                        <input
                            :value="field.value"
                            :type="inputTypeFor(field)"
                            :placeholder="t('vault.form.field_value')"
                            class="w-full rounded-md bg-surface-2 border border-line px-2 py-1.5 text-sm text-primary placeholder-muted focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            :class="field.type === 'password' ? 'pr-8' : ''"
                            v-on:input="patchField(field.id, { value: $event.target.value })"
                        >
                        <button
                            v-if="field.type === 'password'"
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-2 text-muted hover:text-primary transition-colors"
                            v-on:click="toggleRevealed(field.id)"
                        >
                            <Eye v-if="!revealedIds.has(field.id)" class="w-4 h-4" :stroke-width="2" />
                            <EyeOff v-else class="w-4 h-4" :stroke-width="2" />
                        </button>
                    </div>
                    <div class="flex items-center justify-end gap-1 pt-1 border-t border-line">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs text-muted hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                            v-on:click="removeField(field.id)"
                        >
                            <X class="w-3.5 h-3.5" :stroke-width="2" />
                            {{ t("vault.form.remove_field") }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs text-emerald-400 hover:bg-emerald-500/10 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            :disabled="!field.label.trim() && !field.value.trim()"
                            v-on:click="validateField(field)"
                        >
                            <Check class="w-3.5 h-3.5" :stroke-width="2" />
                            {{ t("vault.form.validate_field") }}
                        </button>
                    </div>
                </div>

                <!-- Display mode -->
                <div
                    v-else
                    class="group flex flex-col sm:flex-row sm:items-start gap-2 bg-surface-2/30 border border-line rounded-lg p-3 hover:border-indigo-500/30 transition-colors"
                >
                    <div class="flex-1 min-w-0 space-y-0.5">
                        <p class="text-xs text-muted uppercase tracking-wide">
                            {{ field.label || t(`vault.field_types.${field.type}`) }}
                        </p>
                        <p v-if="field.type === 'password'" class="text-sm font-mono text-primary break-all">
                            <span v-if="revealedIds.has(field.id)">{{ field.value }}</span>
                            <span v-else>••••••••••••</span>
                        </p>
                        <p v-else-if="field.type === 'note'" class="text-sm text-primary whitespace-pre-wrap break-words">{{ field.value }}</p>
                        <a
                            v-else-if="field.type === 'url' && field.value"
                            :href="field.value"
                            target="_blank"
                            rel="noopener"
                            class="text-sm text-indigo-400 hover:text-indigo-300 break-all inline-flex items-center gap-1"
                        >
                            {{ field.value }}
                            <ExternalLink class="w-3 h-3 shrink-0" :stroke-width="2" />
                        </a>
                        <p v-else class="text-sm text-primary break-all">{{ field.value }}</p>
                    </div>
                    <div class="flex items-center justify-end gap-1 shrink-0 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                        <button
                            v-if="field.type === 'password' && field.value"
                            type="button"
                            class="p-1.5 rounded-md text-muted hover:text-primary hover:bg-surface-2 transition-colors"
                            :title="revealedIds.has(field.id) ? t('vault.form.hide') : t('vault.form.reveal')"
                            v-on:click="toggleRevealed(field.id)"
                        >
                            <Eye v-if="!revealedIds.has(field.id)" class="w-4 h-4" :stroke-width="2" />
                            <EyeOff v-else class="w-4 h-4" :stroke-width="2" />
                        </button>
                        <button
                            v-if="field.value && field.type !== 'note'"
                            type="button"
                            class="p-1.5 rounded-md text-muted hover:text-primary hover:bg-surface-2 transition-colors"
                            :title="t('vault.form.copy')"
                            v-on:click="copyValue(field.value)"
                        >
                            <Copy class="w-4 h-4" :stroke-width="2" />
                        </button>
                        <button
                            type="button"
                            class="p-1.5 rounded-md text-muted hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors"
                            :title="t('common.edit')"
                            v-on:click="editField(field)"
                        >
                            <Pencil class="w-4 h-4" :stroke-width="2" />
                        </button>
                        <button
                            type="button"
                            class="p-1.5 rounded-md text-muted hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                            :title="t('vault.form.remove_field')"
                            v-on:click="removeField(field.id)"
                        >
                            <X class="w-4 h-4" :stroke-width="2" />
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
