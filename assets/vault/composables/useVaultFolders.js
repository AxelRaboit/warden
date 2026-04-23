import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { toast } from "vue-sonner";
import { useApiRequest } from "@/composables/useApiRequest.js";
import { resolvePath } from "@/vault/utils/resolvePath.js";

export function useVaultFolders(
    listPath,
    createPath,
    updatePathTemplate,
    deletePathTemplate,
) {
    const { t } = useI18n();
    const { request } = useApiRequest();

    const folders = ref([]);

    async function loadFolders() {
        const data = await request(listPath, null, "GET");
        if (!data) return;
        folders.value = data.items ?? [];
    }

    async function createFolder(name, color = null) {
        const data = await request(createPath, { name, color });
        if (!data) return null;
        if (!data.success) return data.errors ?? {};
        folders.value = [...folders.value, data.folder];
        toast.success(t("vault.folder_created"));
        return data.folder;
    }

    async function updateFolder(id, name, color = null) {
        const data = await request(
            resolvePath(updatePathTemplate, id),
            { name, color },
            "PATCH",
        );
        if (!data) return null;
        if (!data.success) return data.errors ?? {};
        folders.value = folders.value.map((f) =>
            f.id === id ? data.folder : f,
        );
        toast.success(t("vault.folder_updated"));
        return data.folder;
    }

    async function deleteFolder(id) {
        const data = await request(
            resolvePath(deletePathTemplate, id),
            null,
            "DELETE",
        );
        if (!data) return false;
        folders.value = folders.value.filter((f) => f.id !== id);
        toast.success(t("vault.folder_deleted"));
        return true;
    }

    return { folders, loadFolders, createFolder, updateFolder, deleteFolder };
}
