<script setup>
import { useI18n } from "vue-i18n";
import { toast } from "vue-sonner";
import { Star, StarOff, Pencil, Trash2, Copy } from "lucide-vue-next";
import AppIconButton from "@/components/AppIconButton.vue";
import { getRecordType } from "@/vault/recordTypes.js";

const props = defineProps({
    entries: { type: Array, required: true },
});

const emit = defineEmits(["edit", "delete", "toggle-favorite"]);

const { t } = useI18n();

function typeIcon(entry) {
    return getRecordType(entry.recordType ?? "login").icon;
}

function shouldShowFavicon(entry) {
    return (entry.recordType ?? "login") === "login" && entry.url;
}

function faviconUrl(url) {
    try {
        const { origin } = new URL(url.startsWith("http") ? url : `https://${url}`);
        return `${origin}/favicon.ico`;
    } catch {
        return null;
    }
}

async function copyPassword(password) {
    try {
        await navigator.clipboard.writeText(password);
        toast.success(t("vault.copied"));
    } catch {
        toast.error(t("common.error"));
    }
}
</script>

<template>
    <div>
        <!-- Mobile cards -->
        <div class="sm:hidden space-y-3">
            <div
                v-for="entry in entries"
                :key="entry.id"
                class="bg-surface border border-line rounded-lg p-4 space-y-3"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-surface-2 border border-line flex items-center justify-center shrink-0 overflow-hidden">
                            <img
                                v-if="shouldShowFavicon(entry)"
                                :src="faviconUrl(entry.url)"
                                class="w-5 h-5 object-contain"
                                v-on:error="$event.target.style.display = 'none'"
                            >
                            <component :is="typeIcon(entry)" v-else class="w-4 h-4 text-muted" :stroke-width="2" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-primary truncate">{{ entry.title }}</p>
                            <p v-if="entry.username" class="text-xs text-muted truncate">{{ entry.username }}</p>
                            <p v-else-if="entry.url" class="text-xs text-muted truncate">{{ entry.url }}</p>
                        </div>
                    </div>
                    <button
                        class="shrink-0 text-muted hover:text-amber-400 transition-colors"
                        v-on:click="emit('toggle-favorite', entry)"
                    >
                        <Star v-if="entry.isFavorite" class="w-4 h-4 fill-amber-400 text-amber-400" :stroke-width="2" />
                        <StarOff v-else class="w-4 h-4" :stroke-width="2" />
                    </button>
                </div>

                <div v-if="entry._decryptError" class="text-xs text-rose-400 bg-rose-500/10 rounded-lg px-3 py-2">
                    {{ t("vault.decrypt_error") }}
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-line">
                    <button
                        v-if="!entry._decryptError && entry.password"
                        class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1"
                        v-on:click="copyPassword(entry.password)"
                    >
                        <Copy class="w-3 h-3" :stroke-width="2" />
                        {{ t("vault.copy_password") }}
                    </button>
                    <div v-else />
                    <div class="flex items-center gap-1">
                        <AppIconButton color="indigo" v-on:click="emit('edit', entry)">
                            <Pencil class="w-4 h-4" :stroke-width="2" />
                        </AppIconButton>
                        <AppIconButton color="rose" v-on:click="emit('delete', entry)">
                            <Trash2 class="w-4 h-4" :stroke-width="2" />
                        </AppIconButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop table -->
        <div v-if="entries.length" class="hidden sm:block bg-surface border border-line rounded-lg overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-2 border-b border-line">
                    <tr>
                        <th class="w-10 px-4 py-3" />
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">{{ t("vault.form.title") }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted hidden md:table-cell">{{ t("vault.form.username") }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted hidden lg:table-cell">{{ t("vault.form.url") }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">{{ t("common.edit") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    <tr v-for="entry in entries" :key="entry.id" class="hover:bg-surface-2/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="w-8 h-8 rounded-lg bg-surface-2 border border-line flex items-center justify-center overflow-hidden">
                                <img
                                    v-if="shouldShowFavicon(entry)"
                                    :src="faviconUrl(entry.url)"
                                    class="w-5 h-5 object-contain"
                                    v-on:error="$event.target.style.display = 'none'"
                                >
                                <component :is="typeIcon(entry)" v-else class="w-4 h-4 text-muted" :stroke-width="2" />
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <button
                                    class="shrink-0 text-muted hover:text-amber-400 transition-colors"
                                    v-on:click="emit('toggle-favorite', entry)"
                                >
                                    <Star v-if="entry.isFavorite" class="w-4 h-4 fill-amber-400 text-amber-400" :stroke-width="2" />
                                    <StarOff v-else class="w-4 h-4" :stroke-width="2" />
                                </button>
                                <span class="font-medium text-primary truncate">{{ entry.title }}</span>
                                <span v-if="entry._decryptError" class="text-xs text-rose-400">({{ t("vault.decrypt_error") }})</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-secondary hidden md:table-cell">
                            <span v-if="entry.username" class="truncate">{{ entry.username }}</span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td class="px-4 py-3 text-secondary font-mono text-xs hidden lg:table-cell">
                            <a
                                v-if="entry.url"
                                :href="entry.url"
                                target="_blank"
                                rel="noopener"
                                class="hover:text-indigo-400 truncate block max-w-xs"
                            >
                                {{ entry.url }}
                            </a>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <AppIconButton v-if="!entry._decryptError && entry.password" color="default" :title="t('vault.copy_password')" v-on:click="copyPassword(entry.password)">
                                    <Copy class="w-4 h-4" :stroke-width="2" />
                                </AppIconButton>
                                <AppIconButton color="indigo" v-on:click="emit('edit', entry)">
                                    <Pencil class="w-4 h-4" :stroke-width="2" />
                                </AppIconButton>
                                <AppIconButton color="rose" v-on:click="emit('delete', entry)">
                                    <Trash2 class="w-4 h-4" :stroke-width="2" />
                                </AppIconButton>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
