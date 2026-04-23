import { describe, it, expect, vi } from "vitest";
import { ref } from "vue";

const mockLocale = ref("en");

vi.mock("vue-i18n", () => ({
    useI18n: () => ({ locale: mockLocale }),
}));

import { useDateFormat } from "@/composables/useDateFormat";

// All tests run with TZ=UTC (set in vitest.config.js).
const ISO_DATE = "2024-06-15T10:30:00.000Z";

describe("useDateFormat", () => {
    describe("formatDate", () => {
        it("includes the day, year and time for en locale", () => {
            mockLocale.value = "en";
            const { formatDate } = useDateFormat();
            const result = formatDate(ISO_DATE);

            expect(result).toContain("2024");
            expect(result).toContain("15");
            expect(result).toContain("10");
            expect(result).toContain("30");
        });

        it("includes the day and year for fr locale", () => {
            mockLocale.value = "fr";
            const { formatDate } = useDateFormat();
            const result = formatDate(ISO_DATE);

            expect(result).toContain("2024");
            expect(result).toContain("15");
        });

        it("returns a non-empty string", () => {
            mockLocale.value = "en";
            const { formatDate } = useDateFormat();

            expect(formatDate(ISO_DATE)).not.toBe("");
        });
    });

    describe("formatDateShort", () => {
        it("includes the day and year", () => {
            mockLocale.value = "en";
            const { formatDateShort } = useDateFormat();
            const result = formatDateShort(ISO_DATE);

            expect(result).toContain("2024");
            expect(result).toContain("15");
        });

        it("does not include time", () => {
            mockLocale.value = "en";
            const { formatDateShort } = useDateFormat();
            const result = formatDateShort(ISO_DATE);

            expect(result).not.toContain("10:30");
        });
    });

    describe("formatDateTime", () => {
        it("includes hour and minute", () => {
            mockLocale.value = "en";
            const { formatDateTime } = useDateFormat();
            const result = formatDateTime(ISO_DATE);

            expect(result).toContain("10");
            expect(result).toContain("30");
        });

        it("returns a non-empty string", () => {
            mockLocale.value = "en";
            const { formatDateTime } = useDateFormat();

            expect(formatDateTime(ISO_DATE)).not.toBe("");
        });
    });
});
