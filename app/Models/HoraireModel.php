<?php
declare(strict_types=1);

class HoraireModel extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query('
            SELECT jour, heure_ouverture, heure_fermeture, ferme
            FROM horaire
            ORDER BY horaire_id ASC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Regroupe les jours consécutifs ayant les mêmes horaires.
     * Retourne par ex. [['jours' => 'Lundi – Jeudi', 'heures' => '9h – 18h'], ...]
     */
    public function getLignes(): array
    {
        $lignes = [];

        foreach ($this->getAll() as $j) {
            $heures = $j['ferme']
                ? 'fermé'
                : $this->formatHeure($j['heure_ouverture']) . ' – ' . $this->formatHeure($j['heure_fermeture']);

            $dernier = count($lignes) - 1;
            if ($dernier >= 0 && $lignes[$dernier]['heures'] === $heures) {
                $lignes[$dernier]['fin'] = $j['jour'];
            } else {
                $lignes[] = ['debut' => $j['jour'], 'fin' => null, 'heures' => $heures];
            }
        }

        return array_map(fn($l) => [
            'jours'  => ucfirst($l['debut']) . ($l['fin'] ? ' – ' . ucfirst($l['fin']) : ''),
            'heures' => $l['heures'],
        ], $lignes);
    }

    private function formatHeure(?string $heure): string
    {
        if ($heure === null) {
            return '';
        }
        [$h, $m] = explode(':', $heure);
        return (int)$h . 'h' . ($m !== '00' ? $m : '');
    }
}
