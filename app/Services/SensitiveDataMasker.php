<?php

namespace App\Services;

class SensitiveDataMasker
{
    protected array $sensitiveKeys;

    protected string $maskValue;

    protected bool $caseSensitive;

    public function __construct()
    {
        $this->sensitiveKeys = config('sensitive-data.keys', []);
        $this->maskValue = config('sensitive-data.mask_value', '<information hidden>');
        $this->caseSensitive = config('sensitive-data.case_sensitive', false);
    }

    /**
     * Mask sensitive data in array (deep search)
     */
    public function mask(array $data): array
    {
        return $this->maskRecursive($data);
    }

    /**
     * Recursively mask sensitive data in nested arrays
     */
    protected function maskRecursive(array $data): array
    {
        foreach ($data as $key => $value) {
            // Check if current key is sensitive
            $isSensitive = $this->isSensitiveKey($key);

            if ($isSensitive) {
                $data[$key] = $this->maskValue;
            } elseif (is_array($value)) {
                // Recursively mask nested arrays
                $data[$key] = $this->maskRecursive($value);
            }
        }

        return $data;
    }

    /**
     * Check if a key is sensitive
     */
    protected function isSensitiveKey(string $key): bool
    {
        if ($this->caseSensitive) {
            return in_array($key, $this->sensitiveKeys, true);
        }

        return in_array(strtolower($key), array_map('strtolower', $this->sensitiveKeys), true);
    }

    /**
     * Add custom sensitive keys
     */
    public function addSensitiveKeys(array $keys): self
    {
        $this->sensitiveKeys = array_merge($this->sensitiveKeys, $keys);

        return $this;
    }

    /**
     * Set custom mask value
     */
    public function setMaskValue(string $value): self
    {
        $this->maskValue = $value;

        return $this;
    }

    /**
     * Set case sensitivity
     */
    public function setCaseSensitive(bool $caseSensitive): self
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
    }

    /**
     * Get current sensitive keys
     */
    public function getSensitiveKeys(): array
    {
        return $this->sensitiveKeys;
    }
}
