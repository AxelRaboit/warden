import argon2 from "argon2-browser/dist/argon2-bundled.min.js";

const SESSION_KEY = "warden_vault_key";

function hexToBytes(hex) {
    const bytes = new Uint8Array(hex.length / 2);
    for (let i = 0; i < hex.length; i += 2) {
        bytes[i / 2] = parseInt(hex.slice(i, i + 2), 16);
    }
    return bytes;
}

function base64ToBytes(b64) {
    return Uint8Array.from(atob(b64), (c) => c.charCodeAt(0));
}

function bytesToBase64(bytes) {
    return btoa(String.fromCharCode(...bytes));
}

async function importKey(rawBytes) {
    return crypto.subtle.importKey(
        "raw",
        rawBytes,
        { name: "AES-GCM" },
        false,
        ["encrypt", "decrypt"],
    );
}

export function isUnlocked() {
    return sessionStorage.getItem(SESSION_KEY) !== null;
}

export function lock() {
    sessionStorage.removeItem(SESSION_KEY);
}

export async function deriveKey(password, saltHex) {
    const saltBytes = hexToBytes(saltHex);

    const result = await argon2.hash({
        pass: password,
        salt: saltBytes,
        type: argon2.ArgonType.Argon2id,
        time: 2,
        mem: 65536,
        parallelism: 1,
        hashLen: 32,
    });

    sessionStorage.setItem(SESSION_KEY, bytesToBase64(result.hash));
}

async function getKey() {
    const stored = sessionStorage.getItem(SESSION_KEY);
    if (!stored) throw new Error("Vault is locked");
    return importKey(base64ToBytes(stored));
}

export async function encrypt(plaintext) {
    const key = await getKey();
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const encoded = new TextEncoder().encode(plaintext);
    const ciphertext = await crypto.subtle.encrypt(
        { name: "AES-GCM", iv },
        key,
        encoded,
    );

    return {
        encryptedData: bytesToBase64(new Uint8Array(ciphertext)),
        iv: bytesToBase64(iv),
    };
}

export async function decrypt(encryptedData, iv) {
    const key = await getKey();
    const ciphertext = base64ToBytes(encryptedData);
    const ivBytes = base64ToBytes(iv);
    const plaintext = await crypto.subtle.decrypt(
        { name: "AES-GCM", iv: ivBytes },
        key,
        ciphertext,
    );
    return new TextDecoder().decode(plaintext);
}
