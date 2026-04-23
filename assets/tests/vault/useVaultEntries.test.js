import { describe, it, expect, vi, beforeEach } from "vitest";

const mockRequest = vi.fn();
const mockEncrypt = vi.fn();
const mockDecrypt = vi.fn();

vi.mock("vue-i18n", () => ({
    useI18n: () => ({ t: (key) => key }),
}));

vi.mock("vue-sonner", () => ({
    toast: { success: vi.fn(), error: vi.fn() },
}));

vi.mock("@/composables/useApiRequest.js", () => ({
    useApiRequest: () => ({ loading: { value: false }, request: mockRequest }),
}));

vi.mock("@/composables/useVaultCrypto.js", () => ({
    encrypt: (payload) => mockEncrypt(payload),
    decrypt: (data, iv) => mockDecrypt(data, iv),
}));

import { useVaultEntries } from "@/vault/composables/useVaultEntries.js";

const LIST_PATH = "/vault/entries";
const CREATE_PATH = "/vault/entries";
const UPDATE_PATH = "/vault/entries/__id__";
const DELETE_PATH = "/vault/entries/__id__";

function setup() {
    return useVaultEntries(LIST_PATH, CREATE_PATH, UPDATE_PATH, DELETE_PATH);
}

const CIPHER = { encryptedData: "ENC", iv: "IV" };

describe("useVaultEntries", () => {
    beforeEach(() => {
        mockRequest.mockReset();
        mockEncrypt.mockReset();
        mockDecrypt.mockReset();
    });

    describe("loadEntries", () => {
        it("decrypts each entry and merges the plain fields", async () => {
            mockRequest.mockResolvedValue({
                items: [
                    { id: 1, title: "GitHub", encryptedData: "E", iv: "I" },
                ],
                total: 1,
                page: 1,
                totalPages: 1,
            });
            mockDecrypt.mockResolvedValue(
                JSON.stringify({
                    username: "john",
                    password: "p",
                    notes: null,
                    fields: [],
                }),
            );

            const { entries, loadEntries } = setup();
            await loadEntries();

            expect(entries.value).toHaveLength(1);
            expect(entries.value[0]).toMatchObject({
                id: 1,
                title: "GitHub",
                username: "john",
                password: "p",
            });
        });

        it("flags entries as _decryptError when decryption throws", async () => {
            mockRequest.mockResolvedValue({
                items: [{ id: 1, encryptedData: "E", iv: "I" }],
                total: 1,
                page: 1,
                totalPages: 1,
            });
            mockDecrypt.mockRejectedValue(new Error("bad key"));

            const { entries, loadEntries } = setup();
            await loadEntries();

            expect(entries.value[0]._decryptError).toBe(true);
            expect(entries.value[0].password).toBeNull();
        });
    });

    describe("createEntry", () => {
        it("encrypts sensitive fields before sending", async () => {
            mockEncrypt.mockResolvedValue(CIPHER);
            mockRequest.mockResolvedValueOnce({ success: true });
            mockRequest.mockResolvedValueOnce({
                items: [],
                total: 0,
                page: 1,
                totalPages: 1,
            });

            const { createEntry } = setup();
            const ok = await createEntry({
                title: "GitHub",
                username: "john",
                password: "secret",
                notes: null,
                url: "https://github.com",
                recordType: "login",
            });

            expect(ok).toBe(true);
            expect(mockEncrypt).toHaveBeenCalledWith(
                JSON.stringify({
                    username: "john",
                    password: "secret",
                    notes: null,
                    fields: [],
                }),
            );
            expect(mockRequest).toHaveBeenNthCalledWith(
                1,
                CREATE_PATH,
                expect.objectContaining({
                    title: "GitHub",
                    recordType: "login",
                    url: "https://github.com",
                    encryptedData: "ENC",
                    iv: "IV",
                }),
            );
        });

        it("returns server errors when validation fails", async () => {
            mockEncrypt.mockResolvedValue(CIPHER);
            mockRequest.mockResolvedValue({
                success: false,
                errors: { title: "required" },
            });

            const { createEntry } = setup();
            const result = await createEntry({ title: "" });

            expect(result).toEqual({ title: "required" });
        });
    });

    describe("updateEntry", () => {
        it("calls the templated update path with the id", async () => {
            mockEncrypt.mockResolvedValue(CIPHER);
            mockRequest.mockResolvedValueOnce({ success: true });
            mockRequest.mockResolvedValueOnce({
                items: [],
                total: 0,
                page: 1,
                totalPages: 1,
            });

            const { updateEntry } = setup();
            await updateEntry(42, { title: "x", recordType: "login" });

            expect(mockRequest).toHaveBeenNthCalledWith(
                1,
                "/vault/entries/42",
                expect.any(Object),
                "PATCH",
            );
        });
    });

    describe("deleteEntry", () => {
        it("calls the templated delete path and removes the entry", async () => {
            mockRequest.mockResolvedValueOnce({
                items: [{ id: 1, encryptedData: "E", iv: "I" }],
                total: 1,
                page: 1,
                totalPages: 1,
            });
            mockDecrypt.mockResolvedValue(
                JSON.stringify({
                    username: null,
                    password: null,
                    notes: null,
                    fields: [],
                }),
            );

            const { entries, loadEntries, deleteEntry } = setup();
            await loadEntries();
            expect(entries.value).toHaveLength(1);

            mockRequest.mockResolvedValueOnce({ success: true });
            const ok = await deleteEntry(1);

            expect(ok).toBe(true);
            expect(mockRequest).toHaveBeenLastCalledWith(
                "/vault/entries/1",
                null,
                "DELETE",
            );
            expect(entries.value).toHaveLength(0);
        });
    });
});
