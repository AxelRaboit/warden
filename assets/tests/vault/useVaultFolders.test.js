import { describe, it, expect, vi, beforeEach } from "vitest";

const mockRequest = vi.fn();

vi.mock("vue-i18n", () => ({
    useI18n: () => ({ t: (key) => key }),
}));

vi.mock("vue-sonner", () => ({
    toast: { success: vi.fn(), error: vi.fn() },
}));

vi.mock("@/composables/useApiRequest.js", () => ({
    useApiRequest: () => ({ loading: { value: false }, request: mockRequest }),
}));

import { useVaultFolders } from "@/vault/composables/useVaultFolders.js";

const LIST_PATH = "/vault/folders";
const CREATE_PATH = "/vault/folders";
const UPDATE_PATH = "/vault/folders/__id__";
const DELETE_PATH = "/vault/folders/__id__";

function setup() {
    return useVaultFolders(LIST_PATH, CREATE_PATH, UPDATE_PATH, DELETE_PATH);
}

describe("useVaultFolders", () => {
    beforeEach(() => {
        mockRequest.mockReset();
    });

    describe("loadFolders", () => {
        it("populates folders from the server response", async () => {
            mockRequest.mockResolvedValue({ items: [{ id: 1, name: "Work" }] });
            const { folders, loadFolders } = setup();

            await loadFolders();

            expect(folders.value).toEqual([{ id: 1, name: "Work" }]);
            expect(mockRequest).toHaveBeenCalledWith(LIST_PATH, null, "GET");
        });

        it("leaves folders empty when the request fails", async () => {
            mockRequest.mockResolvedValue(null);
            const { folders, loadFolders } = setup();

            await loadFolders();

            expect(folders.value).toEqual([]);
        });
    });

    describe("createFolder", () => {
        it("appends the created folder to the list", async () => {
            mockRequest.mockResolvedValue({
                success: true,
                folder: { id: 1, name: "Work" },
            });
            const { folders, createFolder } = setup();

            const result = await createFolder("Work");

            expect(result).toEqual({ id: 1, name: "Work" });
            expect(folders.value).toEqual([{ id: 1, name: "Work" }]);
        });

        it("returns server errors when validation fails", async () => {
            mockRequest.mockResolvedValue({
                success: false,
                errors: { name: "Required" },
            });
            const { folders, createFolder } = setup();

            const result = await createFolder("");

            expect(result).toEqual({ name: "Required" });
            expect(folders.value).toEqual([]);
        });

        it("sends name and color to the server", async () => {
            mockRequest.mockResolvedValue({ success: true, folder: { id: 1 } });
            const { createFolder } = setup();

            await createFolder("Work", "#ff0000");

            expect(mockRequest).toHaveBeenCalledWith(CREATE_PATH, {
                name: "Work",
                color: "#ff0000",
            });
        });
    });

    describe("updateFolder", () => {
        it("replaces the folder in the list by id", async () => {
            mockRequest.mockResolvedValueOnce({
                items: [
                    { id: 1, name: "Old" },
                    { id: 2, name: "Other" },
                ],
            });
            mockRequest.mockResolvedValueOnce({
                success: true,
                folder: { id: 1, name: "New" },
            });

            const { folders, loadFolders, updateFolder } = setup();
            await loadFolders();
            await updateFolder(1, "New");

            expect(folders.value).toEqual([
                { id: 1, name: "New" },
                { id: 2, name: "Other" },
            ]);
        });

        it("calls the templated update path with the id", async () => {
            mockRequest.mockResolvedValue({
                success: true,
                folder: { id: 1, name: "New" },
            });
            const { updateFolder } = setup();

            await updateFolder(1, "New");

            expect(mockRequest).toHaveBeenCalledWith(
                "/vault/folders/1",
                { name: "New", color: null },
                "PATCH",
            );
        });
    });

    describe("deleteFolder", () => {
        it("removes the folder from the list", async () => {
            mockRequest.mockResolvedValueOnce({
                items: [
                    { id: 1, name: "A" },
                    { id: 2, name: "B" },
                ],
            });
            mockRequest.mockResolvedValueOnce({ success: true });

            const { folders, loadFolders, deleteFolder } = setup();
            await loadFolders();
            const result = await deleteFolder(1);

            expect(result).toBe(true);
            expect(folders.value).toEqual([{ id: 2, name: "B" }]);
        });

        it("returns false when the request fails", async () => {
            mockRequest.mockResolvedValue(null);
            const { deleteFolder } = setup();

            expect(await deleteFolder(1)).toBe(false);
        });
    });
});
