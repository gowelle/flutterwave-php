/**
 * Flutterwave Encryption Utility
 * Client-side AES-256-GCM encryption for card data
 */

/**
 * Generate a random nonce of specified length
 */
export function generateNonce(length: number = 12): string {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    const randomValues = new Uint8Array(length);
    crypto.getRandomValues(randomValues);
    for (let i = 0; i < length; i++) {
        result += chars[randomValues[i] % chars.length];
    }
    return result;
}

/**
 * Encrypt a string using AES-256-GCM
 */
export async function encryptData(
    key: CryptoKey,
    iv: Uint8Array,
    data: string
): Promise<string> {
    const encoder = new TextEncoder();
    // Create a new Uint8Array backed by a regular ArrayBuffer for TypeScript compatibility
    const ivBuffer = new Uint8Array(iv.length);
    ivBuffer.set(iv);
    const encrypted = await crypto.subtle.encrypt(
        { name: 'AES-GCM', iv: ivBuffer },
        key,
        encoder.encode(data)
    );
    return btoa(String.fromCharCode(...new Uint8Array(encrypted)));
}

/**
 * Import encryption key for AES-256-GCM
 */
export async function importKey(keyString: string): Promise<CryptoKey> {
    const encoder = new TextEncoder();
    const keyBuffer = encoder.encode(keyString).slice(0, 32);

    return crypto.subtle.importKey(
        'raw',
        keyBuffer,
        { name: 'AES-GCM' },
        false,
        ['encrypt']
    );
}

/**
 * Encrypt card data for Flutterwave API
 */
export async function encryptCardData(
    encryptionKey: string,
    cardNumber: string,
    cvv: string,
    expiryMonth: string,
    expiryYear: string
): Promise<{
    nonce: string;
    encrypted_card_number: string;
    encrypted_cvv: string;
    encrypted_expiry_month: string;
    encrypted_expiry_year: string;
}> {
    const nonce = generateNonce(12);
    const encoder = new TextEncoder();
    const iv = encoder.encode(nonce);
    const key = await importKey(encryptionKey);

    const [encryptedCardNumber, encryptedCvv, encryptedExpiryMonth, encryptedExpiryYear] =
        await Promise.all([
            encryptData(key, iv, cardNumber.replace(/\s/g, '')),
            encryptData(key, iv, cvv),
            encryptData(key, iv, expiryMonth),
            encryptData(key, iv, expiryYear),
        ]);

    return {
        nonce,
        encrypted_card_number: encryptedCardNumber,
        encrypted_cvv: encryptedCvv,
        encrypted_expiry_month: encryptedExpiryMonth,
        encrypted_expiry_year: encryptedExpiryYear,
    };
}

/**
 * Encrypt PIN for authorization
 */
export async function encryptPin(
    encryptionKey: string,
    pin: string
): Promise<{ nonce: string; encrypted_pin: string }> {
    const nonce = generateNonce(12);
    const encoder = new TextEncoder();
    const iv = encoder.encode(nonce);
    const key = await importKey(encryptionKey);

    const encryptedPin = await encryptData(key, iv, pin);

    return { nonce, encrypted_pin: encryptedPin };
}

/**
 * Detect card brand from card number
 */
export function detectCardBrand(cardNumber: string): string {
    const number = cardNumber.replace(/\s/g, '');

    if (/^4/.test(number)) return 'visa';
    if (/^5[1-5]/.test(number) || /^2[2-7]/.test(number)) return 'mastercard';
    if (/^506[01]/.test(number) || /^507[89]/.test(number) || /^6500/.test(number)) return 'verve';
    if (/^3[47]/.test(number)) return 'amex';

    return '';
}

/**
 * Format card number with spaces
 */
export function formatCardNumber(value: string): string {
    const digits = value.replace(/\D/g, '').slice(0, 16);
    return digits.replace(/(.{4})/g, '$1 ').trim();
}

/**
 * Validate card number using Luhn algorithm
 */
export function validateCardNumber(cardNumber: string): boolean {
    const digits = cardNumber.replace(/\D/g, '');
    if (digits.length < 13 || digits.length > 19) return false;

    let sum = 0;
    let isEven = false;

    for (let i = digits.length - 1; i >= 0; i--) {
        let digit = parseInt(digits[i], 10);

        if (isEven) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }

        sum += digit;
        isEven = !isEven;
    }

    return sum % 10 === 0;
}

/**
 * Validate expiry date
 */
export function validateExpiry(month: string, year: string): boolean {
    const m = parseInt(month, 10);
    const y = parseInt(year, 10);

    if (m < 1 || m > 12) return false;

    const now = new Date();
    const currentYear = now.getFullYear() % 100;
    const currentMonth = now.getMonth() + 1;

    if (y < currentYear) return false;
    if (y === currentYear && m < currentMonth) return false;

    return true;
}

/**
 * Validate CVV
 */
export function validateCvv(cvv: string, cardBrand: string = ''): boolean {
    const length = cvv.length;
    if (cardBrand === 'amex') return length === 4;
    return length === 3 || length === 4;
}
