import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { toast } from "vue-sonner";
import { useApiRequest } from "@/composables/useApiRequest.js";
import { encrypt, decrypt } from "@/composables/useVaultCrypto.js";
import { resolvePath } from "@/vault/utils/resolvePath.js";

async function encryptFormPayload(form) {
    const { encryptedData, iv } = await encrypt(
        JSON.stringify({
            username: form.username || null,
            password: form.password || null,
            notes: form.notes || null,
            fields: form.fields ?? [],
        }),
    );

    return {
        title: form.title,
        recordType: form.recordType ?? "login",
        url: form.url || null,
        folderId: form.folderId ?? null,
        encryptedData,
        iv,
        isFavorite: form.isFavorite ?? false,
    };
}

export function useVaultEntries(
    listPath,
    createPath,
    updatePathTemplate,
    deletePathTemplate,
) {
    const { t } = useI18n();
    const { loading, request } = useApiRequest();

    const entries = ref([]);
    const page = ref(1);
    const totalPages = ref(1);
    const total = ref(0);

    async function loadEntries() {
        const data = await request(
            `${listPath}?page=${page.value}`,
            null,
            "GET",
        );
        if (!data) return;

        const decrypted = await Promise.all(
            data.items.map(async (item) => {
                try {
                    const plain = JSON.parse(
                        await decrypt(item.encryptedData, item.iv),
                    );
                    return { ...item, ...plain };
                } catch {
                    return {
                        ...item,
                        username: null,
                        password: null,
                        notes: null,
                        fields: [],
                        _decryptError: true,
                    };
                }
            }),
        );

        entries.value = decrypted;
        total.value = data.total;
        totalPages.value = data.totalPages;
        page.value = data.page;
    }

    async function createEntry(form) {
        const payload = await encryptFormPayload(form);
        const data = await request(createPath, payload);

        if (!data) return false;
        if (!data.success) return data.errors ?? {};

        toast.success(t("vault.entry_created"));
        await loadEntries();
        return true;
    }

    async function updateEntry(id, form) {
        const payload = await encryptFormPayload(form);
        const data = await request(
            resolvePath(updatePathTemplate, id),
            payload,
            "PATCH",
        );

        if (!data) return false;
        if (!data.success) return data.errors ?? {};

        toast.success(t("vault.entry_updated"));
        await loadEntries();
        return true;
    }

    async function deleteEntry(id) {
        const data = await request(
            resolvePath(deletePathTemplate, id),
            null,
            "DELETE",
        );
        if (!data) return false;

        toast.success(t("vault.entry_deleted"));
        entries.value = entries.value.filter((e) => e.id !== id);
        total.value = Math.max(0, total.value - 1);
        return true;
    }

    return {
        entries,
        loading,
        page,
        totalPages,
        total,
        loadEntries,
        createEntry,
        updateEntry,
        deleteEntry,
    };
}
