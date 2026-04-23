import { describe, it, expect } from "vitest";
import {
    RECORD_TYPES,
    RECORD_TYPE_KEYS,
    getRecordType,
    buildDefaultFields,
} from "@/vault/recordTypes.js";

describe("RECORD_TYPES", () => {
    it("exposes the expected type keys", () => {
        expect(RECORD_TYPE_KEYS).toContain("login");
        expect(RECORD_TYPE_KEYS).toContain("payment_card");
        expect(RECORD_TYPE_KEYS).toContain("ssh_key");
        expect(RECORD_TYPE_KEYS).toContain("secure_note");
    });

    it("each type has icon, color, and defaultFields", () => {
        for (const key of RECORD_TYPE_KEYS) {
            const type = RECORD_TYPES[key];
            expect(type.icon).toBeDefined();
            expect(typeof type.color).toBe("string");
            expect(Array.isArray(type.defaultFields)).toBe(true);
        }
    });
});

describe("getRecordType", () => {
    it("returns the matching type definition", () => {
        expect(getRecordType("payment_card")).toBe(RECORD_TYPES.payment_card);
    });

    it("falls back to login for unknown keys", () => {
        expect(getRecordType("unknown")).toBe(RECORD_TYPES.login);
    });

    it("falls back to login for null/undefined", () => {
        expect(getRecordType(null)).toBe(RECORD_TYPES.login);
        expect(getRecordType(undefined)).toBe(RECORD_TYPES.login);
    });
});

describe("buildDefaultFields", () => {
    const t = (key) => key;

    it("returns an empty array for login type", () => {
        expect(buildDefaultFields("login", t)).toEqual([]);
    });

    it("returns an empty array for secure_note type", () => {
        expect(buildDefaultFields("secure_note", t)).toEqual([]);
    });

    it("returns fields with id, type, label, value and _editing for payment_card", () => {
        const fields = buildDefaultFields("payment_card", t);

        expect(fields.length).toBeGreaterThan(0);
        for (const field of fields) {
            expect(field).toHaveProperty("id");
            expect(field).toHaveProperty("type");
            expect(field).toHaveProperty("label");
            expect(field).toHaveProperty("value", "");
            expect(field).toHaveProperty("_editing", true);
        }
    });

    it("maps labelKey through the translation function", () => {
        const fields = buildDefaultFields(
            "payment_card",
            (key) => `TRANS:${key}`,
        );

        expect(fields[0].label).toMatch(/^TRANS:vault\.field_labels\./);
    });

    it("generates unique ids for each field", () => {
        const fields = buildDefaultFields("contact", t);
        const ids = fields.map((f) => f.id);

        expect(new Set(ids).size).toBe(ids.length);
    });

    it("generates unique ids across multiple calls", () => {
        const a = buildDefaultFields("contact", t);
        const b = buildDefaultFields("contact", t);

        expect(a[0].id).not.toBe(b[0].id);
    });

    it("falls back to login defaults for unknown type", () => {
        expect(buildDefaultFields("unknown_type", t)).toEqual([]);
    });
});
