<?php

namespace App\Services;

use App\Models\PasswordResetCode;
use App\Models\User;
use App\Mail\PasswordResetCode as PasswordResetMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * ──────────────────────────────────────────────────────────────
 *  OtpService — Modular OTP (One-Time Password) Engine
 * ──────────────────────────────────────────────────────────────
 *  Responsibilities:
 *   1. Generate cryptographically-secure 6-digit codes
 *   2. Store hashed codes with automatic TTL (5 min)
 *   3. Send OTP via email (Mail driver agnostic)
 *   4. Verify code, increment attempts, delete on success
 *   5. Rate-limit: cooldown between sends + hourly cap
 *   6. Lock after N failed verification attempts
 * ──────────────────────────────────────────────────────────────
 */
class OtpService
{
    /* ═══════════════════════════════════════════════════════════
     *  1. GENERATE — Secure 6-digit OTP
     * ═══════════════════════════════════════════════════════════ */

    /**
     * Generate a cryptographically secure 6-digit numeric code.
     * Uses random_int() which reads from /dev/urandom or CNG.
     */
    public function generate(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /* ═══════════════════════════════════════════════════════════
     *  2. SEND — Rate-limit check → generate → store → email
     * ═══════════════════════════════════════════════════════════ */

    /**
     * Full "send OTP" pipeline for a given email.
     *
     * @return array{success: bool, message: string, debug_code?: string}
     */
    public function send(string $email): array
    {
        // ── Rate-limit: minimum gap between consecutive sends ──
        $latest = PasswordResetCode::where('email', $email)
            ->orderByDesc('created_at')
            ->first();

        if ($latest) {
            $nextAllowed = $latest->created_at->addSeconds(PasswordResetCode::RATE_LIMIT_SECONDS);
            if ($nextAllowed->isFuture()) {
                $wait = (int) now()->diffInSeconds($nextAllowed, false);
                return [
                    'success' => false,
                    'message' => "Please wait {$wait} seconds before requesting a new code.",
                    'status'  => 429,
                ];
            }
        }

        // ── Rate-limit: max sends per hour ──
        $sendsLastHour = PasswordResetCode::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        if ($sendsLastHour >= PasswordResetCode::MAX_SENDS_PER_HOUR) {
            return [
                'success' => false,
                'message' => 'Too many reset requests. Please try again later.',
                'status'  => 429,
            ];
        }

        // ── Lookup user (return generic message if not found — don't leak info) ──
        $user = User::where('email', $email)->first();
        if (!$user) {
            return [
                'success' => true,
                'message' => 'If this email is registered, a reset code has been sent.',
            ];
        }

        // ── Purge old codes for this email ──
        PasswordResetCode::where('email', $email)->delete();

        // ── Generate & store (hashed) ──
        $plainCode = $this->generate();

        PasswordResetCode::create([
            'email'      => $email,
            'code'       => Hash::make($plainCode),
            'attempts'   => 0,
            'created_at' => Carbon::now(),
        ]);

        // ── Send email ──
        $this->dispatchEmail($email, $plainCode, $user->name);

        // ── Response ──
        $response = [
            'success' => true,
            'message' => 'If this email is registered, a reset code has been sent.',
        ];

        // In debug/dev mode, include the plain code so frontend can auto-fill
        if (config('app.debug')) {
            $response['debug_code'] = $plainCode;
        }

        return $response;
    }

    /* ═══════════════════════════════════════════════════════════
     *  3. VERIFY — Validate the code, lock/delete as needed
     * ═══════════════════════════════════════════════════════════ */

    /**
     * Verify a submitted OTP code for a given email.
     *
     * @return array{success: bool, message: string}
     */
    public function verify(string $email, string $code): array
    {
        $record = PasswordResetCode::where('email', $email)->first();

        if (!$record) {
            return [
                'success' => false,
                'message' => 'No reset code found. Please request a new one.',
                'status'  => 422,
            ];
        }

        // ── TTL check ──
        if ($record->isExpired()) {
            $record->delete();
            return [
                'success' => false,
                'message' => 'Reset code has expired. Please request a new one.',
                'status'  => 422,
            ];
        }

        // ── Lock check (3 failed attempts) ──
        if ($record->isLocked()) {
            $record->delete();
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new code.',
                'status'  => 422,
            ];
        }

        // ── Code comparison (hash-safe) ──
        if (!Hash::check($code, $record->code)) {
            $record->increment('attempts');
            $record->refresh();

            // If this was the last attempt, delete and lock
            if ($record->isLocked()) {
                $record->delete();
                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new code.',
                    'status'  => 422,
                ];
            }

            $remaining = $record->remainingAttempts();

            return [
                'success' => false,
                'message' => "Invalid code. {$remaining} attempt(s) remaining.",
                'status'  => 422,
            ];
        }

        // ── Success: delete the code immediately ──
        $record->delete();

        return [
            'success' => true,
            'message' => 'Code verified successfully.',
        ];
    }

    /* ═══════════════════════════════════════════════════════════
     *  4. CLEANUP — Purge expired codes (for scheduler)
     * ═══════════════════════════════════════════════════════════ */

    /**
     * Remove all expired code records from the database.
     * Intended to be called from a scheduled artisan command.
     *
     * @return int Number of records deleted
     */
    public function purgeExpired(): int
    {
        return PasswordResetCode::where(
            'created_at', '<', Carbon::now()->subMinutes(PasswordResetCode::TTL_MINUTES)
        )->delete();
    }

    /* ═══════════════════════════════════════════════════════════
     *  5. EMAIL DISPATCH (private)
     * ═══════════════════════════════════════════════════════════ */

    /**
     * Send the OTP email. Silently catches transport errors in dev.
     */
    private function dispatchEmail(string $email, string $code, string $userName): void
    {
        try {
            Mail::to($email)->send(new PasswordResetMail($code, $userName));
        } catch (\Exception $e) {
            // Mail transport not configured — graceful fallback in dev
            if (!config('app.debug')) {
                report($e); // Log in production for monitoring
            }
        }
    }
}
