import { describe, it, expect } from "vitest";
import { required, email, compose } from "@/utils/validators";

describe("required", () => {
    it("returns null when value is a non-empty string", () => {
        expect(required("Required")(hello)).toBe(null);
    });

    it("returns the message when value is an empty string", () => {
        expect(required("Required")("")).toBe("Required");
    });

    it("returns the message when value is whitespace only", () => {
        expect(required("Required")("   ")).toBe("Required");
    });

    it("returns the message when value is null", () => {
        expect(required("Required")(null)).toBe("Required");
    });

    it("returns the message when value is undefined", () => {
        expect(required("Required")(undefined)).toBe("Required");
    });

    it("returns the message when value is an empty array", () => {
        expect(required("Required")([])).toBe("Required");
    });

    it("returns null for a non-empty array", () => {
        expect(required("Required")(["item"])).toBe(null);
    });
});

describe("email", () => {
    it("returns null for a valid email", () => {
        expect(email("Invalid")("user@example.com")).toBe(null);
    });

    it("returns null for an email with plus alias", () => {
        expect(email("Invalid")("user+tag@example.com")).toBe(null);
    });

    it("returns the message for a string without @", () => {
        expect(email("Invalid")("userexample.com")).toBe("Invalid");
    });

    it("returns the message for a string without domain", () => {
        expect(email("Invalid")("user@")).toBe("Invalid");
    });

    it("returns null (not an error) when value is empty — use required for that", () => {
        expect(email("Invalid")("")).toBe(null);
    });

    it("returns null when value is null", () => {
        expect(email("Invalid")(null)).toBe(null);
    });
});

describe("compose", () => {
    it("returns null when all validators pass", () => {
        const validator = compose(required("Required"), email("Invalid email"));

        expect(validator("user@example.com")).toBe(null);
    });

    it("returns the first error from the chain", () => {
        const validator = compose(required("Required"), email("Invalid email"));

        expect(validator("")).toBe("Required");
    });

    it("returns the second error when first passes but second fails", () => {
        const validator = compose(required("Required"), email("Invalid email"));

        expect(validator("not-an-email")).toBe("Invalid email");
    });

    it("stops at the first failing validator", () => {
        let called = false;
        const neverCalled = () => {
            called = true;
            return null;
        };

        compose(required("Required"), neverCalled)("");

        expect(called).toBe(false);
    });
});

// helper to avoid lint errors on undefined variable
const hello = "hello";
