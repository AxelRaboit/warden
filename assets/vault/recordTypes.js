import {
    KeyRound,
    CreditCard,
    User,
    MapPin,
    Landmark,
    Car,
    ScrollText,
    Database,
    Server,
    HeartPulse,
    BadgeCheck,
    FileText,
    BookMarked,
    IdCard,
    Package,
    Terminal,
} from "lucide-vue-next";

export const RECORD_TYPES = {
    login: {
        icon: KeyRound,
        color: "indigo",
        defaultFields: [],
    },
    payment_card: {
        icon: CreditCard,
        color: "emerald",
        defaultFields: [
            { type: "text", labelKey: "cardholder" },
            { type: "text", labelKey: "card_number" },
            { type: "text", labelKey: "expiry" },
            { type: "password", labelKey: "cvv" },
            { type: "password", labelKey: "pin" },
        ],
    },
    contact: {
        icon: User,
        color: "sky",
        defaultFields: [
            { type: "text", labelKey: "full_name" },
            { type: "text", labelKey: "phone" },
            { type: "email", labelKey: "email" },
            { type: "text", labelKey: "company" },
        ],
    },
    address: {
        icon: MapPin,
        color: "cyan",
        defaultFields: [
            { type: "text", labelKey: "street" },
            { type: "text", labelKey: "city" },
            { type: "text", labelKey: "postal_code" },
            { type: "text", labelKey: "country" },
        ],
    },
    bank_account: {
        icon: Landmark,
        color: "emerald",
        defaultFields: [
            { type: "text", labelKey: "bank_name" },
            { type: "text", labelKey: "account_number" },
            { type: "text", labelKey: "iban" },
            { type: "text", labelKey: "swift_bic" },
        ],
    },
    driver_license: {
        icon: Car,
        color: "amber",
        defaultFields: [
            { type: "text", labelKey: "number" },
            { type: "text", labelKey: "country" },
            { type: "text", labelKey: "state" },
            { type: "text", labelKey: "license_class" },
            { type: "text", labelKey: "expiry" },
            { type: "text", labelKey: "date_of_birth" },
        ],
    },
    birth_certificate: {
        icon: ScrollText,
        color: "slate",
        defaultFields: [
            { type: "text", labelKey: "certificate_number" },
            { type: "text", labelKey: "place_of_birth" },
            { type: "text", labelKey: "date_of_birth" },
        ],
    },
    database: {
        icon: Database,
        color: "purple",
        defaultFields: [
            { type: "text", labelKey: "host" },
            { type: "text", labelKey: "port" },
            { type: "text", labelKey: "database_name" },
            { type: "text", labelKey: "username" },
            { type: "password", labelKey: "password" },
        ],
    },
    server: {
        icon: Server,
        color: "violet",
        defaultFields: [
            { type: "text", labelKey: "host" },
            { type: "text", labelKey: "port" },
            { type: "text", labelKey: "username" },
            { type: "password", labelKey: "password" },
        ],
    },
    health_insurance: {
        icon: HeartPulse,
        color: "rose",
        defaultFields: [
            { type: "text", labelKey: "provider" },
            { type: "text", labelKey: "member_id" },
            { type: "text", labelKey: "group_number" },
        ],
    },
    membership: {
        icon: BadgeCheck,
        color: "yellow",
        defaultFields: [
            { type: "text", labelKey: "organization" },
            { type: "text", labelKey: "member_id" },
            { type: "text", labelKey: "expiry" },
        ],
    },
    secure_note: {
        icon: FileText,
        color: "slate",
        defaultFields: [],
    },
    passport: {
        icon: BookMarked,
        color: "sky",
        defaultFields: [
            { type: "text", labelKey: "number" },
            { type: "text", labelKey: "country" },
            { type: "text", labelKey: "issue_date" },
            { type: "text", labelKey: "expiry" },
            { type: "text", labelKey: "place_of_birth" },
        ],
    },
    identity_card: {
        icon: IdCard,
        color: "teal",
        defaultFields: [
            { type: "text", labelKey: "number" },
            { type: "text", labelKey: "issue_date" },
            { type: "text", labelKey: "expiry" },
        ],
    },
    software_license: {
        icon: Package,
        color: "fuchsia",
        defaultFields: [
            { type: "text", labelKey: "product" },
            { type: "password", labelKey: "license_key" },
            { type: "text", labelKey: "version" },
            { type: "email", labelKey: "support_email" },
        ],
    },
    ssh_key: {
        icon: Terminal,
        color: "lime",
        defaultFields: [
            { type: "note", labelKey: "private_key" },
            { type: "note", labelKey: "public_key" },
            { type: "password", labelKey: "passphrase" },
        ],
    },
};

export const RECORD_TYPE_KEYS = Object.keys(RECORD_TYPES);

export function getRecordType(key) {
    return RECORD_TYPES[key] ?? RECORD_TYPES.login;
}

export function buildDefaultFields(typeKey, t) {
    const type = getRecordType(typeKey);
    return type.defaultFields.map((f) => ({
        id: crypto.randomUUID(),
        type: f.type,
        label: t(`vault.field_labels.${f.labelKey}`),
        value: "",
        _editing: true,
    }));
}
