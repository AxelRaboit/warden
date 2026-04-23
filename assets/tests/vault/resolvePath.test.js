import { describe, it, expect } from "vitest";
import { resolvePath } from "@/vault/utils/resolvePath.js";

describe("resolvePath", () => {
    it("replaces __id__ with the given id", () => {
        expect(resolvePath("/vault/entries/__id__", 42)).toBe(
            "/vault/entries/42",
        );
    });

    it("accepts string ids", () => {
        expect(resolvePath("/vault/entries/__id__", "abc")).toBe(
            "/vault/entries/abc",
        );
    });

    it("only replaces the first occurrence", () => {
        expect(resolvePath("/a/__id__/b/__id__", 1)).toBe("/a/1/b/__id__");
    });

    it("returns the template unchanged when no placeholder exists", () => {
        expect(resolvePath("/vault/entries", 1)).toBe("/vault/entries");
    });
});
