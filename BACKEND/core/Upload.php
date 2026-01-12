<?php

namespace Core;

class Upload
{
    private array $file;
    private array $allowedExtensions = [];
    private int $maxSize = 2097152; // 2MB por defecto

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    public function allowed(array $extensions): self
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }

    public function maxSize(int $bytes): self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    public function store(string $path, ?string $customName = null): string|bool
    {
        if ($this->file['error'] !== UPLOAD_ERR_OK) return false;

        // Validar tamaño
        if ($this->file['size'] > $this->maxSize) return false;

        // Validar extensión
        $ext = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!empty($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) {
            return false;
        }

        // Preparar nombre y ruta
        $name = $customName ?? bin2hex(random_bytes(8)) . "." . $ext;
        $fullPath = rtrim($path, '/') . '/' . $name;

        if (!is_dir($path)) mkdir($path, 0755, true);

        if (move_uploaded_file($this->file['tmp_name'], $fullPath)) {
            return $name; // Retornamos el nombre final
        }

        return false;
    }
}