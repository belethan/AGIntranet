<?php

namespace App\Service;

interface DocumentWebserviceInterface
{
    /**
     * Liste des documents pour un agent
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchDocuments(string $codagt): array;

    /**
     * Récupère le fichier binaire d’un document
     */
    public function fetchDocumentFile(int $iddoc): string;

    /**
     * Récupère un nouvel ID Oracle pour un document
     */
    public function fetchNewDocId(): int;
}

